<?php

namespace App\Http\Controllers;

use App\Models\AttendanceLog;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use App\Services\FaceVerification\PythonFaceVerificationClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        private PythonFaceVerificationClient $faceVerification,
    ) {}

    public function mark(Request $request): JsonResponse
    {
        $student = $request->user('student');

        abort_if(! $student instanceof Student, 403);

        $validated = $request->validate([
            'session_id' => ['required', 'integer', 'exists:class_sessions,id'],
            'latitude' => ['required', 'numeric', 'min:-90', 'max:90'],
            'longitude' => ['required', 'numeric', 'min:-180', 'max:180'],
            'face_detected' => ['required', 'boolean'],
            'face_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $session = ClassSession::query()
            ->with(['classroom', 'subject', 'term'])
            ->findOrFail($validated['session_id']);

        $this->assertStudentApprovedEnrollment($student->id, $session->subject_id, $session->term_id);

        // Prevent duplicate attendance for the same session.
        $existing = AttendanceLog::query()
            ->where('student_id', $student->id)
            ->where('class_session_id', $session->id)
            ->first();

        if ($existing) {
            return response()->json([
                'ok' => true,
                'message' => 'Attendance already marked for this session.',
                'status' => $existing->status,
            ], 200);
        }

        // Time window enforcement (with a small grace period).
        $now = now();
        $windowStartAt = $session->start_at->copy()->subMinutes(10);
        $windowEndAt = $session->marking_end_at->copy()->addMinutes(30);

        if ($now->lt($windowStartAt) || $now->gt($windowEndAt)) {
            return response()->json([
                'ok' => false,
                'message' => 'Attendance window is closed for this session.',
            ], 422);
        }

        $faceDetected = (bool) $validated['face_detected'];
        if (! $faceDetected) {
            // Still record the attempt (for audit), but mark it as failed.
            $log = $this->persistAttempt(
                $student->id,
                $session,
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                false,
                null,
            );

            $log->status = 'failed_face';
            $log->save();

            return response()->json([
                'ok' => false,
                'message' => 'No face detected. Please try again.',
                'status' => $log->status,
            ], 422);
        }

        $referenceBytes = null;
        if (config('face.enabled')) {
            $referenceBytes = $student->referencePhotoBinary();
            if (! $referenceBytes) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Add a profile photo in Settings before marking attendance (required for face verification).',
                ], 422);
            }
        }

        $image = $request->file('face_image');
        $faceImagePath = null;
        if ($image) {
            $faceImagePath = $image->store('attendance-faces', 'public');
        }

        $log = $this->persistAttempt(
            $student->id,
            $session,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            true,
            $faceImagePath,
        );

        if (config('face.enabled') && $referenceBytes && $faceImagePath) {
            $probePath = storage_path('app/public/'.$faceImagePath);
            $outcome = $this->faceVerification->verify($referenceBytes, $probePath);
            $log->face_match_distance = $outcome->distance;

            if ($outcome->errorMessage !== null) {
                $log->status = 'failed_face_match';
                $log->save();

                return response()->json([
                    'ok' => false,
                    'message' => $outcome->errorMessage,
                    'status' => $log->status,
                ], 422);
            }

            if (! $outcome->verified) {
                $log->status = 'failed_face_match';
                $log->save();

                return response()->json([
                    'ok' => false,
                    'message' => 'Live capture does not match your profile photo.',
                    'status' => $log->status,
                    'distance' => $outcome->distance,
                ], 422);
            }

            $log->save();
        }

        $success = $log->location_match && $log->face_detected;
        $log->status = $success ? 'present' : 'rejected_location';
        $log->save();

        return response()->json([
            'ok' => $success,
            'message' => $success ? 'Attendance marked successfully.' : 'Face ok, but geolocation does not match classroom.',
            'status' => $log->status,
            'distance_m' => $log->distance_m,
            'radius_m' => $log->geofence_radius_m,
            'face_match_distance' => $log->face_match_distance,
        ], 200);
    }

    private function assertStudentApprovedEnrollment(int $studentId, int $subjectId, int $termId): void
    {
        $approved = StudentSubjectEnrollment::query()
            ->where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->where('term_id', $termId)
            ->where('status', 'approved')
            ->exists();

        abort_if(! $approved, 403);
    }

    private function persistAttempt(
        int $studentId,
        ClassSession $session,
        float $lat,
        float $lng,
        bool $faceDetected,
        ?string $faceImagePath
    ): AttendanceLog {
        $classroom = $session->classroom;

        $distanceM = null;
        $radiusM = null;
        $locationMatch = false;

        if ($classroom && $classroom->latitude !== null && $classroom->longitude !== null) {
            $radiusM = (int) ($classroom->radius_m ?? 100);
            $distanceM = $this->distanceMeters(
                $lat,
                $lng,
                (float) $classroom->latitude,
                (float) $classroom->longitude,
            );
            $locationMatch = $distanceM <= $radiusM;
        }

        return AttendanceLog::query()->create([
            'class_session_id' => $session->id,
            'student_id' => $studentId,
            'marked_at' => now(),
            'geolocation_lat' => $lat,
            'geolocation_lng' => $lng,
            'distance_m' => $distanceM,
            'geofence_radius_m' => $radiusM,
            'face_detected' => $faceDetected,
            'face_image_path' => $faceImagePath,
            'location_match' => $locationMatch,
            // status is finalized after location/face checks
            'status' => 'rejected',
        ]);
    }

    private function distanceMeters(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        // Haversine formula.
        $earthRadiusM = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * (sin($dLng / 2) ** 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusM * $c;
    }
}

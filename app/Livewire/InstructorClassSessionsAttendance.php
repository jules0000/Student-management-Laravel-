<?php

namespace App\Livewire;

use App\Models\AttendanceLog;
use App\Models\ClassSession;
use App\Models\StudentSubjectEnrollment;
use App\Models\Term;
use Livewire\Component;

class InstructorClassSessionsAttendance extends Component
{
    public ?int $selectedSubjectId = null;
    public ?int $selectedTermId = null;
    public ?int $selectedSessionId = null;

    public function mount(): void
    {
        $instructor = auth('instructor')->user();
        $this->selectedSubjectId = $instructor?->subjects()->orderBy('id')->value('id');
        $this->selectedTermId = Term::query()->orderBy('id')->value('id');
    }

    public function updatedSelectedSubjectId(): void
    {
        $this->selectedSessionId = null;
    }

    public function updatedSelectedTermId(): void
    {
        $this->selectedSessionId = null;
    }

    public function render()
    {
        $instructor = auth('instructor')->user();

        $subjects = $instructor?->subjects()
            ->orderBy('name')
            ->get() ?? collect();

        $terms = Term::query()
            ->orderBy('id')
            ->get();

        $enrollments = collect();
        $sessions = collect();

        /** @var ?ClassSession $selectedSession */
        $selectedSession = null;
        $attendanceByStudentId = collect();
        $attendanceSummary = [
            'students' => 0,
            'marked' => 0,
            'present' => 0,
            'failed_face' => 0,
            'failed_face_match' => 0,
            'rejected_location' => 0,
        ];

        if ($instructor && $this->selectedSubjectId && $this->selectedTermId) {
            abort_if(
                ! $instructor->subjects()->whereKey($this->selectedSubjectId)->exists(),
                403
            );

            $enrollments = StudentSubjectEnrollment::query()
                ->where('subject_id', $this->selectedSubjectId)
                ->where('term_id', $this->selectedTermId)
                ->where('status', 'approved')
                ->with(['student'])
                ->orderBy('student_id')
                ->get();

            $sessions = ClassSession::query()
                ->with(['classroom'])
                ->where('instructor_id', $instructor->id)
                ->where('subject_id', $this->selectedSubjectId)
                ->where('term_id', $this->selectedTermId)
                ->orderByDesc('start_at')
                ->get();

            $selectedSession = $this->selectedSessionId
                ? $sessions->firstWhere('id', $this->selectedSessionId)
                : null;

            if (! $selectedSession && $sessions->isNotEmpty()) {
                $selectedSession = $sessions->first();
                $this->selectedSessionId = $selectedSession->id;
            }

            if ($selectedSession) {
                /** @var \Illuminate\Support\Collection<int, AttendanceLog> $attendanceByStudentId */
                $attendanceByStudentId = AttendanceLog::query()
                    ->where('class_session_id', $selectedSession->id)
                    ->get()
                    ->keyBy('student_id');

                $attendanceSummary = [
                    'students' => $enrollments->count(),
                    'marked' => $attendanceByStudentId->count(),
                    'present' => $attendanceByStudentId->where('status', 'present')->count(),
                    'failed_face' => $attendanceByStudentId->where('status', 'failed_face')->count(),
                    'failed_face_match' => $attendanceByStudentId->where('status', 'failed_face_match')->count(),
                    'rejected_location' => $attendanceByStudentId->where('status', 'rejected_location')->count(),
                ];
            }
        }

        return view('livewire.instructor-class-sessions-attendance', [
            'subjects' => $subjects,
            'terms' => $terms,
            'enrollments' => $enrollments,
            'sessions' => $sessions,
            'selectedSession' => $selectedSession,
            'attendanceByStudentId' => $attendanceByStudentId,
            'attendanceSummary' => $attendanceSummary,
        ]);
    }
}


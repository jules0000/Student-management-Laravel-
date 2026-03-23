<?php

namespace App\Livewire;

use App\Models\AttendanceLog;
use App\Models\ClassSession;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use App\Models\Term;
use Livewire\Component;

class StudentAttendance extends Component
{
    public ?int $selectedSubjectId = null;
    public ?int $selectedMonitoringSessionId = null;
    public ?int $selectedTermId = null;

    private function getStudentCurrentTerm(?Student $student): ?Term
    {
        if (! $student instanceof Student) {
            return null;
        }

        $approvedEnrollment = StudentSubjectEnrollment::query()
            ->where('student_id', $student->id)
            ->whereRaw('lower(trim(status)) = ?', ['approved'])
            ->orderByDesc('term_id')
            ->first();

        if (! $approvedEnrollment) {
            return null;
        }

        return Term::query()->whereKey($approvedEnrollment->term_id)->first();
    }

    public function mount(): void
    {
        $student = auth('student')->user();
        if (! $student instanceof Student) {
            return;
        }

        $currentTerm = $this->getStudentCurrentTerm($student);
        $this->selectedTermId = $currentTerm?->id;

        if (! $this->selectedTermId) {
            return;
        }

        $subjectIds = StudentSubjectEnrollment::query()
            ->where('student_id', $student->id)
            ->where('term_id', $this->selectedTermId)
            ->whereRaw('lower(trim(status)) = ?', ['approved'])
            ->pluck('subject_id')
            ->unique()
            ->values()
            ->all();

        $this->selectedSubjectId = $subjectIds[0] ?? null;
    }

    public function updatedSelectedTermId($value): void
    {
        $student = auth('student')->user();
        if (! $student instanceof Student) {
            return;
        }

        $termId = $value ? (int) $value : null;
        if (! $termId) {
            $this->selectedTermId = null;
            $this->selectedSubjectId = null;
            $this->selectedMonitoringSessionId = null;
            return;
        }

        // If student has no approved enrollment in this term, reset selection.
        $termHasApproved = StudentSubjectEnrollment::query()
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->whereRaw('lower(trim(status)) = ?', ['approved'])
            ->exists();

        if (! $termHasApproved) {
            $this->selectedTermId = null;
            $this->selectedSubjectId = null;
            $this->selectedMonitoringSessionId = null;
            return;
        }

        $subjectIds = StudentSubjectEnrollment::query()
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->whereRaw('lower(trim(status)) = ?', ['approved'])
            ->pluck('subject_id')
            ->unique()
            ->values()
            ->all();

        $this->selectedSubjectId = $subjectIds[0] ?? null;
        $this->selectedMonitoringSessionId = null;
    }

    public function updatedSelectedSubjectId($value): void
    {
        $student = auth('student')->user();
        if (! $student instanceof Student) {
            return;
        }

        $termId = $this->selectedTermId;
        if (! $termId) {
            $this->selectedSubjectId = null;
            return;
        }

        $subjectOk = StudentSubjectEnrollment::query()
            ->where('student_id', $student->id)
            ->where('term_id', $termId)
            ->whereRaw('lower(trim(status)) = ?', ['approved'])
            ->where('subject_id', $value)
            ->exists();

        if (! $subjectOk) {
            $this->selectedSubjectId = null;
        }
    }

    public function render()
    {
        $student = auth('student')->user();
        $currentTerm = $this->getStudentCurrentTerm($student instanceof Student ? $student : null);

        $termId = $this->selectedTermId ?: $currentTerm?->id;
        $selectedTerm = $termId ? Term::query()->whereKey($termId)->first() : null;

        $subjects = collect();
        $sessions = collect();
        $classmates = collect();
        $rosterEnrollments = collect();
        $attendanceBySessionId = collect();
        $monitoringSession = null;
        $monitoringAttendanceByStudentId = collect();
        $now = now();
        $terms = collect();

        if ($student instanceof Student) {
            $approvedTermIds = StudentSubjectEnrollment::query()
                ->where('student_id', $student->id)
                ->whereRaw('lower(trim(status)) = ?', ['approved'])
                ->distinct()
                ->pluck('term_id')
                ->all();

            $terms = Term::query()
                ->whereIn('id', $approvedTermIds)
                ->orderByDesc('id')
                ->get();
        }

        if ($student instanceof Student && $selectedTerm) {
            $enrollments = StudentSubjectEnrollment::query()
                ->where('student_id', $student->id)
                ->where('term_id', $selectedTerm->id)
                ->whereRaw('lower(trim(status)) = ?', ['approved'])
                ->with(['subject'])
                ->get();

            $subjects = $enrollments
                ->pluck('subject')
                ->filter()
                ->unique('id')
                ->values();

            if (! $this->selectedSubjectId && $subjects->isNotEmpty()) {
                $this->selectedSubjectId = $subjects->first()->id;
            }

            if ($this->selectedSubjectId) {
                $rosterEnrollments = StudentSubjectEnrollment::query()
                    ->where('term_id', $selectedTerm->id)
                    ->where('subject_id', $this->selectedSubjectId)
                    ->whereRaw('lower(trim(status)) = ?', ['approved'])
                    ->with(['student'])
                    ->orderBy('student_id')
                    ->get();

                $classmates = $rosterEnrollments
                    ->where('student_id', '!=', $student->id);

                $sessions = ClassSession::query()
                    ->with(['classroom', 'instructor'])
                    ->where('subject_id', $this->selectedSubjectId)
                        ->where('term_id', $selectedTerm->id)
                    ->orderBy('start_at')
                    ->get();

                $sessionIds = $sessions->pluck('id')->all();

                if (! empty($sessionIds)) {
                    $attendanceBySessionId = AttendanceLog::query()
                        ->where('student_id', $student->id)
                        ->whereIn('class_session_id', $sessionIds)
                        ->get()
                        ->keyBy('class_session_id');

                    // For monitoring: attendance status for the whole roster (current + classmates).
                    $rosterStudentIds = $rosterEnrollments->pluck('student_id')->all();

                    $attendanceLogs = AttendanceLog::query()
                        ->whereIn('class_session_id', $sessionIds)
                        ->whereIn('student_id', $rosterStudentIds)
                        ->get();

                    $attendanceLogsBySessionId = $attendanceLogs
                        ->groupBy('class_session_id')
                        ->map(fn ($group) => $group->keyBy('student_id'));

                    if ($this->selectedMonitoringSessionId) {
                        $monitoringSession = $sessions->firstWhere('id', $this->selectedMonitoringSessionId);
                        $monitoringAttendanceByStudentId = $attendanceLogsBySessionId[$this->selectedMonitoringSessionId] ?? collect();
                    }
                }
            }
        }

        // If the selected monitoring session no longer belongs to this subject/term, reset it.
        if ($monitoringSession === null && $this->selectedMonitoringSessionId !== null) {
            $this->selectedMonitoringSessionId = null;
        }

        return view('livewire.student-attendance', [
            'student' => $student,
            'currentTerm' => $selectedTerm,
            'terms' => $terms,
            'subjects' => $subjects,
            'sessions' => $sessions,
            'classmates' => $classmates,
            'rosterEnrollments' => $rosterEnrollments,
            'attendanceBySessionId' => $attendanceBySessionId,
            'monitoringSession' => $monitoringSession,
            'monitoringAttendanceByStudentId' => $monitoringAttendanceByStudentId,
            'now' => $now,
        ]);
    }
}


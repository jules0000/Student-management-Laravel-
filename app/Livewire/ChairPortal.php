<?php

namespace App\Livewire;

use App\Models\Grade;
use App\Models\Instructor;
use App\Models\Program;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use App\Models\Term;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChairPortal extends Component
{
    public array $subjectInstructorSelections = [];
    public ?int $confirmingDeleteSubjectId = null;
    public bool $supportsSubjectIsActive = false;
    private bool $supportsGradeStatus = false;
    private bool $supportsGradeApprovalFields = false;

    public array $form = [
        'subject_name' => '',
        'subject_code' => '',
        'instructor_id' => null,
        'is_active' => true,
    ];

    public ?int $createSubjectProgramId = null;

    /**
     * Schema flags must refresh on every Livewire request (hydration), not only in mount(),
     * otherwise wire:click actions like approveGrade() see supportsGradeStatus=false and skip updates.
     */
    public function boot(): void
    {
        $this->supportsSubjectIsActive = Schema::hasColumn('subjects', 'is_active');
        $this->supportsGradeStatus = Schema::hasColumn('grades', 'status');
        $this->supportsGradeApprovalFields = Schema::hasColumn('grades', 'approved_by_program_chair_id')
            && Schema::hasColumn('grades', 'approved_at');
    }

    public function mount(): void
    {
        $program = auth('program_chair')->user()?->programs()->orderBy('programs.id')->first();
        $this->createSubjectProgramId = $program?->id;
    }

    private function getChairProgramIds(): array
    {
        $chair = auth('program_chair')->user();
        return $chair?->programs()->pluck('programs.id')->all() ?? [];
    }

    public function createSubject(): void
    {
        $programIds = $this->getChairProgramIds();
        abort_if(empty($programIds), 403);

        $program = Program::query()->whereKey($this->createSubjectProgramId)->first();
        abort_if(! $program, 403);

        $this->validate([
            'form.subject_name' => ['required', 'string', 'max:255'],
            'form.subject_code' => ['required', 'string', 'max:50'],
            'form.instructor_id' => ['required', 'integer', 'exists:instructors,id'],
            'form.is_active' => ['boolean'],
        ]);

        $data = [
            'name' => $this->form['subject_name'],
            'code' => $this->form['subject_code'],
            'instructor_id' => $this->form['instructor_id'],
            'department_id' => $program->department_id,
            'program_id' => $program->id,
        ];

        if ($this->supportsSubjectIsActive) {
            $data['is_active'] = (bool) $this->form['is_active'];
        }

        $subject = Subject::create($data);

        // Reset form
        $this->form = [
            'subject_name' => '',
            'subject_code' => '',
            'instructor_id' => null,
            'is_active' => true,
        ];

        $this->dispatch('saved');
    }

    public function toggleSubjectActive(int $subjectId): void
    {
        $programIds = $this->getChairProgramIds();
        abort_if(empty($programIds), 403);

        $subject = Subject::query()->whereKey($subjectId)->first();
        abort_if(! $subject, 404);

        abort_if(! in_array((int) ($subject->program_id ?? -1), $programIds, true), 403);

        if (! $this->supportsSubjectIsActive) {
            // Column is not present in the current DB schema (e.g., migrations not applied yet).
            // Avoid crashing the whole Livewire request.
            $this->dispatch('saved');
            return;
        }

        $subject->update(['is_active' => ! (bool) $subject->is_active]);
        $this->dispatch('saved');
    }

    public function confirmDeleteSubject(int $subjectId): void
    {
        $programIds = $this->getChairProgramIds();
        abort_if(empty($programIds), 403);

        $subject = Subject::query()->whereKey($subjectId)->first();
        abort_if(! $subject, 404);
        abort_if(! in_array((int) ($subject->program_id ?? -1), $programIds, true), 403);

        $this->confirmingDeleteSubjectId = $subjectId;
    }

    public function deleteSubject(): void
    {
        $programIds = $this->getChairProgramIds();
        abort_if(empty($programIds), 403);

        if (! $this->confirmingDeleteSubjectId) {
            return;
        }

        $subject = Subject::query()->whereKey($this->confirmingDeleteSubjectId)->first();
        abort_if(! $subject, 404);
        abort_if(! in_array((int) ($subject->program_id ?? -1), $programIds, true), 403);

        $subject->delete();
        $this->confirmingDeleteSubjectId = null;
        $this->dispatch('saved');
    }

    public function exportSubjects()
    {
        $programIds = $this->getChairProgramIds();
        abort_if(empty($programIds), 403);

        $fileName = 'subjects_export_' . now()->toDateString() . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        $callback = function () use ($programIds) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'id',
                'name',
                'code',
                'instructor_name',
                'department',
                'program',
                'is_active',
            ]);

            Subject::query()
                ->with(['instructor', 'department', 'program'])
                ->whereIn('program_id', $programIds)
                ->orderByDesc('id')
                ->chunk(200, function ($chunk) use ($handle) {
                    foreach ($chunk as $subject) {
                        fputcsv($handle, [
                            $subject->id,
                            $subject->name,
                            $subject->code ?? '',
                            $subject->instructor?->name ?? '',
                            $subject->department?->name ?? '',
                            $subject->program?->name ?? '',
                            (bool) $subject->is_active ? 'active' : 'inactive',
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function approveEnrollment(int $enrollmentId): void
    {
        $programIds = $this->getChairProgramIds();
        abort_if(empty($programIds), 403);

        $chair = auth('program_chair')->user();

        $enrollment = StudentSubjectEnrollment::query()
            ->whereKey($enrollmentId)
            ->with(['student', 'subject'])
            ->first();

        abort_if(! $enrollment, 404);

        abort_if(! in_array((int) ($enrollment->student->program_id ?? -1), $programIds, true), 403);

        $enrollment->update(['status' => 'approved']);
    }

    public function approveGrade(int $gradeId): void
    {
        $programIds = $this->getChairProgramIds();
        abort_if(empty($programIds), 403);

        $chair = auth('program_chair')->user();

        $grade = Grade::query()
            ->whereKey($gradeId)
            ->with('student')
            ->first();

        abort_if(! $grade, 404);

        abort_if($grade->student && ! in_array((int) ($grade->student->program_id ?? -1), $programIds, true), 403);

        $data = [];
        if ($this->supportsGradeStatus) {
            $data['status'] = 'approved';
        }

        if ($this->supportsGradeApprovalFields) {
            $data['approved_by_program_chair_id'] = $chair->id;
            $data['approved_at'] = now();
        }

        // If grade approval columns don't exist in the current schema, do nothing instead of crashing.
        if (! empty($data)) {
            $grade->update($data);
        }

        $this->dispatch('saved');
    }

    public function assignInstructorToSubject(int $subjectId, int $instructorId): void
    {
        $programIds = $this->getChairProgramIds();
        abort_if(empty($programIds), 403);

        $subject = Subject::query()->whereKey($subjectId)->first();
        abort_if(! $subject, 404);

        abort_if(! in_array((int) ($subject->program_id ?? -1), $programIds, true), 403);

        $this->subjectInstructorSelections[$subjectId] = $instructorId;
        $subject->update(['instructor_id' => $instructorId]);
        $this->dispatch('saved');
    }

    public function render()
    {
        $chair = auth('program_chair')->user();
        $programIds = $this->getChairProgramIds();
        $program = $chair?->programs()->orderBy('programs.id')->first();

        $instructors = Instructor::query()->orderBy('name')->get();
        $terms = Term::query()->orderBy('id')->get();

        $subjectsQuery = Subject::query()
            ->whereIn('program_id', $programIds);

        if ($this->supportsSubjectIsActive) {
            $subjectsQuery->orderByDesc('is_active');
        }

        $subjects = $subjectsQuery
            ->orderByDesc('id')
            ->get();

        // Keep select state in sync on first render (prevents stale instructor IDs).
        foreach ($subjects as $subject) {
            if (! array_key_exists($subject->id, $this->subjectInstructorSelections)) {
                $this->subjectInstructorSelections[$subject->id] = $subject->instructor_id;
            }
        }

        // Pending enrollments for students in chair program(s)
        $studentsInPrograms = Student::query()
            ->whereIn('program_id', $programIds)
            ->pluck('id');

        $pendingEnrollments = StudentSubjectEnrollment::query()
            ->whereIn('student_id', $studentsInPrograms)
            ->where('status', 'pending')
            ->with(['student', 'subject', 'term'])
            ->orderByDesc('id')
            ->get();

        $pendingGrades = Grade::query()
            ->whereIn('student_id', $studentsInPrograms)
            ->with(['student', 'subject', 'term'])
            ->orderByDesc('id')
            ->get();

        if ($this->supportsGradeStatus) {
            $pendingGrades = Grade::query()
                ->whereIn('student_id', $studentsInPrograms)
                ->where('status', 'pending')
                ->with(['student', 'subject', 'term'])
                ->orderByDesc('id')
                ->get();
        } else {
            $pendingGrades = collect();
        }

        return view('livewire.chair-portal', [
            'program' => $program,
            'subjects' => $subjects,
            'instructors' => $instructors,
            'terms' => $terms,
            'pendingEnrollments' => $pendingEnrollments,
            'pendingGrades' => $pendingGrades,
        ]);
    }
}


<?php

namespace App\Livewire;

use App\Models\Grade;
use App\Models\Instructor;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use App\Models\Subject;
use App\Models\Term;
use Livewire\Component;

class ChairInstructorDetails extends Component
{
    public int $instructorId;

    public ?int $selectedSubjectId = null;
    public ?int $selectedTermId = null;

    /**
     * Student IDs currently selected to be advised by this instructor.
     * (Only students within the Program Chair's programs are editable here.)
     *
     * @var int[]
     */
    public array $advisingStudentIds = [];

    public function mount(int $instructorId): void
    {
        $this->instructorId = $instructorId;

        $programIds = $this->getChairProgramIds();
        if (! empty($programIds)) {
            $instructor = Instructor::query()->whereKey($this->instructorId)->first();
            $this->advisingStudentIds = $instructor
                ?->advisees()
                ->where(function ($q) use ($programIds) {
                    $q->whereIn('program_id', $programIds)->orWhereNull('program_id');
                })
                ->pluck('students.id')
                // Keep as strings to match checkbox `value` attributes (HTML values are strings).
                // This prevents Livewire from "forgetting" which advisers were already selected.
                ->map(fn ($id) => (string) $id)
                ->values()
                ->all() ?? [];
        }
    }

    private function getChairProgramIds(): array
    {
        $chair = auth('program_chair')->user();
        return $chair?->programs()->pluck('programs.id')->all() ?? [];
    }

    public function render()
    {
        $programIds = $this->getChairProgramIds();
        if (empty($programIds)) {
            return view('livewire.chair-instructor-details', [
                'instructor' => null,
                'subjects' => collect(),
                'terms' => collect(),
                'enrollments' => collect(),
                'gradesByStudentId' => [],
                'advisees' => collect(),
                'candidateStudents' => collect(),
                'adviseesEnrollStatusByStudentId' => [],
                'adviseesGradesByStudentId' => [],
            ]);
        }

        $instructor = Instructor::query()
            ->whereKey($this->instructorId)
            ->first();

        $subjects = Subject::query()
            ->where('instructor_id', $this->instructorId)
            ->whereIn('program_id', $programIds)
            ->orderBy('name')
            ->get();

        $terms = Term::query()->orderBy('id')->get();

        if ($this->selectedSubjectId === null || ! $subjects->contains('id', $this->selectedSubjectId)) {
            $this->selectedSubjectId = $subjects->first()?->id;
        }

        if ($this->selectedTermId === null || $this->selectedTermId === 0) {
            $this->selectedTermId = $terms->first()?->id;
        }

        $enrollments = collect();
        $gradesByStudentId = [];

        if ($this->selectedSubjectId && $this->selectedTermId) {
            $enrollments = StudentSubjectEnrollment::query()
                ->where('subject_id', $this->selectedSubjectId)
                ->where('term_id', $this->selectedTermId)
                ->whereHas('student', function ($q) use ($programIds) {
                    $q->whereIn('program_id', $programIds)->orWhereNull('program_id');
                })
                ->with(['student'])
                ->get()
                ->sortBy(function ($e) {
                    return (string) ($e->student->section ?? '') . '|' . (string) ($e->student->last_name ?? '');
                })
                ->values();

            $studentIds = $enrollments->pluck('student_id')->all();

            if (! empty($studentIds)) {
                $gradesByStudentId = Grade::query()
                    ->where('subject_id', $this->selectedSubjectId)
                    ->where('term_id', $this->selectedTermId)
                    ->whereIn('student_id', $studentIds)
                    ->get()
                    ->keyBy('student_id')
                    ;
            }
        }

        // Adviser class: students linked to this instructor via adviser_assignments.
        $advisees = $instructor
            ? $instructor->advisees()
                ->where(function ($q) use ($programIds) {
                    $q->whereIn('program_id', $programIds)->orWhereNull('program_id');
                })
                ->orderBy('section')
                ->orderBy('last_name')
                ->get()
            : collect();

        $candidateStudents = Student::query()
            ->where(function ($q) use ($programIds) {
                $q->whereIn('program_id', $programIds)->orWhereNull('program_id');
            })
            ->orderBy('section')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'section']);

        $adviseesEnrollStatusByStudentId = [];
        $adviseesGradesByStudentId = [];

        if ($instructor && $this->selectedSubjectId && $this->selectedTermId) {
            $adviseeIds = $advisees->pluck('id')->all();

            if (! empty($adviseeIds)) {
                $adviseesEnrollStatusByStudentId = StudentSubjectEnrollment::query()
                    ->where('subject_id', $this->selectedSubjectId)
                    ->where('term_id', $this->selectedTermId)
                    ->whereIn('student_id', $adviseeIds)
                    ->pluck('status', 'student_id')
                    ->toArray();

                $adviseesGradesByStudentId = Grade::query()
                    ->where('subject_id', $this->selectedSubjectId)
                    ->where('term_id', $this->selectedTermId)
                    ->whereIn('student_id', $adviseeIds)
                    ->get()
                    ->keyBy('student_id')
                    ;
            }
        }

        return view('livewire.chair-instructor-details', [
            'instructor' => $instructor,
            'subjects' => $subjects,
            'terms' => $terms,
            'enrollments' => $enrollments,
            'gradesByStudentId' => $gradesByStudentId,
            'advisees' => $advisees,
            'candidateStudents' => $candidateStudents,
            'adviseesEnrollStatusByStudentId' => $adviseesEnrollStatusByStudentId,
            'adviseesGradesByStudentId' => $adviseesGradesByStudentId,
        ]);
    }

    public function saveAdvising(): void
    {
        $programIds = $this->getChairProgramIds();
        abort_if(empty($programIds), 403);

        $instructor = Instructor::query()->whereKey($this->instructorId)->firstOrFail();

        // Only allow adviser assignment updates for students within the chair's programs.
        $candidateIds = Student::query()
            ->where(function ($q) use ($programIds) {
                $q->whereIn('program_id', $programIds)->orWhereNull('program_id');
            })
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $selectedIds = collect($this->advisingStudentIds)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->filter(fn ($id) => in_array($id, $candidateIds, true))
            ->values()
            ->all();

        $currentIds = $instructor
            ->advisees()
            ->where(function ($q) use ($programIds) {
                $q->whereIn('program_id', $programIds)->orWhereNull('program_id');
            })
            ->pluck('students.id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $toDetach = array_values(array_diff($currentIds, $selectedIds));
        $toAttach = array_values(array_diff($selectedIds, $currentIds));

        if (! empty($toDetach)) {
            $instructor->advisees()->detach($toDetach);
        }

        if (! empty($toAttach)) {
            $instructor->advisees()->attach($toAttach);
        }

        // Keep checkbox state consistent with DB.
        $this->advisingStudentIds = $selectedIds;

        $this->dispatch('saved');
    }
}


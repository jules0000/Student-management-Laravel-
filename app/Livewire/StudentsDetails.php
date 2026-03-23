<?php

namespace App\Livewire;

use App\Models\Grade;
use App\Models\StudentSubjectEnrollment;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Term;
use Livewire\Component;

class StudentsDetails extends Component
{
    public ?int $selectedStudentId = null;
    public ?int $selectedTermId = null;
    public ?int $selectedSubjectId = null;

    public function mount(
        ?int $selectedStudentId = null,
        ?int $selectedTermId = null,
        ?int $selectedSubjectId = null
    ): void
    {
        if ($selectedStudentId !== null) {
            $this->selectedStudentId = $selectedStudentId;
        }
        if ($selectedTermId !== null) {
            $this->selectedTermId = $selectedTermId;
        }
        if ($selectedSubjectId !== null) {
            $this->selectedSubjectId = $selectedSubjectId;
        }

        $query = Student::query()->orderBy('id');
        $visibleIds = null;

        // If multiple guards are accidentally active in the same session,
        // prefer Program Chair over Instructor so the chair UI shows the correct students.
        if (auth('program_chair')->check()) {
            $visibleIds = $this->getChairVisibleStudentIds();
            $query->whereIn('id', $visibleIds);
        } elseif (auth('instructor')->check()) {
            $visibleIds = $this->getInstructorVisibleStudentIds();
            $query->whereIn('id', $visibleIds);
        }

        // Respect a pre-selected student coming from instructor portal links.
        if ($this->selectedStudentId) {
            $isVisible = is_array($visibleIds)
                ? in_array($this->selectedStudentId, $visibleIds, true)
                : $query->whereKey($this->selectedStudentId)->exists();

            if ($isVisible) {
                return;
            }
        }

        $first = $query->first();
        $this->selectedStudentId = $first?->id;
    }

    public function updatedSelectedStudentId(): void
    {
        // No-op; render will reload details for the selected student.
    }

    /**
     * Students this instructor should be able to see:
     * - their advising class (advisees)
     * - plus students enrolled (pending/approved) in subjects they teach
     *
     * @return int[]
     */
    private function getInstructorVisibleStudentIds(): array
    {
        $instructor = auth('instructor')->user();

        $adviseeIds = $instructor->advisees()->pluck('students.id')->all();
        $subjectIds = $instructor->subjects()->pluck('subjects.id')->all();

        $courseStudentIds = StudentSubjectEnrollment::query()
            ->whereIn('subject_id', $subjectIds)
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('student_id')
            ->all();

        return array_values(array_unique(array_merge($adviseeIds, $courseStudentIds)));
    }

    /**
     * Program chair should only see students in their program(s).
     *
     * @return int[]
     */
    private function getChairVisibleStudentIds(): array
    {
        $chair = auth('program_chair')->user();
        $programIds = $chair?->programs()->pluck('programs.id')->all() ?? [];

        if (empty($programIds)) {
            return [];
        }

        return Student::query()
            // Some existing seed/imports may leave `program_id` NULL.
            // Chairs should still be able to see and advise those students.
            ->where(function ($q) use ($programIds) {
                $q->whereIn('program_id', $programIds)
                  ->orWhereNull('program_id');
            })
            ->pluck('id')
            ->all();
    }

    public function render()
    {
        $studentQuery = Student::query()
            ->select(['id', 'first_name', 'last_name', 'section'])
            ->orderByDesc('id');

        $visibleIds = null;
        $terms = Term::query()->orderBy('id')->get();
        $subjects = Subject::query()->orderBy('name')->get();

        // Prefer Program Chair over Instructor for the same reason as in `mount()`.
        if (auth('program_chair')->check()) {
            $visibleIds = $this->getChairVisibleStudentIds();
            $studentQuery->whereIn('id', $visibleIds);
        } elseif (auth('instructor')->check()) {
            $visibleIds = $this->getInstructorVisibleStudentIds();
            $studentQuery->whereIn('id', $visibleIds);
        }

        $students = $studentQuery->get();

        $selectedStudent = null;
        $gradesByTerm = collect();

        // If the selected student id no longer exists in the currently-visible set
        // (e.g., user switched roles/guards), fall back to the first available student.
        $studentIds = $students->pluck('id')->all();
        $effectiveSelectedStudentId = $this->selectedStudentId;
        if ($effectiveSelectedStudentId && ! in_array($effectiveSelectedStudentId, $studentIds, true)) {
            $effectiveSelectedStudentId = $students->first()?->id;
        }

        if ($effectiveSelectedStudentId) {
            $selectedStudentQuery = Student::query()
                ->with(['advisers', 'grades.subject', 'grades.term'])
                ->where('id', $effectiveSelectedStudentId);

            if (is_array($visibleIds)) {
                $selectedStudentQuery->whereIn('id', $visibleIds);
            }

            $selectedStudent = $selectedStudentQuery->first();

            if ($selectedStudent) {
                $studentGrades = $selectedStudent->grades;
                if ($this->selectedTermId) {
                    $studentGrades = $studentGrades->where('term_id', $this->selectedTermId);
                }
                if ($this->selectedSubjectId) {
                    $studentGrades = $studentGrades->where('subject_id', $this->selectedSubjectId);
                }

                $gradesByTerm = $studentGrades->groupBy('term_id');
            }
        }

        return view('livewire.students-details', [
            'students' => $students,
            'selectedStudent' => $selectedStudent,
            'gradesByTerm' => $gradesByTerm,
            'terms' => $terms,
            'subjects' => $subjects,
        ]);
    }
}


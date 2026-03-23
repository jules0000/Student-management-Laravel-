<?php

namespace App\Livewire;

use App\Models\Grade;
use App\Models\StudentSubjectEnrollment;
use App\Models\Term;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class StudentPortal extends Component
{
    public function render()
    {
        $student = auth('student')->user();

        $terms = Term::query()->orderBy('id')->get();

        // Load all enrollment records for this student so subjects show in the UI
        // even before an instructor/office publishes grades.
        $enrollments = StudentSubjectEnrollment::query()
            ->where('student_id', $student->id)
            ->with(['subject', 'term'])
            ->get();

        // Load all grade rows so we can show "pending chair approval" vs "not yet graded".
        $grades = Grade::query()
            ->where('student_id', $student->id)
            ->with(['subject', 'term'])
            ->get();

        $gradeBySubjectAndTerm = $grades->keyBy(function (Grade $grade) {
            return (int) $grade->subject_id . '|' . (int) $grade->term_id;
        });

        $enrollmentsByTerm = $enrollments->groupBy('term_id');
        $rowsByTerm = $enrollmentsByTerm->map(function ($termEnrollments) use ($gradeBySubjectAndTerm) {
            return $termEnrollments->map(function ($enrollment) use ($gradeBySubjectAndTerm) {
                $key = (int) $enrollment->subject_id . '|' . (int) $enrollment->term_id;
                $grade = $gradeBySubjectAndTerm->get($key);

                return (object) [
                    'subject' => $enrollment->subject,
                    'grade' => $grade,
                    'enrollmentStatus' => $enrollment->status,
                ];
            });
        });

        $adviser = $student->advisers()->first();

        $supportsGradeComponents = Schema::hasColumn('grades', 'prelim')
            && Schema::hasColumn('grades', 'midterm')
            && Schema::hasColumn('grades', 'final_exam');

        return view('livewire.student-portal', [
            'student' => $student,
            'terms' => $terms,
            'rowsByTerm' => $rowsByTerm,
            'adviser' => $adviser,
            'supportsGradeComponents' => $supportsGradeComponents,
        ]);
    }
}


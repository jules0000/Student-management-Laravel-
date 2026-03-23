<?php

namespace App\Livewire;

use App\Models\Grade;
use App\Models\Student;
use App\Models\StudentSubjectEnrollment;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $role = $this->detectRole();

        // Shared defaults so the Blade template can safely branch.
        $sectionCounts = collect();
        $sectionMaxCount = 0;
        $recentStudents = collect();

        $totalStudents = 0;
        $activeSections = 0;
        $newThisWeek = 0;

        $totalAdvisees = 0;
        $subjectsCount = 0;
        $pendingEnrollmentCount = 0;
        $pendingGradeCount = 0;

        $approvedGradesCount = 0;
        $termsCount = 0;
        $supportsGradeComponents = false;
        $recentGrades = collect();
        $adviser = null;

        $hasGradeStatus = Schema::hasColumn('grades', 'status');
        $hasApprovedBy = Schema::hasColumn('grades', 'approved_by_program_chair_id');
        $hasApprovedAt = Schema::hasColumn('grades', 'approved_at');

        if ($role === 'admin') {
            $students = Student::query()->latest()->get();

            $sectionCounts = $students
                ->filter(fn (Student $student) => filled($student->section))
                ->groupBy('section')
                ->map->count()
                ->sortDesc();
            $activeSections = $sectionCounts->count();

            $newThisWeek = Student::query()
                ->where('created_at', '>=', now()->subDays(7))
                ->count();

            $totalStudents = $students->count();
            $recentStudents = $students->take(5);
            $sectionMaxCount = $sectionCounts->max() ?? 0;
        } elseif ($role === 'instructor') {
            $instructor = auth('instructor')->user();

            $adviseeIds = $instructor?->advisees()->pluck('students.id')->all() ?? [];
            $subjectIds = $instructor?->subjects()->pluck('subjects.id')->all() ?? [];
            $courseStudentIds = StudentSubjectEnrollment::query()
                ->whereIn('subject_id', $subjectIds)
                ->where('status', 'approved')
                ->pluck('student_id')
                ->all();
            $visibleStudentIds = array_values(array_unique(array_merge($adviseeIds, $courseStudentIds)));

            $totalAdvisees = count($adviseeIds);
            $subjectsCount = count($subjectIds);

            if (! empty($visibleStudentIds)) {
                $students = Student::query()
                    ->whereIn('id', $visibleStudentIds)
                    ->latest()
                    ->get();

                $sectionCounts = $students
                    ->filter(fn (Student $student) => filled($student->section))
                    ->groupBy('section')
                    ->map->count()
                    ->sortDesc();
                $activeSections = $sectionCounts->count();
                $recentStudents = $students->take(5);
                $sectionMaxCount = $sectionCounts->max() ?? 0;
            }

            if (! empty($adviseeIds) && ! empty($subjectIds)) {
                $pendingEnrollmentCount = StudentSubjectEnrollment::query()
                    ->whereIn('student_id', $adviseeIds)
                    ->whereIn('subject_id', $subjectIds)
                    ->whereRaw('LOWER(status) = ?', ['pending'])
                    ->count();

                $pendingGradeQuery = Grade::query()
                    ->whereIn('student_id', $adviseeIds)
                    ->whereIn('subject_id', $subjectIds);

                if ($hasGradeStatus) {
                    $pendingGradeQuery->whereRaw('LOWER(status) = ?', ['pending']);
                } elseif ($hasApprovedBy && $hasApprovedAt) {
                    $pendingGradeQuery
                        ->whereNull('approved_by_program_chair_id')
                        ->whereNull('approved_at');
                } elseif ($hasApprovedBy) {
                    $pendingGradeQuery->whereNull('approved_by_program_chair_id');
                } elseif ($hasApprovedAt) {
                    $pendingGradeQuery->whereNull('approved_at');
                } else {
                    // If we can't identify approval state from the DB schema, avoid miscounting.
                    $pendingGradeQuery = null;
                }

                $pendingGradeCount = $pendingGradeQuery?->count() ?? 0;
            }
        } elseif ($role === 'program_chair') {
            $chair = auth('program_chair')->user();
            $programIds = $chair?->programs()->pluck('programs.id')->all() ?? [];

            if (! empty($programIds)) {
                $students = Student::query()
                    ->whereIn('program_id', $programIds)
                    ->latest()
                    ->get();

                $totalStudents = $students->count();
                $sectionCounts = $students
                    ->filter(fn (Student $student) => filled($student->section))
                    ->groupBy('section')
                    ->map->count()
                    ->sortDesc();
                $activeSections = $sectionCounts->count();
                $recentStudents = $students->take(5);
                $sectionMaxCount = $sectionCounts->max() ?? 0;

                $studentIds = $students->pluck('id')->all();

                $pendingEnrollmentCount = StudentSubjectEnrollment::query()
                    ->whereIn('student_id', $studentIds)
                    ->whereRaw('LOWER(status) = ?', ['pending'])
                    ->count();

                $pendingGradeQuery = Grade::query()->whereIn('student_id', $studentIds);
                if ($hasGradeStatus) {
                    $pendingGradeQuery->whereRaw('LOWER(status) = ?', ['pending']);
                } elseif ($hasApprovedBy && $hasApprovedAt) {
                    $pendingGradeQuery
                        ->whereNull('approved_by_program_chair_id')
                        ->whereNull('approved_at');
                } elseif ($hasApprovedBy) {
                    $pendingGradeQuery->whereNull('approved_by_program_chair_id');
                } elseif ($hasApprovedAt) {
                    $pendingGradeQuery->whereNull('approved_at');
                } else {
                    // If we can't identify approval state from the DB schema, avoid miscounting.
                    $pendingGradeQuery = null;
                }

                $pendingGradeCount = $pendingGradeQuery?->count() ?? 0;
            }
        } elseif ($role === 'student') {
            $student = auth('student')->user();

            $adviser = $student?->advisers()->first();

            $enrollments = StudentSubjectEnrollment::query()
                ->where('student_id', $student->id)
                ->with(['subject', 'term'])
                ->orderByDesc('id')
                ->get();

            $grades = Grade::query()
                ->where('student_id', $student->id)
                ->with(['subject', 'term'])
                ->orderByDesc('approved_at')
                ->get();

            $publishedGrades = $grades->filter(fn (Grade $g) => $g->isPublishedToStudent());

            $approvedGradesCount = $publishedGrades->count();
            $termsCount = $publishedGrades->pluck('term_id')->unique()->count();

            $gradeBySubjectAndTerm = $grades->keyBy(function (Grade $grade) {
                return (int) $grade->subject_id . '|' . (int) $grade->term_id;
            });

            $recentEnrollments = $enrollments->take(5)->map(function (StudentSubjectEnrollment $enrollment) use ($gradeBySubjectAndTerm) {
                $key = (int) $enrollment->subject_id . '|' . (int) $enrollment->term_id;
                $grade = $gradeBySubjectAndTerm->get($key);

                return (object) [
                    'subject' => $enrollment->subject,
                    'term' => $enrollment->term,
                    'enrollmentStatus' => $enrollment->status,
                    'grade' => $grade,
                ];
            });

            $supportsGradeComponents = Schema::hasColumn('grades', 'prelim')
                && Schema::hasColumn('grades', 'midterm')
                && Schema::hasColumn('grades', 'final_exam');
        }

        return view('dashboard', [
            'role' => $role,
            'sectionCounts' => $sectionCounts,
            'sectionMaxCount' => $sectionMaxCount,
            'recentStudents' => $recentStudents,

            'totalStudents' => $totalStudents,
            'activeSections' => $activeSections,
            'newThisWeek' => $newThisWeek,

            'totalAdvisees' => $totalAdvisees,
            'subjectsCount' => $subjectsCount,
            'pendingEnrollmentCount' => $pendingEnrollmentCount,
            'pendingGradeCount' => $pendingGradeCount,

            'approvedGradesCount' => $approvedGradesCount,
            'termsCount' => $termsCount,
            'recentEnrollments' => $recentEnrollments ?? collect(),
            'adviser' => $adviser,
            'supportsGradeComponents' => $supportsGradeComponents,
        ]);
    }

    private function detectRole(): string
    {
        if (auth('admin')->check()) {
            return 'admin';
        }

        if (auth('program_chair')->check()) {
            return 'program_chair';
        }

        // Prefer Program Chair over Instructor if multiple guards are active in the session.
        if (auth('instructor')->check()) {
            return 'instructor';
        }

        if (auth('student')->check()) {
            return 'student';
        }

        return 'guest';
    }
}


<?php

namespace App\Livewire;

use App\Models\Instructor;
use App\Models\Subject;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ChairInstructorsTable extends Component
{
    public function render()
    {
        $chair = auth('program_chair')->user();
        $programIds = $chair?->programs()->pluck('programs.id')->all() ?? [];

        if (empty($programIds)) {
            return view('livewire.chair-instructors-table', [
                'instructors' => collect(),
                'subjectsCountByInstructorId' => [],
                'adviseesCountByInstructorId' => [],
            ]);
        }

        $instructorsFromSubjects = Subject::query()
            ->whereIn('program_id', $programIds)
            ->distinct()
            ->pluck('instructor_id')
            ->all();

        $instructorsFromAdvisers = DB::table('adviser_assignments')
            ->join('students', 'students.id', '=', 'adviser_assignments.student_id')
            ->whereIn('students.program_id', $programIds)
            ->distinct()
            ->pluck('adviser_assignments.instructor_id')
            ->all();

        $instructorIds = array_values(array_unique(array_merge($instructorsFromSubjects, $instructorsFromAdvisers)));

        $instructors = Instructor::query()
            ->whereIn('id', $instructorIds)
            ->orderBy('name')
            ->get();

        $subjectsCountByInstructorId = Subject::query()
            ->whereIn('program_id', $programIds)
            ->select('instructor_id')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('instructor_id')
            ->pluck('cnt', 'instructor_id')
            ->toArray();

        $adviseesCountByInstructorId = DB::table('adviser_assignments')
            ->join('students', 'students.id', '=', 'adviser_assignments.student_id')
            ->whereIn('students.program_id', $programIds)
            ->select('adviser_assignments.instructor_id')
            ->selectRaw('COUNT(*) as cnt')
            ->groupBy('adviser_assignments.instructor_id')
            ->pluck('cnt', 'instructor_id')
            ->toArray();

        return view('livewire.chair-instructors-table', [
            'instructors' => $instructors,
            'subjectsCountByInstructorId' => $subjectsCountByInstructorId,
            'adviseesCountByInstructorId' => $adviseesCountByInstructorId,
        ]);
    }
}


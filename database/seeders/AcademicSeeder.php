<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Instructor;
use App\Models\Subject;
use App\Models\Program;
use App\Models\Term;
use Illuminate\Database\Seeder;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $instructor = Instructor::where('email', 'instructor@example.com')->first();
        if (! $instructor) {
            return;
        }

        $department = Department::firstOrCreate(['name' => 'General Department']);
        $program = Program::query()->orderBy('id')->first();

        foreach ([
            ['name' => 'Term 1', 'school_year' => '2025-2026'],
            ['name' => 'Term 2', 'school_year' => '2025-2026'],
            ['name' => 'Term 3', 'school_year' => '2025-2026'],
        ] as $term) {
            Term::firstOrCreate([
                'name' => $term['name'],
                'school_year' => $term['school_year'],
            ]);
        }

        foreach ([
            ['name' => 'Mathematics', 'code' => 'MATH'],
            ['name' => 'Science', 'code' => 'SCI'],
            ['name' => 'English', 'code' => 'ENG'],
        ] as $subject) {
            $row = Subject::firstOrCreate([
                'name' => $subject['name'],
                'code' => $subject['code'],
                'instructor_id' => $instructor->id,
            ]);

            // Ensure these subjects belong to a program/department for chair restriction logic.
            if ($program) {
                $row->update([
                    'department_id' => $department->id,
                    'program_id' => $program->id,
                ]);
            }
        }
    }
}


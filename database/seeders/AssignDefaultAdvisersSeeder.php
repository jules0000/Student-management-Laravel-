<?php

namespace Database\Seeders;

use App\Models\Instructor;
use App\Models\Student;
use Illuminate\Database\Seeder;

class AssignDefaultAdvisersSeeder extends Seeder
{
    public function run(): void
    {
        $instructors = Instructor::query()
            ->orderBy('id')
            ->get(['id', 'email']);

        if ($instructors->isEmpty()) {
            return;
        }

        $students = Student::query()
            ->orderBy('id')
            ->get(['id']);

        if ($students->isEmpty()) {
            return;
        }

        // Create a simple rotating adviser assignment so multiple instructors show up.
        // NOTE: Adviser assignment table is unique by (instructor_id, student_id),
        // so repeated seeding is safe (no duplicates).
        foreach ($students as $index => $student) {
            $instructor = $instructors[$index % $instructors->count()];

            // Use relation pivot to keep it consistent with the rest of the app.
            $student->advisers()->syncWithoutDetaching([
                $instructor->id,
            ]);
        }
    }
}


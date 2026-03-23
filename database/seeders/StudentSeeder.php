<?php

namespace Database\Seeders;

use App\Models\Student;
use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        // This app uses multiple auth guards (admin/student/instructor), so students need
        // their own email+hashed password columns in the `students` table.
        //
        // The original seeder only created 1 student, which makes pages like:
        // - `/students/details`
        // - chair instructor "Set Advisers"
        // show only a single record.
        $seedStudents = [
            [
                'email' => 'student@example.com',
                'last_name' => 'One',
                'section' => 'A',
                'birthdate' => '2000-01-01',
                'address' => '123 Student Street',
            ],
            [
                'email' => 'student2@example.com',
                'last_name' => 'Two',
                'section' => 'A',
                'birthdate' => '2000-02-02',
                'address' => '456 Student Street',
            ],
            [
                'email' => 'student3@example.com',
                'last_name' => 'Three',
                'section' => 'B',
                'birthdate' => '2000-03-03',
                'address' => '789 Student Street',
            ],
            [
                'email' => 'student4@example.com',
                'last_name' => 'Four',
                'section' => 'B',
                'birthdate' => '2000-04-04',
                'address' => '321 Student Street',
            ],
            [
                'email' => 'student5@example.com',
                'last_name' => 'Five',
                'section' => 'C',
                'birthdate' => '2000-05-05',
                'address' => '654 Student Street',
            ],
        ];

        $defaultProgramId = Program::query()->orderBy('id')->value('id');

        foreach ($seedStudents as $seed) {
            if (Student::query()->where('email', $seed['email'])->exists()) {
                continue;
            }

            Student::create([
                'first_name' => 'Student',
                'middle_name' => null,
                'last_name' => $seed['last_name'],
                'birthdate' => $seed['birthdate'],
                'photo_url' => null,
                'section' => $seed['section'],
                'address' => $seed['address'],
                // Default program makes chair/instructor views show all seeded students immediately.
                'program_id' => $defaultProgramId,
                'email' => $seed['email'],
                'password' => Hash::make('password123'),
            ]);
        }
    }
}


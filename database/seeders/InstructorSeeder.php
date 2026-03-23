<?php

namespace Database\Seeders;

use App\Models\Instructor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class InstructorSeeder extends Seeder
{
    public function run(): void
    {
        // Keep the original instructor@example.com (used by AcademicSeeder),
        // and add more instructors so chair pages can reassign instructors.
        $seedInstructors = [
            [
                'email' => 'instructor@example.com',
                'name' => 'Instructor One',
            ],
            [
                'email' => 'instructor2@example.com',
                'name' => 'Instructor Two',
            ],
            [
                'email' => 'instructor3@example.com',
                'name' => 'Instructor Three',
            ],
        ];

        foreach ($seedInstructors as $seed) {
            if (Instructor::where('email', $seed['email'])->exists()) {
                continue;
            }

            Instructor::create([
                'email' => $seed['email'],
                'password' => Hash::make('password123'),
                'name' => $seed['name'],
            ]);
        }
    }
}


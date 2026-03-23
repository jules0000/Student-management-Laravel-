<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Keep default Laravel seed minimal; this app uses multiple guards (admin/student/instructor).

        // Admin seed
        $this->call(AdminSeeder::class);

        // Academic/department/program seed data
        $this->call(\Database\Seeders\DepartmentProgramSeeder::class);

        // Program chair seed data
        $this->call(\Database\Seeders\ProgramChairSeeder::class);

        // Student seed data (must run before assigning default program / advisers)
        $this->call(\Database\Seeders\StudentSeeder::class);

        // Assign default program to existing student rows
        $this->call(\Database\Seeders\AssignDefaultProgramToStudentsSeeder::class);

        // Instructor + academic seed data
        $this->call(InstructorSeeder::class);

        // Default adviser assignments so instructor/chair UIs have multiple rows.
        $this->call(\Database\Seeders\AssignDefaultAdvisersSeeder::class);

        $this->call(AcademicSeeder::class);

        // Optionally keep the default user record (not used by this app's login)
        if (! User::query()->where('email', 'test@example.com')->exists()) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }
    }
}

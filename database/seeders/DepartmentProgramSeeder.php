<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Program;
use Illuminate\Database\Seeder;

class DepartmentProgramSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::firstOrCreate(
            ['name' => 'General Department']
        );

        Program::firstOrCreate(
            ['name' => 'BS Program', 'department_id' => $department->id],
            ['department_id' => $department->id]
        );
    }
}


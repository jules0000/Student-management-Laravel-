<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Student;
use Illuminate\Database\Seeder;

class AssignDefaultProgramToStudentsSeeder extends Seeder
{
    public function run(): void
    {
        $programId = Program::query()->orderBy('id')->value('id');
        if (! $programId) {
            return;
        }

        Student::query()
            ->whereNull('program_id')
            ->update(['program_id' => $programId]);
    }
}


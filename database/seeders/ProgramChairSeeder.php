<?php

namespace Database\Seeders;

use App\Models\ProgramChair;
use App\Models\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProgramChairSeeder extends Seeder
{
    public function run(): void
    {
        $chair = ProgramChair::query()->firstOrCreate(
            ['email' => 'chair@example.com'],
            [
                'password' => Hash::make('password123'),
                'name' => 'Program Chair',
            ]
        );

        $program = Program::query()->orderBy('id')->first();
        if ($program) {
            $chair->programs()->syncWithoutDetaching([$program->id]);
        }
    }
}


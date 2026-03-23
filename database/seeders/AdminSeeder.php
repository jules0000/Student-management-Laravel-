<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        if (! Admin::where('email', 'admin@example.com')->exists()) {
            Admin::create([
                'email' => 'admin@example.com',
                'password' => Hash::make('password123'),
                'name' => 'Super Admin',
            ]);
        }
    }
}


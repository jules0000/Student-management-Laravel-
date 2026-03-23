<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('program_chair_programs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('program_chair_id');
            $table->unsignedBigInteger('program_id');
            $table->timestamps();

            $table->unique(['program_chair_id', 'program_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('program_chair_programs');
    }
};


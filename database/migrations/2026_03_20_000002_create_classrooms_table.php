<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instructor_id')->constrained('instructors')->cascadeOnDelete();

            // In UI, instructor can label the classroom (e.g., "Room 101").
            $table->string('room_label');

            // Instructor geolocation captured at setup time.
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);

            // Allowed radius (meters) for student attendance geolocation.
            $table->unsignedInteger('radius_m')->default(100);

            $table->timestamps();

            $table->index(['instructor_id', 'latitude', 'longitude']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};


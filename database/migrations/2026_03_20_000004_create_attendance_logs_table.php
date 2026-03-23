<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('class_session_id')->constrained('class_sessions')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();

            $table->timestamp('marked_at')->useCurrent();

            // Student geolocation at time of marking.
            $table->decimal('geolocation_lat', 10, 7);
            $table->decimal('geolocation_lng', 10, 7);

            // Computed distance between student and classroom geofence point.
            $table->decimal('distance_m', 10, 2)->nullable();
            $table->unsignedInteger('geofence_radius_m')->nullable();

            // Face scan outcome from the client.
            $table->boolean('face_detected')->default(false);

            // Optional photo saved for audit/debug.
            $table->string('face_image_path')->nullable();

            // Geolocation match result.
            $table->boolean('location_match')->default(false);

            // present | failed_face | rejected_location
            $table->string('status')->default('rejected');

            $table->timestamps();

            $table->unique(['class_session_id', 'student_id']);
            $table->index(['student_id', 'class_session_id']);
            $table->index(['marked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};


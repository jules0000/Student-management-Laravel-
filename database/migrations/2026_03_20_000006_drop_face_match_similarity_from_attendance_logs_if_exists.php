<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('attendance_logs', 'face_match_similarity')) {
            Schema::table('attendance_logs', function (Blueprint $table) {
                $table->dropColumn('face_match_similarity');
            });
        }
    }

    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->decimal('face_match_similarity', 6, 2)->nullable()->after('face_image_path');
        });
    }
};

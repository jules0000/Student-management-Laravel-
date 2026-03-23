<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->longText('photo_url')->nullable()->after('name');
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->longText('photo_url')->nullable()->after('name');
        });

        Schema::table('program_chairs', function (Blueprint $table) {
            $table->longText('photo_url')->nullable()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('admins', function (Blueprint $table) {
            $table->dropColumn('photo_url');
        });

        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn('photo_url');
        });

        Schema::table('program_chairs', function (Blueprint $table) {
            $table->dropColumn('photo_url');
        });
    }
};


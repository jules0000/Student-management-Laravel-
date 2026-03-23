<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('remarks');
            $table->unsignedBigInteger('approved_by_program_chair_id')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by_program_chair_id');
        });
    }

    public function down(): void
    {
        Schema::table('grades', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_by_program_chair_id', 'approved_at']);
        });
    }
};


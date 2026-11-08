<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->json('working_days')->nullable();
            $table->time('work_start_time')->nullable();
            $table->time('work_end_time')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('worker_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'working_days',
                'work_start_time',
                'work_end_time',
            ]);
        });
    }
};
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('worker_unavailable_dates', function (Blueprint $table) {
            $table->id();

            $table->foreignId('worker_profile_id')
                ->constrained('worker_profiles')
                ->cascadeOnDelete();

            $table->date('unavailable_date');

            $table->string('reason', 255)->nullable();

            $table->timestamps();

            $table->unique(
                ['worker_profile_id', 'unavailable_date'],
                'worker_unavailable_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('worker_unavailable_dates');
    }
};
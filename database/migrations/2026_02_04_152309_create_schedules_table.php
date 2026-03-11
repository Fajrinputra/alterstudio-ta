<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jadwal penugasan fotografer/editor untuk project.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('photographer_id')->nullable()->constrained('users');
            $table->foreignId('editor_id')->nullable()->constrained('users');
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('location');
            $table->string('status')->default('SCHEDULED');
            $table->timestamps();

            // Satu project hanya boleh punya satu jadwal aktif.
            $table->unique('project_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};

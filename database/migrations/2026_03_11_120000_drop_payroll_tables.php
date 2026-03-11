<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cleanup modul payroll legacy yang sudah tidak dipakai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payroll_records');
        Schema::dropIfExists('payroll_rules');
    }

    public function down(): void
    {
        Schema::create('payroll_rules', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->unsignedBigInteger('base_salary')->default(0);
            $table->unsignedBigInteger('incentive_per_project')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('payroll_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->date('period_start');
            $table->date('period_end');
            $table->unsignedInteger('projects_count')->default(0);
            $table->unsignedBigInteger('base_salary')->default(0);
            $table->unsignedBigInteger('incentive')->default(0);
            $table->unsignedBigInteger('total')->default(0);
            $table->timestamp('generated_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'period_start', 'period_end'], 'payroll_user_period_unique');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payroll_rules', function (Blueprint $table) {
            $table->id();
            $table->string('role');
            $table->unsignedBigInteger('base_salary')->default(0);
            $table->unsignedBigInteger('incentive_per_project')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['role', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_rules');
    }
};

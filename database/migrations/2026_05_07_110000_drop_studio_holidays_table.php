<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('studio_holidays');
    }

    public function down(): void
    {
        Schema::create('studio_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_location_id')->constrained()->cascadeOnDelete();
            $table->date('holiday_date');
            $table->string('name');
            $table->string('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['studio_location_id', 'holiday_date'], 'studio_holidays_location_date_unique');
        });
    }
};

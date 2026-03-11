<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daftar ruangan per cabang studio.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('studio_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_location_id')->constrained('studio_locations')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_rooms');
    }
};

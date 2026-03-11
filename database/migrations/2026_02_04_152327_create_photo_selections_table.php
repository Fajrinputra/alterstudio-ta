<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan daftar foto yang dipilih client untuk editing.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('photo_selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users');
            $table->foreignId('media_asset_id')->constrained('media_assets');
            $table->timestamp('selected_at')->useCurrent();
            // Foto yang sama tidak boleh dipilih dua kali pada project yang sama.
            $table->unique(['project_id','media_asset_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_selections');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel pin revisi client pada posisi tertentu di gambar.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('revision_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained('media_assets');
            $table->foreignId('client_id')->constrained('users');
            $table->decimal('x', 8, 4);
            $table->decimal('y', 8, 4);
            $table->text('comment');
            $table->enum('status', ['OPEN','RESOLVED'])->default('OPEN');
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('revision_pins');
    }
};

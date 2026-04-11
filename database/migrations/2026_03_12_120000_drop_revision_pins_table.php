<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove legacy revision pin table because revision workflow is not used.
     */
    public function up(): void
    {
        Schema::dropIfExists('revision_pins');
    }

    /**
     * Recreate table if rollback is required.
     */
    public function down(): void
    {
        Schema::create('revision_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('x', 8, 3);
            $table->decimal('y', 8, 3);
            $table->text('comment');
            $table->string('status')->default('OPEN');
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
        });
    }
};


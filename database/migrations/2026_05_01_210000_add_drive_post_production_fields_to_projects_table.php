<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('raw_drive_url', 2048)->nullable();
            $table->foreignId('raw_drive_uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('raw_drive_uploaded_at')->nullable();

            $table->text('edit_photo_codes')->nullable();
            $table->text('edit_request_note')->nullable();
            $table->timestamp('edit_requested_at')->nullable();

            $table->string('final_drive_url', 2048)->nullable();
            $table->text('final_message')->nullable();
            $table->foreignId('final_drive_uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('final_drive_uploaded_at')->nullable();

        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('final_drive_uploaded_by');
            $table->dropConstrainedForeignId('raw_drive_uploaded_by');

            $table->dropColumn([
                'raw_drive_url',
                'raw_drive_uploaded_at',
                'edit_photo_codes',
                'edit_request_note',
                'edit_requested_at',
                'final_drive_url',
                'final_message',
                'final_drive_uploaded_at',
            ]);
        });
    }
};

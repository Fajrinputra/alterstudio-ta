<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            foreach (['city', 'facilities'] as $column) {
                if (Schema::hasColumn('studio_locations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            foreach ($this->projectRevisionColumns() as $column) {
                if (Schema::hasColumn('projects', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('studio_locations', function (Blueprint $table) {
            if (! Schema::hasColumn('studio_locations', 'city')) {
                $table->string('city', 100)->nullable()->after('description');
            }

            if (! Schema::hasColumn('studio_locations', 'facilities')) {
                $table->longText('facilities')->nullable()->after('map_url');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'revision_request_note')) {
                $table->text('revision_request_note')->nullable()->after('final_drive_uploaded_at');
            }

            if (! Schema::hasColumn('projects', 'revision_requested_at')) {
                $table->timestamp('revision_requested_at')->nullable()->after('revision_request_note');
            }

            if (! Schema::hasColumn('projects', 'revision_completed_message')) {
                $table->text('revision_completed_message')->nullable()->after('revision_requested_at');
            }

            if (! Schema::hasColumn('projects', 'revision_completed_by')) {
                $table->foreignId('revision_completed_by')->nullable()->after('revision_completed_message')->constrained('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('projects', 'revision_completed_at')) {
                $table->timestamp('revision_completed_at')->nullable()->after('revision_completed_by');
            }
        });
    }

    protected function projectRevisionColumns(): array
    {
        return [
            'revision_request_note',
            'revision_requested_at',
            'revision_completed_message',
            'revision_completed_by',
            'revision_completed_at',
        ];
    }
};

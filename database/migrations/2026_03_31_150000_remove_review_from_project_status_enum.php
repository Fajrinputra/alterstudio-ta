<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('projects')
            ->where('status', 'REVIEW')
            ->update(['status' => Project::STATUS_EDITING]);

        DB::statement("
            ALTER TABLE projects
            MODIFY status ENUM('DRAFT','SCHEDULED','SHOOT_DONE','EDITING','FINAL')
            NOT NULL DEFAULT 'DRAFT'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE projects
            MODIFY status ENUM('DRAFT','SCHEDULED','SHOOT_DONE','EDITING','REVIEW','FINAL')
            NOT NULL DEFAULT 'DRAFT'
        ");
    }
};

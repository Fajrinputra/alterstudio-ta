<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::table('media_assets')
            ->whereIn('type', ['PREVIEW', 'WATERMARK'])
            ->update(['type' => 'FINAL']);

        DB::statement("
            ALTER TABLE media_assets
            MODIFY type ENUM('RAW','FINAL')
            NOT NULL
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE media_assets
            MODIFY type ENUM('RAW','PREVIEW','WATERMARK','FINAL')
            NOT NULL
        ");
    }
};

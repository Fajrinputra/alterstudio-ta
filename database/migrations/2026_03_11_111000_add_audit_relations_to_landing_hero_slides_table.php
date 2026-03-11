<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan audit trail created_by/updated_by pada hero slide.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('landing_hero_slides') || !Schema::hasTable('users')) {
            return;
        }

        Schema::table('landing_hero_slides', function (Blueprint $table) {
            if (!Schema::hasColumn('landing_hero_slides', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('is_active')->constrained('users')->nullOnDelete();
            }

            if (!Schema::hasColumn('landing_hero_slides', 'updated_by')) {
                $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            }
        });

        // Backfill: ambil admin pertama sebagai pemilik awal jika tersedia.
        $adminId = DB::table('users')
            ->where('role', 'ADMIN')
            ->value('id');

        if ($adminId) {
            DB::table('landing_hero_slides')
                ->whereNull('created_by')
                ->update([
                    'created_by' => $adminId,
                    'updated_by' => $adminId,
                ]);
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('landing_hero_slides')) {
            return;
        }

        Schema::table('landing_hero_slides', function (Blueprint $table) {
            if (Schema::hasColumn('landing_hero_slides', 'updated_by')) {
                $table->dropConstrainedForeignId('updated_by');
            }
            if (Schema::hasColumn('landing_hero_slides', 'created_by')) {
                $table->dropConstrainedForeignId('created_by');
            }
        });
    }
};

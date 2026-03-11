<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan durasi default paket dalam menit.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('service_packages', 'duration_minutes')) {
                $table->integer('duration_minutes')->default(60)->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            if (Schema::hasColumn('service_packages', 'duration_minutes')) {
                $table->dropColumn('duration_minutes');
            }
        });
    }
};

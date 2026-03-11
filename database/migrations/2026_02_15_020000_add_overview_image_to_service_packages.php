<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambahkan gambar overview tunggal per paket.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('service_packages', 'overview_image')) {
                $table->string('overview_image')->nullable()->after('cover_image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            if (Schema::hasColumn('service_packages', 'overview_image')) {
                $table->dropColumn('overview_image');
            }
        });
    }
};

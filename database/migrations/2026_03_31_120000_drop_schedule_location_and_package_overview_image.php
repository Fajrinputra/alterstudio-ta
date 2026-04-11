<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('schedules', 'location')) {
            Schema::table('schedules', function (Blueprint $table) {
                $table->dropColumn('location');
            });
        }

        if (Schema::hasColumn('service_packages', 'overview_image')) {
            Schema::table('service_packages', function (Blueprint $table) {
                $table->dropColumn('overview_image');
            });
        }
    }

    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('schedules', 'location')) {
                $table->string('location')->nullable()->after('end_at');
            }
        });

        Schema::table('service_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('service_packages', 'overview_image')) {
                $table->string('overview_image')->nullable()->after('cover_image');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ekstensi metadata paket: add-on, syarat, media, dan status aktif.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            $table->unsignedInteger('max_people')->nullable()->after('price');
            $table->json('addons')->nullable()->after('features');
            $table->text('terms')->nullable()->after('addons');
            $table->string('portfolio_url')->nullable()->after('addons');
            $table->string('cover_image')->nullable()->after('portfolio_url');
            $table->boolean('is_active')->default(true)->after('cover_image');
        });
    }

    public function down(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            $table->dropColumn(['max_people', 'addons', 'terms', 'portfolio_url', 'cover_image', 'is_active']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sinkronisasi tipe data kolom agar sesuai persis dengan spesifikasi
 * Lampiran D Laporan Tugas Akhir (revisi dosen pembimbing).
 *
 * Perubahan yang dilakukan:
 *   - bookings.booking_date    : datetime  → date
 *   - bookings.addon_total     : bigint    → double (Float)
 *   - bookings.total_price     : bigint    → double (Float)
 *   - payments.amount          : bigint    → double (Float)
 *   - service_packages.price   : bigint    → double (Float)
 *   - service_packages.max_people : int unsigned → int(11)
 *   - studio_locations.address : varchar(200) → text
 *   - media_assets.path        : varchar(200) → varchar(255)
 *   - landing_hero_slides.eyebrow : varchar(100) → varchar(150)
 *   - landing_hero_slides.title   : varchar(150) → varchar(100)
 *   - landing_hero_slides.image_path : varchar(200) → varchar(255)
 *
 * Migration ini aman di SQLite (test) dan MySQL (production).
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── bookings ──────────────────────────────────────────────────────────
        Schema::table('bookings', function (Blueprint $table) {
            // booking_date: datetime → date (laporan: Date)
            $table->date('booking_date')->change();
            // addon_total & total_price: bigint → double (laporan: Float)
            $table->double('addon_total')->default(0)->change();
            $table->double('total_price')->change();
        });

        // ── payments ──────────────────────────────────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            // amount: bigint → double (laporan: Float)
            $table->double('amount')->change();
        });

        // ── service_packages ──────────────────────────────────────────────────
        Schema::table('service_packages', function (Blueprint $table) {
            // price: bigint → double (laporan: Float)
            $table->double('price')->change();
            // max_people: int unsigned → int(11) (laporan: Integer (11))
            $table->integer('max_people')->nullable()->change();
        });

        // ── studio_locations ──────────────────────────────────────────────────
        Schema::table('studio_locations', function (Blueprint $table) {
            // address: varchar(200) → text (laporan: Text)
            $table->text('address')->nullable()->change();
        });

        // ── media_assets ──────────────────────────────────────────────────────
        Schema::table('media_assets', function (Blueprint $table) {
            // path: varchar(200) → varchar(255) (laporan: Varchar (255))
            $table->string('path', 255)->change();
        });

        // ── landing_hero_slides ───────────────────────────────────────────────
        Schema::table('landing_hero_slides', function (Blueprint $table) {
            // eyebrow: varchar(100) → varchar(150) (laporan: Varchar (150))
            $table->string('eyebrow', 150)->nullable()->change();
            // title: varchar(150) → varchar(100) (laporan: Varchar (100))
            $table->string('title', 100)->change();
            // image_path: varchar(200) → varchar(255) (laporan: Varchar (255))
            $table->string('image_path', 255)->change();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dateTime('booking_date')->change();
            $table->unsignedBigInteger('addon_total')->default(0)->change();
            $table->unsignedBigInteger('total_price')->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->change();
        });

        Schema::table('service_packages', function (Blueprint $table) {
            $table->unsignedBigInteger('price')->change();
            $table->unsignedInteger('max_people')->nullable()->change();
        });

        Schema::table('studio_locations', function (Blueprint $table) {
            $table->string('address', 200)->nullable()->change();
        });

        Schema::table('media_assets', function (Blueprint $table) {
            $table->string('path', 200)->change();
        });

        Schema::table('landing_hero_slides', function (Blueprint $table) {
            $table->string('eyebrow', 100)->nullable()->change();
            $table->string('title', 150)->change();
            $table->string('image_path', 200)->change();
        });
    }
};

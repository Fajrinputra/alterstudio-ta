<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyesuaikan ukuran kolom VARCHAR pada semua tabel inti
 * agar sesuai dengan spesifikasi struktur database pada Lampiran D
 * Laporan Tugas Akhir (revisi dosen pembimbing).
 *
 * Kolom yang berelasi (FK) diubah dengan cara: drop FK → resize → recreate FK.
 * Data yang ada tidak terhapus.
 */
return new class extends Migration
{
    // ─── Helper ───────────────────────────────────────────────────────────────

    protected function isMySQL(): bool
    {
        return DB::getDriverName() === 'mysql';
    }

    protected function foreignKeyExists(string $table, string $constraint): bool
    {
        if (! $this->isMySQL()) {
            return false;
        }

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $constraint)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    // ─── UP ───────────────────────────────────────────────────────────────────

    public function up(): void
    {
        // Migration ini hanya relevan untuk MySQL. Di SQLite (test environment)
        // ukuran varchar tidak di-enforce, sehingga cukup di-skip.
        if (! $this->isMySQL()) {
            return;
        }

        // ── 1. Drop FK yang mengikat kolom email users ─────────────────────────
        if ($this->foreignKeyExists('password_reset_tokens', 'password_reset_tokens_email_foreign')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->dropForeign('password_reset_tokens_email_foreign');
            });
        }

        // ── 2. users ───────────────────────────────────────────────────────────
        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 100)->change();
            $table->string('email', 100)->change();
            $table->string('no_hp', 20)->nullable()->change();
            $table->string('avatar_path', 200)->nullable()->change();
            // password & remember_token tetap; bcrypt hash + Laravel token panjangnya pas 255/100
        });

        // ── 3. password_reset_tokens (ikuti ukuran baru users.email) ──────────
        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 100)->change();
            $table->string('token', 100)->change();
        });

        // ── 4. Recreate FK setelah resize ─────────────────────────────────────
        if (! $this->foreignKeyExists('password_reset_tokens', 'password_reset_tokens_email_foreign')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->foreign('email', 'password_reset_tokens_email_foreign')
                    ->references('email')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }

        // ── 5. service_categories ─────────────────────────────────────────────
        Schema::table('service_categories', function (Blueprint $table) {
            $table->string('name', 100)->change();
        });

        // ── 6. service_packages ───────────────────────────────────────────────
        Schema::table('service_packages', function (Blueprint $table) {
            $table->string('name', 100)->change();
            $table->string('cover_image', 200)->nullable()->change();
        });

        // ── 7. studio_locations ───────────────────────────────────────────────
        Schema::table('studio_locations', function (Blueprint $table) {
            $table->string('name', 100)->change();
            $table->string('slug', 100)->change();
            $table->string('address', 200)->nullable()->change();
            $table->string('phone', 20)->nullable()->change();
            $table->string('email', 100)->nullable()->change();
            // map_url tetap 255 (URL Google Maps bisa cukup panjang)
        });

        // ── 8. studio_rooms ───────────────────────────────────────────────────
        Schema::table('studio_rooms', function (Blueprint $table) {
            $table->string('name', 100)->change();
            $table->string('photo_path', 200)->nullable()->change();
        });

        // ── 9. payments ───────────────────────────────────────────────────────
        Schema::table('payments', function (Blueprint $table) {
            $table->string('reference', 100)->nullable()->change();
            $table->string('order_id', 100)->nullable()->change();
            // snap_token tetap 255 (token dari Midtrans panjangnya bervariasi)
            $table->string('transaction_status', 50)->nullable()->change();
        });

        // ── 10. media_assets ──────────────────────────────────────────────────
        Schema::table('media_assets', function (Blueprint $table) {
            $table->string('path', 200)->change();
        });

        // ── 11. landing_hero_slides ───────────────────────────────────────────
        Schema::table('landing_hero_slides', function (Blueprint $table) {
            $table->string('eyebrow', 100)->nullable()->change();
            $table->string('title', 150)->change();
            $table->string('image_path', 200)->change();
        });
    }

    // ─── DOWN ─────────────────────────────────────────────────────────────────

    public function down(): void
    {
        // Hanya relevan di MySQL (lihat up())
        if (! $this->isMySQL()) {
            return;
        }

        // Drop FK sebelum rollback
        if ($this->foreignKeyExists('password_reset_tokens', 'password_reset_tokens_email_foreign')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->dropForeign('password_reset_tokens_email_foreign');
            });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('email', 255)->change();
            $table->string('no_hp', 255)->nullable()->change();
            $table->string('avatar_path', 255)->nullable()->change();
        });

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 255)->change();
            $table->string('token', 255)->change();
        });

        if (! $this->foreignKeyExists('password_reset_tokens', 'password_reset_tokens_email_foreign')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->foreign('email', 'password_reset_tokens_email_foreign')
                    ->references('email')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }

        Schema::table('service_categories', function (Blueprint $table) {
            $table->string('name', 255)->change();
        });

        Schema::table('service_packages', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('cover_image', 255)->nullable()->change();
        });

        Schema::table('studio_locations', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('slug', 255)->change();
            $table->string('address', 255)->nullable()->change();
            $table->string('phone', 255)->nullable()->change();
            $table->string('email', 255)->nullable()->change();
        });

        Schema::table('studio_rooms', function (Blueprint $table) {
            $table->string('name', 255)->change();
            $table->string('photo_path', 255)->nullable()->change();
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('reference', 255)->nullable()->change();
            $table->string('order_id', 255)->nullable()->change();
            $table->string('transaction_status', 255)->nullable()->change();
        });

        Schema::table('media_assets', function (Blueprint $table) {
            $table->string('path', 255)->change();
        });

        Schema::table('landing_hero_slides', function (Blueprint $table) {
            $table->string('eyebrow', 255)->nullable()->change();
            $table->string('title', 255)->change();
            $table->string('image_path', 255)->change();
        });
    }
};

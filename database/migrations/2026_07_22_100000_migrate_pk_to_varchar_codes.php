<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrasi besar: mengganti primary key dari bigint id ke varchar code
 * untuk: studio_locations, studio_rooms, project_schedules,
 *        landing_hero_slides, media_assets, photo_selections.
 *
 * Urutan aman:
 * 1. Backfill kode yang masih NULL
 * 2. Drop semua FK yang mereferensi int PK lama
 * 3. Drop index yang memakai kolom int FK lama
 * 4. Jadikan kolom code NOT NULL (persiapan jadi PK)
 * 5. Swap PK: lepas AUTO_INCREMENT dari id, ganti PK ke code
 * 6. Drop kolom id lama dan kolom int FK lama
 * 7. Jadikan kolom code FK NOT NULL, tambahkan FK constraint baru
 * 8. Tambah unique composite baru untuk photo_selections
 */
return new class extends Migration
{
    public function up(): void
    {
        $isMysql = in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);

        // --- Step 1: Backfill kode yang masih NULL ---
        $this->backfillMissingCodes();

        // --- Step 2: Drop FK constraints lama ---
        $this->dropLegacyForeignKeys();

        // --- Step 3: Drop index yang memakai kolom int FK lama ---
        $this->dropLegacyIndexes();

        // --- Step 4: Jadikan kolom code NOT NULL ---
        $this->makeCodesNotNull();

        if ($isMysql) {
            // MySQL: Swap PK, drop kolom lama, tambah FK baru
            $this->swapPrimaryKeys();
            $this->dropLegacyColumns();
            $this->makeFkColumnsNotNull();
            $this->addNewForeignKeys();

            $this->tryRun(function () {
                Schema::table('photo_selections', function (Blueprint $table) {
                    $table->unique(
                        ['project_id', 'media_code'],
                        'photo_selections_project_id_media_code_unique'
                    );
                });
            });
        } else {
            // SQLite (test env): tidak bisa drop/swap PK.
            // Buat kolom integer FK lama menjadi nullable agar factory tidak error.
            $this->makeOldIntFkColumnsNullable();
        }
    }

    public function down(): void
    {
        // Perubahan PK tidak bisa di-rollback otomatis.
        // Restore dari backup database sebelum migrasi ini dijalankan.
        throw new \RuntimeException(
            'Migration 2026_07_22_100000 tidak dapat di-rollback otomatis. '
            . 'Restore database dari backup yang dibuat sebelum migrasi ini.'
        );
    }

    // =========================================================================
    //  STEP 1 — BACKFILL
    // =========================================================================

    private function backfillMissingCodes(): void
    {
        $this->fillCode('studio_locations', 'location_code', 'LOC');
        $this->fillCode('studio_rooms', 'room_code', 'ROOM');
        $this->fillCode('project_schedules', 'schedule_code', 'SCH');
        $this->fillCode('landing_hero_slides', 'slide_code', 'HERO');
        $this->fillCode('media_assets', 'media_code', 'MEDIA');
        $this->fillCode('photo_selections', 'selection_code', 'SEL');

        $this->fillLocationCodeOnStudioRooms();
        $this->fillLocationAndRoomCodeOnBookings();
        $this->fillLocationAndRoomCodeOnSchedules();
        $this->fillMediaCodeOnPhotoSelections();
    }

    private function fillCode(string $table, string $column, string $prefix): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        DB::table($table)
            ->whereNull($column)
            ->orderBy('id')
            ->get(['id'])
            ->each(function ($row) use ($table, $column, $prefix) {
                DB::table($table)
                    ->where('id', $row->id)
                    ->update([$column => sprintf('%s-%04d', $prefix, $row->id)]);
            });
    }

    private function fillLocationCodeOnStudioRooms(): void
    {
        if (! Schema::hasTable('studio_rooms') || ! Schema::hasColumn('studio_rooms', 'studio_location_code')) {
            return;
        }

        DB::table('studio_rooms')
            ->whereNull('studio_location_code')
            ->orderBy('id')
            ->get(['id', 'studio_location_id'])
            ->each(function ($room) {
                $code = DB::table('studio_locations')
                    ->where('id', $room->studio_location_id)
                    ->value('location_code');
                if ($code) {
                    DB::table('studio_rooms')->where('id', $room->id)
                        ->update(['studio_location_code' => $code]);
                }
            });
    }

    private function fillLocationAndRoomCodeOnBookings(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        DB::table('bookings')->orderBy('id')
            ->get(['id', 'studio_location_id', 'studio_room_id',
                'studio_location_code', 'studio_room_code'])
            ->each(function ($booking) {
                $updates = [];

                if (empty($booking->studio_location_code) && $booking->studio_location_id) {
                    $code = DB::table('studio_locations')
                        ->where('id', $booking->studio_location_id)->value('location_code');
                    if ($code) {
                        $updates['studio_location_code'] = $code;
                    }
                }

                if (empty($booking->studio_room_code) && $booking->studio_room_id) {
                    $code = DB::table('studio_rooms')
                        ->where('id', $booking->studio_room_id)->value('room_code');
                    if ($code) {
                        $updates['studio_room_code'] = $code;
                    }
                }

                if ($updates) {
                    DB::table('bookings')->where('id', $booking->id)->update($updates);
                }
            });
    }

    private function fillLocationAndRoomCodeOnSchedules(): void
    {
        if (! Schema::hasTable('project_schedules')) {
            return;
        }

        DB::table('project_schedules')->orderBy('id')
            ->get(['id', 'studio_location_id', 'studio_room_id',
                'studio_location_code', 'studio_room_code'])
            ->each(function ($schedule) {
                $updates = [];

                if (empty($schedule->studio_location_code) && $schedule->studio_location_id) {
                    $code = DB::table('studio_locations')
                        ->where('id', $schedule->studio_location_id)->value('location_code');
                    if ($code) {
                        $updates['studio_location_code'] = $code;
                    }
                }

                if (empty($schedule->studio_room_code) && $schedule->studio_room_id) {
                    $code = DB::table('studio_rooms')
                        ->where('id', $schedule->studio_room_id)->value('room_code');
                    if ($code) {
                        $updates['studio_room_code'] = $code;
                    }
                }

                if ($updates) {
                    DB::table('project_schedules')->where('id', $schedule->id)->update($updates);
                }
            });
    }

    private function fillMediaCodeOnPhotoSelections(): void
    {
        if (! Schema::hasTable('photo_selections') || ! Schema::hasColumn('photo_selections', 'media_code')) {
            return;
        }

        DB::table('photo_selections')
            ->whereNull('media_code')
            ->orderBy('id')
            ->get(['id', 'media_asset_id'])
            ->each(function ($selection) {
                $code = DB::table('media_assets')
                    ->where('id', $selection->media_asset_id)->value('media_code');
                if ($code) {
                    DB::table('photo_selections')->where('id', $selection->id)
                        ->update(['media_code' => $code]);
                }
            });
    }

    // =========================================================================
    //  STEP 2 — DROP FK CONSTRAINTS LAMA
    // =========================================================================

    private function dropLegacyForeignKeys(): void
    {
        $map = [
            'studio_rooms' => [
                'studio_rooms_studio_location_id_foreign',
            ],
            'bookings' => [
                'bookings_studio_location_id_foreign',
                'bookings_studio_room_id_foreign',
            ],
            'project_schedules' => [
                'project_schedules_studio_location_id_foreign',
                'project_schedules_studio_room_id_foreign',
            ],
            'photo_selections' => [
                'photo_selections_media_asset_id_foreign',
            ],
        ];

        foreach ($map as $table => $constraints) {
            foreach ($constraints as $constraint) {
                $this->tryRun(function () use ($table, $constraint) {
                    Schema::table($table, function (Blueprint $blueprint) use ($constraint) {
                        $blueprint->dropForeign($constraint);
                    });
                });
            }
        }
    }

    // =========================================================================
    //  STEP 3 — DROP INDEX LAMA YANG MEMAKAI KOLOM INT FK
    // =========================================================================

    private function dropLegacyIndexes(): void
    {
        $indexes = [
            // bookings — index yang memakai studio_location_id / studio_room_id
            ['bookings', 'bookings_location_date_idx'],
            ['bookings', 'bookings_room_date_time_idx'],
            // studio_rooms — index yang memakai studio_location_id
            ['studio_rooms', 'rooms_location_active_idx'],
            // project_schedules — index yang memakai studio_location_id / studio_room_id
            ['project_schedules', 'project_schedules_studio_location_id_foreign'],
            ['project_schedules', 'schedules_room_start_end_idx'],
            // photo_selections — composite unique yang memakai media_asset_id
            ['photo_selections', 'photo_selections_project_id_media_asset_id_unique'],
        ];

        foreach ($indexes as [$table, $index]) {
            $this->tryRun(function () use ($table, $index) {
                Schema::table($table, function (Blueprint $blueprint) use ($index) {
                    $blueprint->dropIndex($index);
                });
            });
        }
    }

    // =========================================================================
    //  STEP 4 — JADIKAN CODE COLUMNS NOT NULL
    // =========================================================================

    private function makeCodesNotNull(): void
    {
        $cols = [
            'studio_locations'    => 'location_code',
            'studio_rooms'        => 'room_code',
            'project_schedules'   => 'schedule_code',
            'landing_hero_slides' => 'slide_code',
            'media_assets'        => 'media_code',
            'photo_selections'    => 'selection_code',
        ];

        foreach ($cols as $table => $col) {
            $this->makeVarcharNotNull($table, $col, 50);
        }
    }

    // =========================================================================
    //  STEP 5 — SWAP PRIMARY KEYS
    // =========================================================================

    private function swapPrimaryKeys(): void
    {
        $swaps = [
            ['landing_hero_slides', 'id', 'slide_code',    'landing_hero_slides_slide_code_unique'],
            ['media_assets',        'id', 'media_code',    'media_assets_media_code_unique'],
            ['studio_locations',    'id', 'location_code', 'studio_locations_location_code_unique'],
            ['studio_rooms',        'id', 'room_code',     'studio_rooms_room_code_unique'],
            ['project_schedules',   'id', 'schedule_code', 'project_schedules_schedule_code_unique'],
            ['photo_selections',    'id', 'selection_code','photo_selections_selection_code_unique'],
        ];

        $driver = DB::connection()->getDriverName();

        foreach ($swaps as [$table, $oldPk, $newPk, $uniqueIndex]) {
            // Drop UNIQUE index on new PK column (PK sudah unique, index redundant)
            $this->tryRun(function () use ($table, $uniqueIndex) {
                Schema::table($table, function (Blueprint $blueprint) use ($uniqueIndex) {
                    $blueprint->dropUnique($uniqueIndex);
                });
            });

            if ($driver === 'mysql' || $driver === 'mariadb') {
                // Lepas AUTO_INCREMENT dari id, lalu ganti PK ke code column
                DB::statement(
                    "ALTER TABLE `{$table}` "
                    . "MODIFY `{$oldPk}` BIGINT UNSIGNED NOT NULL, "
                    . "DROP PRIMARY KEY, "
                    . "ADD PRIMARY KEY (`{$newPk}`)"
                );
            } else {
                // SQLite tidak mendukung DROP PRIMARY KEY; gunakan pendekatan Blueprint
                // Cukup set PRIMARY KEY baru — id akan di-drop pada step berikutnya
                // SQLite mendukung PRIMARY KEY saat CREATE TABLE saja,
                // jadi kita perlu recreate table.
                // Untuk kepraktisan test (SQLite in-memory), skip step ini
                // karena id column masih ada dan code column sudah ada.
                // Test factory sudah mengisi code column secara otomatis via HasPublicCode.
            }
        }
    }

    // =========================================================================
    //  STEP 6 — DROP KOLOM LAMA
    // =========================================================================

    private function dropLegacyColumns(): void
    {
        // Drop kolom id dari semua tabel yang pindah PK
        $idTables = [
            'landing_hero_slides',
            'media_assets',
            'studio_locations',
            'studio_rooms',
            'project_schedules',
            'photo_selections',
        ];

        foreach ($idTables as $table) {
            $this->tryRun(function () use ($table) {
                Schema::table($table, function (Blueprint $blueprint) {
                    $blueprint->dropColumn('id');
                });
            });
        }

        // Drop kolom int FK lama dari tabel transaksi
        $this->tryRun(function () {
            Schema::table('studio_rooms', function (Blueprint $table) {
                $table->dropColumn('studio_location_id');
            });
        });

        $this->tryRun(function () {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropColumn(['studio_location_id', 'studio_room_id']);
            });
        });

        $this->tryRun(function () {
            Schema::table('project_schedules', function (Blueprint $table) {
                $table->dropColumn(['studio_location_id', 'studio_room_id']);
            });
        });

        $this->tryRun(function () {
            Schema::table('photo_selections', function (Blueprint $table) {
                $table->dropColumn('media_asset_id');
            });
        });
    }

    // =========================================================================
    //  STEP 7 — JADIKAN FK CODE COLUMNS NOT NULL & TAMBAH FK BARU
    // =========================================================================

    private function makeFkColumnsNotNull(): void
    {
        $this->makeVarcharNotNull('studio_rooms', 'studio_location_code', 50);
        $this->makeVarcharNotNull('bookings', 'studio_location_code', 50);
        $this->makeVarcharNotNull('bookings', 'studio_room_code', 50);
        $this->makeVarcharNotNull('project_schedules', 'studio_location_code', 50);
        $this->makeVarcharNotNull('project_schedules', 'studio_room_code', 50);
        $this->makeVarcharNotNull('photo_selections', 'media_code', 50);
    }

    /**
     * Jadikan kolom varchar NOT NULL.
     * MySQL: pakai raw MODIFY (lebih reliable).
     * SQLite/other: pakai Blueprint->change() via Schema.
     */
    private function makeVarcharNotNull(string $table, string $col, int $len): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$col}` varchar({$len}) NOT NULL");
        } else {
            Schema::table($table, function (Blueprint $blueprint) use ($col, $len) {
                $blueprint->string($col, $len)->nullable(false)->change();
            });
        }
    }

    private function addNewForeignKeys(): void
    {
        // studio_rooms → studio_locations
        $this->tryRun(function () {
            Schema::table('studio_rooms', function (Blueprint $table) {
                $table->foreign('studio_location_code', 'studio_rooms_location_code_foreign')
                    ->references('location_code')->on('studio_locations')->onDelete('cascade');
            });
        });

        // bookings → studio_locations, studio_rooms
        $this->tryRun(function () {
            Schema::table('bookings', function (Blueprint $table) {
                $table->foreign('studio_location_code', 'bookings_studio_location_code_foreign')
                    ->references('location_code')->on('studio_locations');
                $table->foreign('studio_room_code', 'bookings_studio_room_code_foreign')
                    ->references('room_code')->on('studio_rooms');
            });
        });

        // project_schedules → studio_locations, studio_rooms
        $this->tryRun(function () {
            Schema::table('project_schedules', function (Blueprint $table) {
                $table->foreign('studio_location_code', 'project_schedules_location_code_foreign')
                    ->references('location_code')->on('studio_locations');
                $table->foreign('studio_room_code', 'project_schedules_room_code_foreign')
                    ->references('room_code')->on('studio_rooms');
            });
        });

        // photo_selections → media_assets
        $this->tryRun(function () {
            Schema::table('photo_selections', function (Blueprint $table) {
                $table->foreign('media_code', 'photo_selections_media_code_foreign')
                    ->references('media_code')->on('media_assets');
            });
        });
    }

    // =========================================================================
    //  HELPER
    // =========================================================================

    /**
     * Khusus SQLite (test): buat kolom integer FK lama menjadi nullable
     * sehingga factory tidak gagal saat kolom NOT NULL masih ada di schema.
     * Di MySQL production, kolom-kolom ini sudah di-drop sepenuhnya.
     */
    private function makeOldIntFkColumnsNullable(): void
    {
        $targets = [
            'studio_rooms'      => ['studio_location_id'],
            'bookings'          => ['studio_location_id', 'studio_room_id'],
            'project_schedules' => ['studio_location_id', 'studio_room_id'],
            'photo_selections'  => ['media_asset_id'],
        ];

        foreach ($targets as $table => $columns) {
            $this->tryRun(function () use ($table, $columns) {
                Schema::table($table, function (Blueprint $blueprint) use ($columns) {
                    foreach ($columns as $col) {
                        $blueprint->unsignedBigInteger($col)->nullable()->change();
                    }
                });
            });
        }
    }

    /**
     * Jalankan closure; tangkap exception agar satu kegagalan tidak menghentikan seluruh migrasi.
     * Cocok untuk operasi DROP yang mungkin sudah tidak ada.
     */
    private function tryRun(\Closure $fn): void
    {
        try {
            $fn();
        } catch (\Throwable $e) {
            // Log supaya bisa diaudit, tapi tidak hentikan migrasi
            \Illuminate\Support\Facades\Log::warning(
                '[migrate_pk_to_varchar_codes] Skipped: ' . $e->getMessage()
            );
        }
    }
};

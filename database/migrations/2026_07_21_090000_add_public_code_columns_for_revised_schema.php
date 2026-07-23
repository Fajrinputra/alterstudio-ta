<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addColumn('studio_locations', 'location_code', fn (Blueprint $table) => $table->string('location_code', 50)->nullable()->unique()->after('id'));
        $this->addColumn('studio_rooms', 'room_code', fn (Blueprint $table) => $table->string('room_code', 50)->nullable()->unique()->after('id'));
        $this->addColumn('studio_rooms', 'studio_location_code', fn (Blueprint $table) => $table->string('studio_location_code', 50)->nullable()->index()->after('studio_location_id'));
        $this->addColumn('bookings', 'studio_location_code', fn (Blueprint $table) => $table->string('studio_location_code', 50)->nullable()->index()->after('studio_location_id'));
        $this->addColumn('bookings', 'studio_room_code', fn (Blueprint $table) => $table->string('studio_room_code', 50)->nullable()->index()->after('studio_room_id'));
        $this->addColumn('project_schedules', 'schedule_code', fn (Blueprint $table) => $table->string('schedule_code', 50)->nullable()->unique()->after('id'));
        $this->addColumn('project_schedules', 'studio_location_code', fn (Blueprint $table) => $table->string('studio_location_code', 50)->nullable()->index()->after('studio_location_id'));
        $this->addColumn('project_schedules', 'studio_room_code', fn (Blueprint $table) => $table->string('studio_room_code', 50)->nullable()->index()->after('studio_room_id'));
        $this->addColumn('landing_hero_slides', 'slide_code', fn (Blueprint $table) => $table->string('slide_code', 50)->nullable()->unique()->after('id'));
        $this->addColumn('media_assets', 'media_code', fn (Blueprint $table) => $table->string('media_code', 50)->nullable()->unique()->after('id'));
        $this->addColumn('photo_selections', 'selection_code', fn (Blueprint $table) => $table->string('selection_code', 50)->nullable()->unique()->after('id'));
        $this->addColumn('photo_selections', 'media_code', fn (Blueprint $table) => $table->string('media_code', 50)->nullable()->index()->after('media_asset_id'));

        $this->backfillCodes();
    }

    public function down(): void
    {
        $this->dropColumnIfExists('photo_selections', 'media_code');
        $this->dropColumnIfExists('photo_selections', 'selection_code');
        $this->dropColumnIfExists('media_assets', 'media_code');
        $this->dropColumnIfExists('landing_hero_slides', 'slide_code');
        $this->dropColumnIfExists('project_schedules', 'studio_room_code');
        $this->dropColumnIfExists('project_schedules', 'studio_location_code');
        $this->dropColumnIfExists('project_schedules', 'schedule_code');
        $this->dropColumnIfExists('bookings', 'studio_room_code');
        $this->dropColumnIfExists('bookings', 'studio_location_code');
        $this->dropColumnIfExists('studio_rooms', 'studio_location_code');
        $this->dropColumnIfExists('studio_rooms', 'room_code');
        $this->dropColumnIfExists('studio_locations', 'location_code');
    }

    private function addColumn(string $table, string $column, Closure $definition): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($definition) {
            $definition($blueprint);
        });
    }

    private function dropColumnIfExists(string $table, string $column): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column) {
            $blueprint->dropColumn($column);
        });
    }

    private function backfillCodes(): void
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
                $locationCode = DB::table('studio_locations')
                    ->where('id', $room->studio_location_id)
                    ->value('location_code');

                if ($locationCode) {
                    DB::table('studio_rooms')
                        ->where('id', $room->id)
                        ->update(['studio_location_code' => $locationCode]);
                }
            });
    }

    private function fillLocationAndRoomCodeOnBookings(): void
    {
        if (! Schema::hasTable('bookings')) {
            return;
        }

        DB::table('bookings')
            ->orderBy('id')
            ->get(['id', 'studio_location_id', 'studio_room_id'])
            ->each(function ($booking) {
                $updates = [];

                if (Schema::hasColumn('bookings', 'studio_location_code')) {
                    $locationCode = DB::table('studio_locations')
                        ->where('id', $booking->studio_location_id)
                        ->value('location_code');

                    if ($locationCode) {
                        $updates['studio_location_code'] = $locationCode;
                    }
                }

                if (Schema::hasColumn('bookings', 'studio_room_code')) {
                    $roomCode = DB::table('studio_rooms')
                        ->where('id', $booking->studio_room_id)
                        ->value('room_code');

                    if ($roomCode) {
                        $updates['studio_room_code'] = $roomCode;
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

        DB::table('project_schedules')
            ->orderBy('id')
            ->get(['id', 'studio_location_id', 'studio_room_id'])
            ->each(function ($schedule) {
                $updates = [];

                if (Schema::hasColumn('project_schedules', 'studio_location_code')) {
                    $locationCode = DB::table('studio_locations')
                        ->where('id', $schedule->studio_location_id)
                        ->value('location_code');

                    if ($locationCode) {
                        $updates['studio_location_code'] = $locationCode;
                    }
                }

                if (Schema::hasColumn('project_schedules', 'studio_room_code')) {
                    $roomCode = DB::table('studio_rooms')
                        ->where('id', $schedule->studio_room_id)
                        ->value('room_code');

                    if ($roomCode) {
                        $updates['studio_room_code'] = $roomCode;
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
                $mediaCode = DB::table('media_assets')
                    ->where('id', $selection->media_asset_id)
                    ->value('media_code');

                if ($mediaCode) {
                    DB::table('photo_selections')
                        ->where('id', $selection->id)
                        ->update(['media_code' => $mediaCode]);
                }
            });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add room-level booking support so each booking can target a specific studio room.
     */
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'studio_room_id')) {
                $table->foreignId('studio_room_id')
                    ->nullable()
                    ->after('studio_location_id')
                    ->constrained('studio_rooms')
                    ->nullOnDelete();
            }
        });
    }

    /**
     * Remove room reference from bookings.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'studio_room_id')) {
                $table->dropConstrainedForeignId('studio_room_id');
            }
        });
    }
};


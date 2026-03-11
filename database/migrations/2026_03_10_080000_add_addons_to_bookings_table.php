<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menyimpan add-on terpilih dan total add-on pada booking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Avoid hasColumn() introspection to reduce information_schema reads on unstable MySQL/MariaDB.
            $table->longText('selected_addons')->nullable()->after('payment_type');
            $table->unsignedBigInteger('addon_total')->default(0)->after('selected_addons');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn('selected_addons');
            $table->dropColumn('addon_total');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studio_rooms', function (Blueprint $table) {
            if (!Schema::hasColumn('studio_rooms', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('description');
            }
        });
    }

    public function down(): void
    {
        Schema::table('studio_rooms', function (Blueprint $table) {
            if (Schema::hasColumn('studio_rooms', 'photo_path')) {
                $table->dropColumn('photo_path');
            }
        });
    }
};

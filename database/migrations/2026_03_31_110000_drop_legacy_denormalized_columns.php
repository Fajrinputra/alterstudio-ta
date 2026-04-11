<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('service_packages', 'features')
            || Schema::hasColumn('service_packages', 'addons')
            || Schema::hasColumn('service_packages', 'gallery')) {
            Schema::table('service_packages', function (Blueprint $table) {
                $columns = [];
                foreach (['features', 'addons', 'gallery'] as $column) {
                    if (Schema::hasColumn('service_packages', $column)) {
                        $columns[] = $column;
                    }
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasColumn('bookings', 'selected_addons') || Schema::hasColumn('bookings', 'location')) {
            Schema::table('bookings', function (Blueprint $table) {
                $columns = [];
                foreach (['selected_addons', 'location'] as $column) {
                    if (Schema::hasColumn('bookings', $column)) {
                        $columns[] = $column;
                    }
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }

        if (Schema::hasColumn('studio_locations', 'photo_path') || Schema::hasColumn('studio_locations', 'photo_gallery')) {
            Schema::table('studio_locations', function (Blueprint $table) {
                $columns = [];
                foreach (['photo_path', 'photo_gallery'] as $column) {
                    if (Schema::hasColumn('studio_locations', $column)) {
                        $columns[] = $column;
                    }
                }
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            if (!Schema::hasColumn('service_packages', 'features')) {
                $table->json('features')->nullable()->after('description');
            }
            if (!Schema::hasColumn('service_packages', 'addons')) {
                $table->json('addons')->nullable()->after('features');
            }
            if (!Schema::hasColumn('service_packages', 'gallery')) {
                $table->json('gallery')->nullable()->after('overview_image');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'selected_addons')) {
                $table->longText('selected_addons')->nullable()->after('payment_type');
            }
            if (!Schema::hasColumn('bookings', 'location')) {
                $table->string('location')->nullable()->after('booking_date');
            }
        });

        Schema::table('studio_locations', function (Blueprint $table) {
            if (!Schema::hasColumn('studio_locations', 'photo_path')) {
                $table->string('photo_path')->nullable()->after('map_url');
            }
            if (!Schema::hasColumn('studio_locations', 'photo_gallery')) {
                $table->json('photo_gallery')->nullable()->after('photo_path');
            }
        });
    }
};

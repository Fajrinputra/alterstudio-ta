<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addColumns();
        $this->backfillPackages();
        $this->backfillLocations();
        $this->backfillBookings();
        $this->backfillProjects();
        $this->dropMergedTables();
    }

    public function down(): void
    {
        $this->recreateMergedTables();
    }

    protected function addColumns(): void
    {
        Schema::table('service_packages', function (Blueprint $table) {
            if (! Schema::hasColumn('service_packages', 'features')) {
                $table->longText('features')->nullable()->after('terms');
            }
            if (! Schema::hasColumn('service_packages', 'addons')) {
                $table->longText('addons')->nullable()->after('features');
            }
            if (! Schema::hasColumn('service_packages', 'gallery')) {
                $table->longText('gallery')->nullable()->after('addons');
            }
        });

        Schema::table('studio_locations', function (Blueprint $table) {
            if (! Schema::hasColumn('studio_locations', 'photo_gallery')) {
                $table->longText('photo_gallery')->nullable()->after('facilities');
            }
        });

        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'selected_addons')) {
                $table->longText('selected_addons')->nullable()->after('payment_type');
            }
        });

        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'photographer_id')) {
                $table->foreignId('photographer_id')->nullable()->after('selections_locked')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('projects', 'editor_id')) {
                $table->foreignId('editor_id')->nullable()->after('photographer_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('projects', 'start_at')) {
                $table->dateTime('start_at')->nullable()->after('editor_id');
            }
            if (! Schema::hasColumn('projects', 'end_at')) {
                $table->dateTime('end_at')->nullable()->after('start_at');
            }
        });
    }

    protected function backfillPackages(): void
    {
        if (! Schema::hasTable('service_package_features')
            && ! Schema::hasTable('service_package_addons')
            && ! Schema::hasTable('service_package_galleries')) {
            return;
        }

        $packageIds = DB::table('service_packages')->pluck('id');

        foreach ($packageIds as $packageId) {
            $features = Schema::hasTable('service_package_features')
                ? DB::table('service_package_features')
                    ->where('service_package_id', $packageId)
                    ->orderBy('sort_order')
                    ->pluck('feature_text')
                    ->filter()
                    ->values()
                    ->all()
                : [];

            $addons = Schema::hasTable('service_package_addons')
                ? DB::table('service_package_addons')
                    ->where('service_package_id', $packageId)
                    ->orderBy('sort_order')
                    ->get(['label', 'price', 'is_active'])
                    ->map(fn ($row) => [
                        'label' => $row->label,
                        'price' => (int) $row->price,
                        'is_active' => (bool) $row->is_active,
                    ])
                    ->values()
                    ->all()
                : [];

            $gallery = Schema::hasTable('service_package_galleries')
                ? DB::table('service_package_galleries')
                    ->where('service_package_id', $packageId)
                    ->orderBy('sort_order')
                    ->pluck('path')
                    ->filter()
                    ->values()
                    ->all()
                : [];

            DB::table('service_packages')
                ->where('id', $packageId)
                ->update([
                    'features' => json_encode($features),
                    'addons' => json_encode($addons),
                    'gallery' => json_encode($gallery),
                ]);
        }
    }

    protected function backfillLocations(): void
    {
        if (! Schema::hasTable('studio_location_photos')) {
            return;
        }

        $locationIds = DB::table('studio_locations')->pluck('id');

        foreach ($locationIds as $locationId) {
            $gallery = DB::table('studio_location_photos')
                ->where('studio_location_id', $locationId)
                ->orderBy('sort_order')
                ->pluck('path')
                ->filter()
                ->values()
                ->all();

            DB::table('studio_locations')
                ->where('id', $locationId)
                ->update([
                    'photo_gallery' => json_encode($gallery),
                ]);
        }
    }

    protected function backfillBookings(): void
    {
        if (! Schema::hasTable('booking_addons')) {
            return;
        }

        $bookingIds = DB::table('bookings')->pluck('id');

        foreach ($bookingIds as $bookingId) {
            $selectedAddons = DB::table('booking_addons')
                ->where('booking_id', $bookingId)
                ->orderBy('sort_order')
                ->get(['label_snapshot', 'price_snapshot'])
                ->map(fn ($row) => [
                    'label' => $row->label_snapshot,
                    'price' => (int) $row->price_snapshot,
                ])
                ->values()
                ->all();

            DB::table('bookings')
                ->where('id', $bookingId)
                ->update([
                    'selected_addons' => json_encode($selectedAddons),
                ]);
        }
    }

    protected function backfillProjects(): void
    {
        if (! Schema::hasTable('schedules')) {
            return;
        }

        $schedules = DB::table('schedules')->get();

        foreach ($schedules as $schedule) {
            DB::table('projects')
                ->where('id', $schedule->project_id)
                ->update([
                    'photographer_id' => $schedule->photographer_id,
                    'editor_id' => $schedule->editor_id,
                    'start_at' => $schedule->start_at,
                    'end_at' => $schedule->end_at,
                    'status' => DB::raw("CASE WHEN status = 'DRAFT' THEN 'SCHEDULED' ELSE status END"),
                ]);
        }
    }

    protected function dropMergedTables(): void
    {
        Schema::dropIfExists('booking_addons');
        Schema::dropIfExists('service_package_features');
        Schema::dropIfExists('service_package_addons');
        Schema::dropIfExists('service_package_galleries');
        Schema::dropIfExists('studio_location_photos');
        Schema::dropIfExists('schedules');
    }

    protected function recreateMergedTables(): void
    {
        if (! Schema::hasTable('service_package_features')) {
            Schema::create('service_package_features', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_package_id')->constrained('service_packages')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('feature_text');
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('service_package_addons')) {
            Schema::create('service_package_addons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_package_id')->constrained('service_packages')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('label');
                $table->unsignedBigInteger('price')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('service_package_galleries')) {
            Schema::create('service_package_galleries', function (Blueprint $table) {
                $table->id();
                $table->foreignId('service_package_id')->constrained('service_packages')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('path');
                $table->boolean('is_cover')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('studio_location_photos')) {
            Schema::create('studio_location_photos', function (Blueprint $table) {
                $table->id();
                $table->foreignId('studio_location_id')->constrained('studio_locations')->cascadeOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('path');
                $table->boolean('is_cover')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('booking_addons')) {
            Schema::create('booking_addons', function (Blueprint $table) {
                $table->id();
                $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
                $table->foreignId('service_package_addon_id')->nullable()->constrained('service_package_addons')->nullOnDelete();
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('label_snapshot');
                $table->unsignedBigInteger('price_snapshot')->default(0);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('schedules')) {
            Schema::create('schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('photographer_id')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('editor_id')->nullable()->constrained('users')->nullOnDelete();
                $table->dateTime('start_at');
                $table->dateTime('end_at');
                $table->timestamps();
                $table->unique('project_id');
            });
        }
    }
};

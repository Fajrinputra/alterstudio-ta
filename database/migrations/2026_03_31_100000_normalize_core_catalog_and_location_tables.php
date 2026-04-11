<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_package_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained('service_packages')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('feature_text');
            $table->timestamps();
        });

        Schema::create('service_package_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained('service_packages')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label');
            $table->unsignedBigInteger('price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('service_package_galleries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_package_id')->constrained('service_packages')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('path');
            $table->boolean('is_cover')->default(false);
            $table->timestamps();
        });

        Schema::create('booking_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('service_package_addon_id')->nullable()->constrained('service_package_addons')->nullOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('label_snapshot');
            $table->unsignedBigInteger('price_snapshot')->default(0);
            $table->timestamps();
        });

        Schema::create('studio_location_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('studio_location_id')->constrained('studio_locations')->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('path');
            $table->boolean('is_cover')->default(false);
            $table->timestamps();
        });

        $packages = DB::table('service_packages')->select('id', 'features', 'addons', 'gallery', 'overview_image')->get();
        foreach ($packages as $package) {
            $features = json_decode($package->features ?? '[]', true);
            if (is_array($features)) {
                foreach (array_values(array_filter($features, fn ($item) => is_string($item) && trim($item) !== '')) as $index => $feature) {
                    DB::table('service_package_features')->insert([
                        'service_package_id' => $package->id,
                        'sort_order' => $index,
                        'feature_text' => trim($feature),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $addons = json_decode($package->addons ?? '[]', true);
            if (is_array($addons)) {
                foreach (array_values($addons) as $index => $addon) {
                    if (is_array($addon)) {
                        $label = trim((string) ($addon['label'] ?? ''));
                        $price = (int) ($addon['price'] ?? 0);
                    } else {
                        $label = trim((string) $addon);
                        $price = 0;
                    }
                    if ($label === '') {
                        continue;
                    }
                    DB::table('service_package_addons')->insert([
                        'service_package_id' => $package->id,
                        'sort_order' => $index,
                        'label' => $label,
                        'price' => max(0, $price),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $gallery = json_decode($package->gallery ?? '[]', true);
            $galleryRows = [];
            if (is_array($gallery)) {
                foreach (array_values(array_filter($gallery, fn ($item) => is_string($item) && trim($item) !== '')) as $index => $path) {
                    $galleryRows[] = [
                        'service_package_id' => $package->id,
                        'sort_order' => $index,
                        'path' => $path,
                        'is_cover' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
            if (!empty($package->overview_image) && !collect($galleryRows)->contains(fn ($row) => $row['path'] === $package->overview_image)) {
                array_unshift($galleryRows, [
                    'service_package_id' => $package->id,
                    'sort_order' => 0,
                    'path' => $package->overview_image,
                    'is_cover' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                foreach ($galleryRows as $idx => &$row) {
                    $row['sort_order'] = $idx;
                }
                unset($row);
            } elseif (!empty($galleryRows)) {
                $galleryRows[0]['is_cover'] = true;
            }
            if (!empty($galleryRows)) {
                DB::table('service_package_galleries')->insert($galleryRows);
            }
        }

        $bookings = DB::table('bookings')->select('id', 'package_id', 'selected_addons')->get();
        foreach ($bookings as $booking) {
            $selected = json_decode($booking->selected_addons ?? '[]', true);
            if (!is_array($selected)) {
                continue;
            }

            $packageAddonMap = DB::table('service_package_addons')
                ->where('service_package_id', $booking->package_id)
                ->get()
                ->keyBy(fn ($row) => md5(trim((string) $row->label) . '|' . (int) $row->price));

            foreach (array_values($selected) as $index => $addon) {
                if (!is_array($addon)) {
                    continue;
                }
                $label = trim((string) ($addon['label'] ?? ''));
                if ($label === '') {
                    continue;
                }
                $price = max(0, (int) ($addon['price'] ?? 0));
                $key = md5($label . '|' . $price);
                $matchedAddon = $packageAddonMap[$key] ?? null;

                DB::table('booking_addons')->insert([
                    'booking_id' => $booking->id,
                    'service_package_addon_id' => $matchedAddon->id ?? null,
                    'sort_order' => $index,
                    'label_snapshot' => $label,
                    'price_snapshot' => $price,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $locations = DB::table('studio_locations')->select('id', 'photo_path', 'photo_gallery')->get();
        foreach ($locations as $location) {
            $gallery = json_decode($location->photo_gallery ?? '[]', true);
            $paths = [];
            if (is_array($gallery)) {
                $paths = array_values(array_filter($gallery, fn ($item) => is_string($item) && trim($item) !== ''));
            }
            if (!empty($location->photo_path) && !in_array($location->photo_path, $paths, true)) {
                array_unshift($paths, $location->photo_path);
            }
            foreach ($paths as $index => $path) {
                DB::table('studio_location_photos')->insert([
                    'studio_location_id' => $location->id,
                    'sort_order' => $index,
                    'path' => $path,
                    'is_cover' => $index === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('studio_location_photos');
        Schema::dropIfExists('booking_addons');
        Schema::dropIfExists('service_package_galleries');
        Schema::dropIfExists('service_package_addons');
        Schema::dropIfExists('service_package_features');
    }
};

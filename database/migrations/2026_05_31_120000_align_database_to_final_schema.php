<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->alignLandingHeroSlides();
        $this->alignProjectSchedules();
        $this->dropProjectCrewColumns();
        $this->makeBookingStudioReferencesRestrictive();
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            if (! Schema::hasColumn('projects', 'photographer_id')) {
                $table->foreignId('photographer_id')->nullable()->after('selections_locked')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('projects', 'editor_id')) {
                $table->foreignId('editor_id')->nullable()->after('photographer_id')->constrained('users')->nullOnDelete();
            }
        });

        Schema::table('project_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('project_schedules', 'user_id')) {
                $this->dropForeignIfExists('project_schedules', 'project_schedules_user_id_foreign');
                $table->renameColumn('user_id', 'scheduled_by');
            }
        });

        Schema::table('project_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('project_schedules', 'scheduled_by')) {
                if (! $this->foreignKeyExists('project_schedules', 'project_schedules_scheduled_by_foreign')) {
                    $table->foreign('scheduled_by')->references('id')->on('users')->nullOnDelete();
                }
            }
        });

        Schema::table('landing_hero_slides', function (Blueprint $table) {
            if (Schema::hasColumn('landing_hero_slides', 'user_id')) {
                $this->dropForeignIfExists('landing_hero_slides', 'landing_hero_slides_user_id_foreign');
                $table->dropColumn('user_id');
            }
        });
    }

    protected function alignLandingHeroSlides(): void
    {
        Schema::table('landing_hero_slides', function (Blueprint $table) {
            if (! Schema::hasColumn('landing_hero_slides', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id');
            }
        });

        if (Schema::hasColumn('landing_hero_slides', 'created_by')) {
            DB::table('landing_hero_slides')
                ->whereNull('user_id')
                ->update(['user_id' => DB::raw('COALESCE(updated_by, created_by)')]);
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('landing_hero_slides', function (Blueprint $table) {
            if (Schema::hasColumn('landing_hero_slides', 'created_by')) {
                $this->dropForeignIfExists('landing_hero_slides', 'landing_hero_slides_created_by_foreign');
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('landing_hero_slides', 'updated_by')) {
                $this->dropForeignIfExists('landing_hero_slides', 'landing_hero_slides_updated_by_foreign');
                $table->dropColumn('updated_by');
            }
        });

        Schema::table('landing_hero_slides', function (Blueprint $table) {
            if (! $this->foreignKeyExists('landing_hero_slides', 'landing_hero_slides_user_id_foreign')) {
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    protected function alignProjectSchedules(): void
    {
        Schema::table('project_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('project_schedules', 'scheduled_by')) {
                $this->dropForeignIfExists('project_schedules', 'project_schedules_scheduled_by_foreign');
                $table->renameColumn('scheduled_by', 'user_id');
            }
        });

        Schema::table('project_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('project_schedules', 'user_id')) {
                if (! $this->foreignKeyExists('project_schedules', 'project_schedules_user_id_foreign')) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                }
            }
        });
    }

    protected function dropProjectCrewColumns(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('projects', function (Blueprint $table) {
            if (Schema::hasColumn('projects', 'photographer_id')) {
                $this->dropForeignIfExists('projects', 'projects_photographer_id_foreign');
                $table->dropColumn('photographer_id');
            }
            if (Schema::hasColumn('projects', 'editor_id')) {
                $this->dropForeignIfExists('projects', 'projects_editor_id_foreign');
                $table->dropColumn('editor_id');
            }
        });
    }

    protected function makeBookingStudioReferencesRestrictive(): void
    {
        $this->dropForeignIfExists('bookings', 'bookings_studio_location_id_foreign');
        $this->dropForeignIfExists('bookings', 'bookings_studio_room_id_foreign');

        Schema::table('bookings', function (Blueprint $table) {
            $table->foreignId('studio_location_id')->nullable(false)->change();
            $table->foreignId('studio_room_id')->nullable(false)->change();
            if (! $this->foreignKeyExists('bookings', 'bookings_studio_location_id_foreign')) {
                $table->foreign('studio_location_id')->references('id')->on('studio_locations');
            }
            if (! $this->foreignKeyExists('bookings', 'bookings_studio_room_id_foreign')) {
                $table->foreign('studio_room_id')->references('id')->on('studio_rooms');
            }
        });
    }

    private function dropForeignIfExists(string $table, string $foreignKey): void
    {
        if (DB::getDriverName() !== 'mysql') {
            try {
                Schema::table($table, function (Blueprint $blueprint) use ($foreignKey) {
                    $blueprint->dropForeign($foreignKey);
                });
            } catch (Throwable) {
                // SQLite test schemas can have different constraint metadata.
            }

            return;
        }

        if (! $this->foreignKeyExists($table, $foreignKey)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($foreignKey) {
            $blueprint->dropForeign($foreignKey);
        });
    }

    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $foreignKey)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }
};

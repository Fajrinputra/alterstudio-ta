<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->addIndexIfMissing('bookings', ['status', 'confirmed_at'], 'bookings_status_confirmed_idx');
        $this->addIndexIfMissing('bookings', ['client_id', 'status', 'created_at'], 'bookings_client_status_created_idx');
        $this->addIndexIfMissing('bookings', ['package_id', 'status'], 'bookings_package_status_idx');
        $this->addIndexIfMissing('bookings', ['booking_date', 'booking_time'], 'bookings_date_time_idx');
        $this->addIndexIfMissing('bookings', ['studio_location_id', 'booking_date'], 'bookings_location_date_idx');
        $this->addIndexIfMissing('bookings', ['studio_room_id', 'booking_date', 'booking_time'], 'bookings_room_date_time_idx');

        $this->addIndexIfMissing('projects', ['status', 'start_at'], 'projects_status_start_idx');
        $this->addIndexIfMissing('projects', ['start_at'], 'projects_start_at_idx');
        $this->addIndexIfMissing('projects', ['edit_requested_at'], 'projects_edit_requested_idx');

        $this->addIndexIfMissing('payments', ['status', 'type', 'paid_at'], 'payments_status_type_paid_idx');
        $this->addIndexIfMissing('payments', ['booking_id', 'status'], 'payments_booking_status_idx');

        $this->addIndexIfMissing('users', ['role', 'is_active'], 'users_role_active_idx');

        $this->addIndexIfMissing('service_packages', ['category_id', 'is_active', 'price'], 'packages_category_active_price_idx');
        $this->addIndexIfMissing('service_packages', ['is_active', 'name'], 'packages_active_name_idx');

        $this->addIndexIfMissing('studio_locations', ['is_active', 'name'], 'locations_active_name_idx');
        $this->addIndexIfMissing('studio_rooms', ['studio_location_id', 'is_active'], 'rooms_location_active_idx');

        $this->addIndexIfMissing('landing_hero_slides', ['is_active', 'sort_order'], 'hero_active_sort_idx');

        $this->addIndexIfMissing('project_schedules', ['start_at', 'end_at'], 'schedules_start_end_idx');
        $this->addIndexIfMissing('project_schedules', ['studio_room_id', 'start_at', 'end_at'], 'schedules_room_start_end_idx');
        $this->addIndexIfMissing('project_schedule_users', ['user_id', 'role'], 'schedule_users_user_role_idx');
    }

    public function down(): void
    {
        foreach ([
            'bookings' => [
                'bookings_status_confirmed_idx',
                'bookings_client_status_created_idx',
                'bookings_package_status_idx',
                'bookings_date_time_idx',
                'bookings_location_date_idx',
                'bookings_room_date_time_idx',
            ],
            'projects' => [
                'projects_status_start_idx',
                'projects_start_at_idx',
                'projects_edit_requested_idx',
            ],
            'payments' => [
                'payments_status_type_paid_idx',
                'payments_booking_status_idx',
            ],
            'users' => [
                'users_role_active_idx',
            ],
            'service_packages' => [
                'packages_category_active_price_idx',
                'packages_active_name_idx',
            ],
            'studio_locations' => [
                'locations_active_name_idx',
            ],
            'studio_rooms' => [
                'rooms_location_active_idx',
            ],
            'landing_hero_slides' => [
                'hero_active_sort_idx',
            ],
            'project_schedules' => [
                'schedules_start_end_idx',
                'schedules_room_start_end_idx',
            ],
            'project_schedule_users' => [
                'schedule_users_user_role_idx',
            ],
        ] as $table => $indexes) {
            foreach ($indexes as $index) {
                $this->dropIndexIfExists($table, $index);
            }
        }
    }

    private function addIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        if (! Schema::hasTable($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            return match (Schema::getConnection()->getDriverName()) {
                'mysql', 'mariadb' => count(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName])) > 0,
                'sqlite' => collect(DB::select("PRAGMA index_list('{$table}')"))
                    ->contains(fn ($index) => ($index->name ?? null) === $indexName),
                default => false,
            };
        } catch (Throwable) {
            return false;
        }
    }
};

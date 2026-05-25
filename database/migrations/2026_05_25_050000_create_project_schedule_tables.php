<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->unique()->constrained('projects')->cascadeOnDelete();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('studio_location_id')->constrained('studio_locations')->cascadeOnDelete();
            $table->foreignId('studio_room_id')->constrained('studio_rooms')->cascadeOnDelete();
            $table->foreignId('scheduled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->enum('status', ['SCHEDULED', 'LOCKED', 'CANCELLED'])->default('SCHEDULED');
            $table->timestamps();
        });

        Schema::create('project_schedule_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_schedule_id')->constrained('project_schedules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['PHOTOGRAPHER', 'EDITOR']);
            $table->timestamps();

            $table->unique(['project_schedule_id', 'role']);
            $table->unique(['project_schedule_id', 'user_id']);
        });

        $this->backfillExistingSchedules();
    }

    public function down(): void
    {
        Schema::dropIfExists('project_schedule_users');
        Schema::dropIfExists('project_schedules');
    }

    protected function backfillExistingSchedules(): void
    {
        DB::table('projects')
            ->join('bookings', 'bookings.id', '=', 'projects.booking_id')
            ->whereNotNull('projects.start_at')
            ->whereNotNull('projects.end_at')
            ->whereNotNull('projects.photographer_id')
            ->whereNotNull('projects.editor_id')
            ->whereNotNull('bookings.studio_location_id')
            ->whereNotNull('bookings.studio_room_id')
            ->select([
                'projects.id as project_id',
                'projects.booking_id',
                'projects.photographer_id',
                'projects.editor_id',
                'projects.start_at',
                'projects.end_at',
                'bookings.studio_location_id',
                'bookings.studio_room_id',
            ])
            ->orderBy('projects.id')
            ->get()
            ->each(function ($row) {
                $scheduleId = DB::table('project_schedules')->insertGetId([
                    'project_id' => $row->project_id,
                    'booking_id' => $row->booking_id,
                    'studio_location_id' => $row->studio_location_id,
                    'studio_room_id' => $row->studio_room_id,
                    'start_at' => $row->start_at,
                    'end_at' => $row->end_at,
                    'status' => 'SCHEDULED',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('project_schedule_users')->insert([
                    [
                        'project_schedule_id' => $scheduleId,
                        'user_id' => $row->photographer_id,
                        'role' => 'PHOTOGRAPHER',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'project_schedule_id' => $scheduleId,
                        'user_id' => $row->editor_id,
                        'role' => 'EDITOR',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ]);
            });
    }
};

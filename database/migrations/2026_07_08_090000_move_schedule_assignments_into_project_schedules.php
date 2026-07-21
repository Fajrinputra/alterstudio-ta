<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('project_schedules')) {
            return;
        }

        Schema::table('project_schedules', function (Blueprint $table) {
            if (! Schema::hasColumn('project_schedules', 'photographer_id')) {
                $table->foreignId('photographer_id')->nullable()->after('scheduled_by')->constrained('users')->restrictOnDelete();
            }

            if (! Schema::hasColumn('project_schedules', 'editor_id')) {
                $table->foreignId('editor_id')->nullable()->after('photographer_id')->constrained('users')->restrictOnDelete();
            }
        });

        if (Schema::hasTable('project_schedule_users')) {
            DB::table('project_schedule_users')
                ->orderBy('id')
                ->get()
                ->each(function ($assignment) {
                    $column = match ($assignment->role) {
                        'PHOTOGRAPHER' => 'photographer_id',
                        'EDITOR' => 'editor_id',
                        default => null,
                    };

                    if ($column === null) {
                        return;
                    }

                    DB::table('project_schedules')
                        ->where('id', $assignment->project_schedule_id)
                        ->update([$column => $assignment->user_id]);
                });
        }

        DB::table('project_schedules')
            ->whereNull('photographer_id')
            ->orWhereNull('editor_id')
            ->orderBy('id')
            ->get()
            ->each(function ($schedule) {
                $project = DB::table('projects')->where('id', $schedule->project_id)->first();

                DB::table('project_schedules')
                    ->where('id', $schedule->id)
                    ->update([
                        'photographer_id' => $schedule->photographer_id ?? $project?->photographer_id,
                        'editor_id' => $schedule->editor_id ?? $project?->editor_id,
                    ]);
            });

        $fallbackPhotographerId = DB::table('users')
            ->where('role', 'PHOTOGRAPHER')
            ->where('is_active', true)
            ->value('id') ?? DB::table('users')->where('role', 'PHOTOGRAPHER')->value('id');

        $fallbackEditorId = DB::table('users')
            ->where('role', 'EDITOR')
            ->where('is_active', true)
            ->value('id') ?? DB::table('users')->where('role', 'EDITOR')->value('id');

        if ($fallbackPhotographerId) {
            DB::table('project_schedules')
                ->whereNull('photographer_id')
                ->update(['photographer_id' => $fallbackPhotographerId]);
        }

        if ($fallbackEditorId) {
            DB::table('project_schedules')
                ->whereNull('editor_id')
                ->update(['editor_id' => $fallbackEditorId]);
        }

        $hasIncompleteSchedules = DB::table('project_schedules')
            ->whereNull('photographer_id')
            ->orWhereNull('editor_id')
            ->exists();

        if (! $hasIncompleteSchedules && DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE project_schedules MODIFY photographer_id BIGINT UNSIGNED NOT NULL');
            DB::statement('ALTER TABLE project_schedules MODIFY editor_id BIGINT UNSIGNED NOT NULL');
        }

        $this->addIndexIfMissing('project_schedules', ['photographer_id', 'start_at', 'end_at'], 'schedules_photographer_time_idx');
        $this->addIndexIfMissing('project_schedules', ['editor_id', 'start_at', 'end_at'], 'schedules_editor_time_idx');

        Schema::dropIfExists('project_schedule_users');
    }

    public function down(): void
    {
        if (! Schema::hasTable('project_schedules')) {
            return;
        }

        Schema::create('project_schedule_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_schedule_id')->constrained('project_schedules')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('role', ['PHOTOGRAPHER', 'EDITOR']);
            $table->timestamps();

            $table->unique(['project_schedule_id', 'role']);
            $table->unique(['project_schedule_id', 'user_id']);
        });

        DB::table('project_schedules')
            ->orderBy('id')
            ->get()
            ->each(function ($schedule) {
                foreach ([
                    'PHOTOGRAPHER' => $schedule->photographer_id,
                    'EDITOR' => $schedule->editor_id,
                ] as $role => $userId) {
                    if (! $userId) {
                        continue;
                    }

                    DB::table('project_schedule_users')->insert([
                        'project_schedule_id' => $schedule->id,
                        'user_id' => $userId,
                        'role' => $role,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

        Schema::table('project_schedules', function (Blueprint $table) {
            if ($this->indexExists('project_schedules', 'schedules_photographer_time_idx')) {
                $table->dropIndex('schedules_photographer_time_idx');
            }

            if ($this->indexExists('project_schedules', 'schedules_editor_time_idx')) {
                $table->dropIndex('schedules_editor_time_idx');
            }

            if (Schema::hasColumn('project_schedules', 'photographer_id')) {
                $table->dropConstrainedForeignId('photographer_id');
            }

            if (Schema::hasColumn('project_schedules', 'editor_id')) {
                $table->dropConstrainedForeignId('editor_id');
            }
        });
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

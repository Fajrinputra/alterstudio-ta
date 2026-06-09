<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->alignPasswordResetTokens();
        $this->alignProjectScheduleSchedulerColumn();
    }

    public function down(): void
    {
        if (Schema::hasTable('project_schedules') && Schema::hasColumn('project_schedules', 'scheduled_by')) {
            if (DB::getDriverName() === 'mysql' && $this->foreignKeyExists('project_schedules', 'project_schedules_scheduled_by_foreign')) {
                Schema::table('project_schedules', function (Blueprint $table) {
                    $table->dropForeign('project_schedules_scheduled_by_foreign');
                });
            }

            Schema::table('project_schedules', function (Blueprint $table) {
                $table->renameColumn('scheduled_by', 'user_id');
            });

            if (DB::getDriverName() === 'mysql') {
                DB::statement('ALTER TABLE project_schedules MODIFY user_id BIGINT UNSIGNED NULL');
                Schema::table('project_schedules', function (Blueprint $table) {
                    $table->foreign('user_id', 'project_schedules_user_id_foreign')
                        ->references('id')
                        ->on('users')
                        ->nullOnDelete();
                });
            }
        }
    }

    private function alignPasswordResetTokens(): void
    {
        if (! Schema::hasTable('password_reset_tokens')) {
            return;
        }

        if (! Schema::hasColumn('password_reset_tokens', 'user_id')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('email');
            });
        }

        if (Schema::hasTable('users')) {
            DB::table('password_reset_tokens')
                ->whereNull('user_id')
                ->select(['email'])
                ->get()
                ->each(function ($row): void {
                    $userId = DB::table('users')->where('email', $row->email)->value('id');

                    if ($userId) {
                        DB::table('password_reset_tokens')
                            ->where('email', $row->email)
                            ->update(['user_id' => $userId]);
                    }
                });
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if ($this->foreignKeyExists('password_reset_tokens', 'password_reset_tokens_email_foreign')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->dropForeign('password_reset_tokens_email_foreign');
            });
        }

        if ($this->foreignKeyExists('password_reset_tokens', 'password_reset_tokens_user_id_foreign')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->dropForeign('password_reset_tokens_user_id_foreign');
            });
        }

        DB::statement('
            DELETE prt FROM password_reset_tokens prt
            LEFT JOIN users u ON u.id = prt.user_id
            WHERE prt.user_id IS NULL OR u.id IS NULL
        ');

        if (! Schema::hasColumn('password_reset_tokens', 'id')) {
            DB::statement('ALTER TABLE password_reset_tokens DROP PRIMARY KEY');
            DB::statement('ALTER TABLE password_reset_tokens ADD id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        }

        DB::statement('ALTER TABLE password_reset_tokens MODIFY user_id BIGINT UNSIGNED NOT NULL');
        $this->addIndexIfMissing('password_reset_tokens', ['email'], 'password_reset_tokens_email_index');
        $this->addIndexIfMissing('password_reset_tokens', ['user_id'], 'password_reset_tokens_user_id_index');

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->foreign('user_id', 'password_reset_tokens_user_id_foreign')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    private function alignProjectScheduleSchedulerColumn(): void
    {
        if (! Schema::hasTable('project_schedules')) {
            return;
        }

        if (Schema::hasColumn('project_schedules', 'user_id')) {
            if (DB::getDriverName() === 'mysql' && $this->foreignKeyExists('project_schedules', 'project_schedules_user_id_foreign')) {
                Schema::table('project_schedules', function (Blueprint $table) {
                    $table->dropForeign('project_schedules_user_id_foreign');
                });
            }

            Schema::table('project_schedules', function (Blueprint $table) {
                $table->renameColumn('user_id', 'scheduled_by');
            });
        }

        if (! Schema::hasColumn('project_schedules', 'scheduled_by')) {
            Schema::table('project_schedules', function (Blueprint $table) {
                $table->foreignId('scheduled_by')->nullable()->after('studio_room_id');
            });
        }

        $fallbackUserId = DB::table('users')
            ->whereIn('role', ['OWNER', 'ADMIN'])
            ->orderByRaw("CASE WHEN role = 'OWNER' THEN 0 WHEN role = 'ADMIN' THEN 1 ELSE 2 END")
            ->value('id')
            ?? DB::table('users')->orderBy('id')->value('id');

        if ($fallbackUserId) {
            DB::table('project_schedules')
                ->whereNull('scheduled_by')
                ->update(['scheduled_by' => $fallbackUserId]);
        }

        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if ($this->foreignKeyExists('project_schedules', 'project_schedules_scheduled_by_foreign')) {
            Schema::table('project_schedules', function (Blueprint $table) {
                $table->dropForeign('project_schedules_scheduled_by_foreign');
            });
        }

        DB::statement('ALTER TABLE project_schedules MODIFY scheduled_by BIGINT UNSIGNED NOT NULL');
        $this->addIndexIfMissing('project_schedules', ['scheduled_by'], 'project_schedules_scheduled_by_index');

        Schema::table('project_schedules', function (Blueprint $table) {
            $table->foreign('scheduled_by', 'project_schedules_scheduled_by_foreign')
                ->references('id')
                ->on('users');
        });
    }

    private function addIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        if ($this->indexExists($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $database = DB::getDatabaseName();

        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $foreignKey)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function indexExists(string $table, string $indexName): bool
    {
        if (DB::getDriverName() !== 'mysql') {
            return false;
        }

        $database = DB::getDatabaseName();

        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', $database)
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};

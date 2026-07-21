<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('password_reset_tokens')) {
            return;
        }

        if (! Schema::hasColumn('password_reset_tokens', 'user_id')) {
            return;
        }

        $this->dropForeignIfExists('password_reset_tokens', 'password_reset_tokens_user_id_foreign');
        $this->dropForeignIfExists('password_reset_tokens', 'password_reset_tokens_email_foreign');

        DB::statement('
            DELETE prt FROM password_reset_tokens prt
            LEFT JOIN users u ON u.id = prt.user_id
            WHERE prt.user_id IS NULL OR u.id IS NULL
        ');

        if (Schema::hasColumn('password_reset_tokens', 'id')) {
            DB::statement('
                DELETE older FROM password_reset_tokens older
                INNER JOIN password_reset_tokens newer
                    ON older.user_id = newer.user_id
                    AND older.id < newer.id
            ');

            DB::statement('ALTER TABLE password_reset_tokens MODIFY id BIGINT UNSIGNED NOT NULL');
        }

        if ($this->hasPrimaryKey('password_reset_tokens')) {
            DB::statement('ALTER TABLE password_reset_tokens DROP PRIMARY KEY');
        }

        if (Schema::hasColumn('password_reset_tokens', 'id')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->dropColumn('id');
            });
        }

        DB::statement('ALTER TABLE password_reset_tokens MODIFY user_id BIGINT UNSIGNED NOT NULL');

        if (! $this->primaryKeyIs('password_reset_tokens', ['user_id'])) {
            DB::statement('ALTER TABLE password_reset_tokens ADD PRIMARY KEY (user_id)');
        }

        $this->addIndexIfMissing('password_reset_tokens', ['email'], 'password_reset_tokens_email_index');

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->foreign('user_id', 'password_reset_tokens_user_id_foreign')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('password_reset_tokens')) {
            return;
        }

        $this->dropForeignIfExists('password_reset_tokens', 'password_reset_tokens_user_id_foreign');

        if ($this->primaryKeyIs('password_reset_tokens', ['user_id'])) {
            DB::statement('ALTER TABLE password_reset_tokens DROP PRIMARY KEY');
        }

        if (! Schema::hasColumn('password_reset_tokens', 'id')) {
            DB::statement('ALTER TABLE password_reset_tokens ADD id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST');
        }

        $this->addIndexIfMissing('password_reset_tokens', ['user_id'], 'password_reset_tokens_user_id_index');

        Schema::table('password_reset_tokens', function (Blueprint $table) {
            $table->foreign('user_id', 'password_reset_tokens_user_id_foreign')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    private function hasPrimaryKey(string $table): bool
    {
        return count($this->primaryKeyColumns($table)) > 0;
    }

    private function primaryKeyIs(string $table, array $columns): bool
    {
        return $this->primaryKeyColumns($table) === $columns;
    }

    private function primaryKeyColumns(string $table): array
    {
        return collect(DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = 'PRIMARY'"))
            ->sortBy('Seq_in_index')
            ->pluck('Column_name')
            ->values()
            ->all();
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

    private function dropForeignIfExists(string $table, string $foreignKey): void
    {
        if (! $this->foreignKeyExists($table, $foreignKey)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($foreignKey) {
            $blueprint->dropForeign($foreignKey);
        });
    }

    private function foreignKeyExists(string $table, string $foreignKey): bool
    {
        return DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('CONSTRAINT_NAME', $foreignKey)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->exists();
    }

    private function indexExists(string $table, string $indexName): bool
    {
        return DB::table('information_schema.STATISTICS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $table)
            ->where('INDEX_NAME', $indexName)
            ->exists();
    }
};

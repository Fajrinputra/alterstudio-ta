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

        if ($this->indexExists('password_reset_tokens', 'password_reset_tokens_user_id_index')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->dropIndex('password_reset_tokens_user_id_index');
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || ! Schema::hasTable('password_reset_tokens')) {
            return;
        }

        if (! $this->indexExists('password_reset_tokens', 'password_reset_tokens_user_id_index')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->index('user_id', 'password_reset_tokens_user_id_index');
            });
        }
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

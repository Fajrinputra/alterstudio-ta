<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // sessions/user_id adalah relasi logis ke users.id, tambahkan FK di MySQL.
        if (DB::getDriverName() !== 'mysql' || !Schema::hasTable('sessions') || !Schema::hasTable('users')) {
            return;
        }

        if ($this->foreignKeyExists('sessions', 'sessions_user_id_foreign')) {
            return;
        }

        DB::table('sessions')
            ->whereNotNull('user_id')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'sessions.user_id');
            })
            ->update(['user_id' => null]);

        Schema::table('sessions', function (Blueprint $table) {
            $table->foreign('user_id', 'sessions_user_id_foreign')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || !Schema::hasTable('sessions')) {
            return;
        }

        if (!$this->foreignKeyExists('sessions', 'sessions_user_id_foreign')) {
            return;
        }

        Schema::table('sessions', function (Blueprint $table) {
            $table->dropForeign('sessions_user_id_foreign');
        });
    }

    protected function foreignKeyExists(string $tableName, string $constraintName): bool
    {
        $row = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', $tableName)
            ->where('CONSTRAINT_NAME', $constraintName)
            ->where('CONSTRAINT_TYPE', 'FOREIGN KEY')
            ->first();

        return $row !== null;
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menjadikan password_reset_tokens relasional (FK ke users).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Diterapkan khusus MySQL sesuai strategi informasi_schema yang dipakai.
        if (DB::getDriverName() !== 'mysql' || !Schema::hasTable('password_reset_tokens') || !Schema::hasTable('users')) {
            return;
        }

        if (!Schema::hasColumn('password_reset_tokens', 'user_id')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->after('email');
            });
        }

        // Bersihkan token orphan agar penambahan FK tidak gagal.
        DB::statement('
            DELETE prt FROM password_reset_tokens prt
            LEFT JOIN users u ON u.email = prt.email
            WHERE u.id IS NULL
        ');

        DB::statement('
            UPDATE password_reset_tokens prt
            INNER JOIN users u ON u.email = prt.email
            SET prt.user_id = u.id
        ');

        if (!$this->foreignKeyExists('password_reset_tokens', 'password_reset_tokens_user_id_foreign')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->foreign('user_id', 'password_reset_tokens_user_id_foreign')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }

        if (!$this->foreignKeyExists('password_reset_tokens', 'password_reset_tokens_email_foreign')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->foreign('email', 'password_reset_tokens_email_foreign')
                    ->references('email')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql' || !Schema::hasTable('password_reset_tokens')) {
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

        if (Schema::hasColumn('password_reset_tokens', 'user_id')) {
            Schema::table('password_reset_tokens', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
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

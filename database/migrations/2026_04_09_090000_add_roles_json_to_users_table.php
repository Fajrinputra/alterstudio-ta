<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'roles')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('roles')->nullable()->after('role');
            });
        }

        DB::table('users')
            ->select('id', 'role', 'roles')
            ->orderBy('id')
            ->eachById(function ($user) {
                $roles = json_decode((string) $user->roles, true);

                if (! is_array($roles) || empty($roles)) {
                    $roles = [$user->role];
                }

                $roles = array_values(array_unique(array_filter($roles)));

                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['roles' => json_encode($roles, JSON_UNESCAPED_UNICODE)]);
            }, 'id');
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'roles')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('roles');
            });
        }
    }
};

<?php

use App\Enums\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            $values = collect(Role::all())
                ->map(fn (string $role) => "'{$role}'")
                ->implode(',');

            DB::statement("ALTER TABLE users MODIFY role ENUM({$values}) NOT NULL DEFAULT 'CLIENT'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('users')
                ->where('role', Role::OWNER->value)
                ->update(['role' => Role::MANAGER->value]);

            $values = collect(Role::all())
                ->reject(fn (string $role) => $role === Role::OWNER->value)
                ->map(fn (string $role) => "'{$role}'")
                ->implode(',');

            DB::statement("ALTER TABLE users MODIFY role ENUM({$values}) NOT NULL DEFAULT 'CLIENT'");
        }
    }
};

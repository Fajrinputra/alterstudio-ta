<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\Role;

/**
 * Menambahkan atribut RBAC dan status akun pada users.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Role berbasis enum agar validasi konsisten dengan App\Enums\Role.
            $table->enum('role', Role::all())
                ->default(Role::CLIENT->value)
                ->after('password');
            $table->boolean('is_active')->default(true)->after('role');
            $table->string('avatar_path')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
};

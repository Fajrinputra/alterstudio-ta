<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->changeString('users', 'name', 50);
        $this->changeString('users', 'email', 50);
        $this->changeString('users', 'no_hp', 20, true);

        $this->changeString('password_reset_tokens', 'email', 50);

        $this->changeString('service_categories', 'name', 50);
        $this->changeString('service_packages', 'name', 50);

        $this->changeString('studio_locations', 'name', 50);
        $this->changeString('studio_locations', 'slug', 50);
        $this->changeString('studio_locations', 'phone', 20, true);
        $this->changeString('studio_locations', 'email', 50, true);

        $this->changeString('studio_rooms', 'name', 50);

        $this->changeString('landing_hero_slides', 'eyebrow', 50, true);
        $this->changeString('landing_hero_slides', 'title', 50);

        $this->changeString('payments', 'reference', 50, true);
        $this->changeString('payments', 'order_id', 50, true);
    }

    public function down(): void
    {
        $this->changeString('users', 'name', 100);
        $this->changeString('users', 'email', 100);
        $this->changeString('users', 'no_hp', 20, true);

        $this->changeString('password_reset_tokens', 'email', 100);

        $this->changeString('service_categories', 'name', 100);
        $this->changeString('service_packages', 'name', 100);

        $this->changeString('studio_locations', 'name', 100);
        $this->changeString('studio_locations', 'slug', 100);
        $this->changeString('studio_locations', 'phone', 20, true);
        $this->changeString('studio_locations', 'email', 100, true);

        $this->changeString('studio_rooms', 'name', 100);

        $this->changeString('landing_hero_slides', 'eyebrow', 150, true);
        $this->changeString('landing_hero_slides', 'title', 100);

        $this->changeString('payments', 'reference', 100, true);
        $this->changeString('payments', 'order_id', 100, true);
    }

    private function changeString(string $table, string $column, int $length, bool $nullable = false): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($column, $length, $nullable) {
            $definition = $blueprint->string($column, $length);

            if ($nullable) {
                $definition->nullable();
            }

            $definition->change();
        });
    }
};

<?php

namespace Database\Factories;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory paket layanan.
 *
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ServicePackage>
 */
class ServicePackageFactory extends Factory
{
    public function definition(): array
    {
        $category = ServiceCategory::first() ?? ServiceCategory::factory()->create();

        return [
            'category_id' => $category->id,
            'name' => 'Standard Package',
            'price' => 1500000,
            'description' => 'Standard photo session',
            'features' => ['2 hours shoot', '20 edited photos'],
        ];
    }
}

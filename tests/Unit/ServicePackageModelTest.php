<?php

namespace Tests\Unit;

use App\Models\ServicePackage;
use Tests\TestCase;

class ServicePackageModelTest extends TestCase
{
    public function test_features_are_normalized_from_json_and_empty_values_are_removed(): void
    {
        $package = new ServicePackage();
        $package->setRawAttributes([
            'features' => json_encode(['Cetak foto', '', '  File digital  ']),
        ]);

        $this->assertSame(['Cetak foto', 'File digital'], $package->features);
    }

    public function test_addons_are_normalized_and_price_can_be_parsed_from_label(): void
    {
        $package = new ServicePackage();
        $package->setRawAttributes([
            'addons' => json_encode([
                ['label' => 'Tambah waktu 100k / sesi', 'price' => 0, 'unit' => 'sesi'],
                ['label' => '  Cetak 10R  ', 'price' => 25000, 'unit' => 'lembar', 'is_active' => false],
                ['label' => '', 'price' => 999],
            ]),
        ]);

        $addons = $package->addons;

        $this->assertCount(2, $addons);
        $this->assertSame('Tambah waktu', $addons[0]['label']);
        $this->assertSame(100000, $addons[0]['price']);
        $this->assertTrue($addons[0]['is_active']);
        $this->assertSame('Cetak 10R', $addons[1]['label']);
        $this->assertSame(25000, $addons[1]['price']);
        $this->assertFalse($addons[1]['is_active']);
    }

    public function test_gallery_and_overview_image_fallback_are_normalized(): void
    {
        $package = new ServicePackage();
        $package->setRawAttributes([
            'cover_image' => null,
            'gallery' => json_encode([
                ['path' => 'packages/1/a.jpg'],
                'packages/1/b.jpg',
                ['invalid' => 'skip'],
            ]),
        ]);

        $this->assertSame(['packages/1/a.jpg', 'packages/1/b.jpg'], $package->gallery);
        $this->assertSame('packages/1/a.jpg', $package->overview_image);

        $package->cover_image = 'packages/1/cover.jpg';
        $this->assertSame('packages/1/cover.jpg', $package->overview_image);
    }
}

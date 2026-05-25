<?php

namespace Tests\Unit;

use App\Models\StudioLocation;
use Tests\TestCase;

class StudioLocationModelTest extends TestCase
{
    /**
     * Pengujian: normalisasi galeri foto cabang studio.
     * Hasil yang diharapkan: hanya path foto valid yang dipakai dan foto pertama menjadi foto utama.
     */
    public function test_photo_gallery_is_normalized_and_photo_path_uses_first_photo(): void
    {
        $location = new StudioLocation();
        $location->setRawAttributes([
            'photo_gallery' => json_encode([
                ['path' => 'locations/1/front.jpg'],
                'locations/1/lobby.jpg',
                ['broken' => 'skip'],
            ]),
        ]);

        $this->assertSame(['locations/1/front.jpg', 'locations/1/lobby.jpg'], $location->photo_gallery);
        $this->assertSame('locations/1/front.jpg', $location->photo_path);
    }
}

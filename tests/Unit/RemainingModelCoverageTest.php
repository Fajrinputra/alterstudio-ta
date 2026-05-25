<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use Tests\TestCase;

class RemainingModelCoverageTest extends TestCase
{
    public function test_remaining_model_relationships_are_exposed(): void
    {
        $booking = new Booking();
        $project = new Project();
        $location = new StudioLocation();
        $user = new User();

        $this->assertSame('studio_location_id', $booking->studioLocation()->getForeignKeyName());
        $this->assertSame('studio_room_id', $booking->studioRoom()->getForeignKeyName());
        $this->assertSame('project_id', $project->selections()->getForeignKeyName());
        $this->assertSame('studio_location_id', $location->bookings()->getForeignKeyName());
        $this->assertSame('user_id', $user->schedulesAsPhotographer()->getForeignKeyName());
        $this->assertSame('user_id', $user->schedulesAsEditor()->getForeignKeyName());
    }

    public function test_remaining_model_normalizers_handle_non_array_items(): void
    {
        $booking = new Booking();
        $booking->setRawAttributes([
            'selected_addons' => json_encode([
                'invalid',
                ['label' => '', 'price' => 1000],
                ['label' => 'Valid', 'price' => 2000, 'quantity' => 0],
            ]),
        ]);

        $this->assertSame([
            [
                'label' => 'Valid',
                'price' => 2000,
                'unit' => '',
                'quantity' => 1,
                'subtotal' => 2000,
            ],
        ], $booking->selected_addons);

        $package = new ServicePackage();
        $package->setRawAttributes([
            'addons' => json_encode([
                'invalid',
                ['label' => 'Tanpa nominal', 'price' => 0],
            ]),
            'gallery' => json_encode([
                123,
                ['path' => 'gallery/path.jpg'],
                'gallery/string.jpg',
            ]),
        ]);

        $this->assertSame([
            [
                'label' => 'Tanpa nominal',
                'price' => 0,
                'unit' => '',
                'is_active' => true,
            ],
        ], $package->addons);
        $this->assertSame(['gallery/path.jpg', 'gallery/string.jpg'], $package->gallery);

        $location = new StudioLocation();
        $location->setRawAttributes([
            'photo_gallery' => json_encode([
                123,
                ['path' => 'locations/photo.jpg'],
                'locations/string.jpg',
            ]),
        ]);

        $this->assertSame(['locations/photo.jpg', 'locations/string.jpg'], $location->photo_gallery);
    }
}

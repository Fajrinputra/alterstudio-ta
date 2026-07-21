<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\LandingHeroSlide;
use App\Models\Project;
use App\Models\ProjectSchedule;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Tests\TestCase;

class RemainingModelCoverageTest extends TestCase
{
    public function test_remaining_model_relationships_are_exposed(): void
    {
        $booking = new Booking();
        $project = new Project();
        $location = new StudioLocation();
        $slide = new LandingHeroSlide();
        $schedule = new ProjectSchedule();
        $user = new User();

        $this->assertSame('studio_location_id', $booking->studioLocation()->getForeignKeyName());
        $this->assertSame('studio_room_id', $booking->studioRoom()->getForeignKeyName());
        $this->assertSame('project_id', $project->selections()->getForeignKeyName());
        $this->assertSame('studio_location_id', $location->bookings()->getForeignKeyName());
        $this->assertSame('user_id', $slide->creator()->getForeignKeyName());
        $this->assertSame('user_id', $slide->updater()->getForeignKeyName());
        $this->assertSame('project_id', $schedule->project()->getForeignKeyName());
        $this->assertSame('studio_location_id', $schedule->studioLocation()->getForeignKeyName());
        $this->assertSame('studio_room_id', $schedule->studioRoom()->getForeignKeyName());
        $this->assertSame('scheduled_by', $schedule->scheduler()->getForeignKeyName());
        $this->assertSame('photographer_id', $schedule->photographer()->getForeignKeyName());
        $this->assertSame('editor_id', $schedule->editor()->getForeignKeyName());
        $this->assertSame('photographer_id', $user->schedulesAsPhotographer()->getForeignKeyName());
        $this->assertSame('editor_id', $user->schedulesAsEditor()->getForeignKeyName());
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

    public function test_remaining_model_accessors_and_alias_mutators_are_exposed(): void
    {
        $slide = new LandingHeroSlide();
        $slide->created_by = 12;
        $this->assertSame(12, $slide->user_id);
        $slide->updated_by = 15;
        $this->assertSame(15, $slide->user_id);

        $booking = new Booking();
        $booking->setRawAttributes(['location' => 'Cabang Lama - Studio Lama']);
        $this->assertSame('Cabang Lama - Studio Lama', $booking->location);

        $bookingWithRelations = new Booking();
        $bookingWithRelations->setRelation('studioLocation', new StudioLocation(['name' => 'Cabang Baru']));
        $bookingWithRelations->setRelation('studioRoom', new StudioRoom(['name' => 'Studio A']));
        $this->assertSame('Cabang Baru - Studio A', $bookingWithRelations->location);

        $photographer = new User(['name' => 'Fotografer Test']);
        $photographer->id = 21;
        $editor = new User(['name' => 'Editor Test']);
        $editor->id = 22;

        $schedule = new ProjectSchedule();
        $schedule->photographer_id = $photographer->id;
        $schedule->editor_id = $editor->id;
        $schedule->setRelation('photographer', $photographer);
        $schedule->setRelation('editor', $editor);

        $this->assertSame(21, $schedule->photographer_id);
        $this->assertSame(22, $schedule->editor_id);
        $this->assertSame($photographer, $schedule->photographer);
        $this->assertSame($editor, $schedule->editor);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\MediaAsset;
use App\Models\PhotoSelection;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PhotoSelectionLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_cannot_select_more_than_five_photos(): void
    {
        $package = ServicePackage::factory()->create();
        $client = User::factory()->create(['role' => Role::CLIENT]);

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'status' => 'PAID',
        ]);

        $project = Project::factory()->create(['booking_id' => $booking->id]);

        MediaAsset::factory()->count(6)->create(['project_id' => $project->id]);

        $assets = MediaAsset::where('project_id', $project->id)->get();

        // Seed 5 selections
        foreach ($assets->take(5) as $asset) {
            PhotoSelection::create([
                'project_id' => $project->id,
                'client_id' => $client->id,
                'media_asset_id' => $asset->id,
            ]);
        }

        $sixth = $assets->last();

        $this->actingAs($client)
            ->post("/projects/{$project->id}/selections", [
                'media_asset_id' => $sixth->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Maksimum 5 foto dapat dipilih.');
    }
}

<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\MediaAsset;
use App\Models\PhotoSelection;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account_without_password_confirmation(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile');

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_user_with_completed_history_is_anonymized_when_deleting_account(): void
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $originalEmail = $client->email;

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'status' => Booking::STATUS_PAID,
        ]);

        $project = Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_FINAL,
        ]);

        $media = MediaAsset::factory()->create([
            'project_id' => $project->id,
            'uploaded_by' => $client->id,
        ]);

        PhotoSelection::create([
            'project_id' => $project->id,
            'client_id' => $client->id,
            'media_asset_id' => $media->id,
            'selected_at' => now(),
        ]);

        $response = $this
            ->actingAs($client)
            ->delete('/profile');

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();

        $client->refresh();
        $this->assertSame('Akun Dihapus', $client->name);
        $this->assertFalse($client->is_active);
        $this->assertNull($client->no_hp);
        $this->assertNotSame($originalEmail, $client->email);
        $this->assertStringStartsWith('deleted-user-'.$client->id.'-', $client->email);
    }
}

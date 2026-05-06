<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EditRequestLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_submit_photo_codes_and_edit_description_once(): void
    {
        [$project, $client] = $this->makeProject([
            'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-project',
            'raw_drive_uploaded_at' => now(),
            'status' => Project::STATUS_SHOOT_DONE,
        ]);

        $this->actingAs($client)
            ->post(route('projects.edit-request.store', $project), [
                'edit_photo_codes' => 'D001, D014, D027',
                'edit_request_note' => 'Retouch natural dan tone hangat.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Permintaan edit berhasil dikirim ke editor.');

        $project->refresh();
        $this->assertEquals(Project::STATUS_EDITING, $project->status);
        $this->assertTrue($project->selections_locked);
        $this->assertEquals('D001, D014, D027', $project->edit_photo_codes);

        $this->actingAs($client)
            ->post(route('projects.edit-request.store', $project), [
                'edit_photo_codes' => 'D002',
                'edit_request_note' => 'Ubah pilihan.',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Permintaan edit sudah dikirim dan tidak dapat diubah.');
    }

    public function test_client_cannot_submit_edit_request_before_drive_link_exists(): void
    {
        [$project, $client] = $this->makeProject();

        $this->actingAs($client)
            ->post(route('projects.edit-request.store', $project), [
                'edit_photo_codes' => 'D001',
                'edit_request_note' => 'Edit natural.',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Link Drive foto mentah belum tersedia.');
    }

    public function test_client_cannot_submit_more_than_ten_photo_codes(): void
    {
        [$project, $client] = $this->makeProject([
            'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-project',
            'raw_drive_uploaded_at' => now(),
            'status' => Project::STATUS_SHOOT_DONE,
        ]);

        $this->actingAs($client)
            ->post(route('projects.edit-request.store', $project), [
                'edit_photo_codes' => 'D001, D002, D003, D004, D005, D006, D007, D008, D009, D010, D011',
                'edit_request_note' => 'Retouch natural.',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Maksimal 10 foto dapat diajukan untuk diedit.');

        $this->assertNull($project->fresh()->edit_requested_at);
    }

    public function test_cancelled_booking_cannot_submit_edit_request(): void
    {
        [$project, $client] = $this->makeProject([
            'raw_drive_url' => 'https://drive.google.com/drive/folders/raw-project',
            'raw_drive_uploaded_at' => now(),
            'status' => Project::STATUS_SHOOT_DONE,
        ]);
        $project->booking->update(['status' => Booking::STATUS_CANCELLED]);

        $this->actingAs($client)
            ->post(route('projects.edit-request.store', $project), [
                'edit_photo_codes' => 'D001',
                'edit_request_note' => 'Retouch natural.',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Pemesanan sudah dibatalkan. Proses pasca-produksi tidak dapat dilanjutkan.');

        $this->assertNull($project->fresh()->edit_requested_at);
    }

    /**
     * @param array<string, mixed> $projectAttributes
     * @return array{0: Project, 1: User}
     */
    protected function makeProject(array $projectAttributes = []): array
    {
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $package = ServicePackage::factory()->create();

        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'status' => Booking::STATUS_PAID,
        ]);

        $project = Project::factory()->create(array_merge([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_SCHEDULED,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay()->setTime(11, 0),
            'end_at' => now()->addDay()->setTime(12, 0),
        ], $projectAttributes));

        return [$project, $client];
    }
}

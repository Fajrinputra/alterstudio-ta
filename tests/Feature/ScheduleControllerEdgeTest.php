<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Controllers\ScheduleController;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\StudioRoom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ScheduleControllerEdgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_schedule_index_filters_for_crew_and_admin(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $otherPhotographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);

        $scheduled = $this->projectFor(Booking::STATUS_PAID);
        $scheduled->update([
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDay()->setTime(13, 0),
            'end_at' => now()->addDay()->setTime(14, 0),
            'status' => Project::STATUS_SCHEDULED,
        ]);
        $other = $this->projectFor(Booking::STATUS_PAID);
        $other->update([
            'photographer_id' => $otherPhotographer->id,
            'editor_id' => $editor->id,
            'start_at' => now()->addDays(2)->setTime(13, 0),
            'end_at' => now()->addDays(2)->setTime(14, 0),
            'status' => Project::STATUS_SCHEDULED,
        ]);
        $unscheduled = $this->projectFor(Booking::STATUS_PAID);

        $this->actingAs($photographer)
            ->get(route('admin.schedules', ['assignment_role' => 'photographer']))
            ->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->pluck('id')->all() === [$scheduled->id])
            ->assertViewHas('readOnly', true);

        $this->actingAs($admin)
            ->get(route('admin.schedules', [
                'schedule_status' => 'unscheduled',
                'package_id' => $unscheduled->booking->package_id,
            ]))
            ->assertOk()
            ->assertViewHas('projects', fn ($projects) => $projects->contains('id', $unscheduled->id))
            ->assertViewHas('readOnly', false);
    }

    public function test_store_schedule_rejects_missing_booking_cancelled_unpaid_and_locked_project(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        [$photographer, $editor, $room] = $this->crewAndRoom();
        $payload = [
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'studio_room_id' => $room->id,
        ];

        $missingBooking = Project::factory()->make(['id' => 999, 'booking_id' => 999]);
        $request = Request::create('/fake', 'POST', $payload, [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->setUserResolver(fn () => $admin);
        $this->assertSame(404, app(ScheduleController::class)->store($request, $missingBooking)->getStatusCode());

        $cancelled = $this->projectFor(Booking::STATUS_CANCELLED, $room->studio_location_id);
        $this->actingAs($admin)
            ->postJson("/projects/{$cancelled->id}/schedule", $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Pemesanan sudah dibatalkan dan tidak dapat dijadwalkan.');

        $unpaid = $this->projectFor(Booking::STATUS_WAITING_PAYMENT, $room->studio_location_id);
        $this->actingAs($admin)
            ->postJson("/projects/{$unpaid->id}/schedule", $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Pemesanan harus sudah dibayar minimal DP sebelum dijadwalkan.');

        $locked = $this->projectFor(Booking::STATUS_PAID, $room->studio_location_id);
        $locked->update(['start_at' => now()->addDay(), 'end_at' => now()->addDay()->addHour()]);
        $this->actingAs($admin)
            ->postJson("/projects/{$locked->id}/schedule", $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Jadwal sudah tersimpan dan tidak dapat diubah dari proses ini.');
    }

    public function test_store_schedule_rejects_bad_assignees_and_wrong_room(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        [$photographer, $editor, $room] = $this->crewAndRoom();
        $project = $this->projectFor(Booking::STATUS_PAID, $room->studio_location_id);

        $this->actingAs($admin)
            ->postJson("/projects/{$project->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $photographer->id,
                'studio_room_id' => $room->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Fotografer dan editor harus menggunakan akun yang berbeda.');

        $inactivePhotographer = User::factory()->create(['role' => Role::PHOTOGRAPHER, 'is_active' => false]);
        $this->actingAs($admin)
            ->postJson("/projects/{$project->id}/schedule", [
                'photographer_id' => $inactivePhotographer->id,
                'editor_id' => $editor->id,
                'studio_room_id' => $room->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Akun fotografer yang dipilih tidak memiliki akses fotografer aktif.');

        $inactiveEditor = User::factory()->create(['role' => Role::EDITOR, 'is_active' => false]);
        $this->actingAs($admin)
            ->postJson("/projects/{$project->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $inactiveEditor->id,
                'studio_room_id' => $room->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Akun editor yang dipilih tidak memiliki akses editor aktif.');

        $otherLocation = StudioLocation::create(['name' => 'Cabang Lain', 'slug' => 'cabang-lain', 'is_active' => true]);
        $wrongRoom = StudioRoom::create(['studio_location_id' => $otherLocation->id, 'name' => 'Studio Lain', 'is_active' => true]);
        $this->actingAs($admin)
            ->postJson("/projects/{$project->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'studio_room_id' => $wrongRoom->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Ruangan tidak valid untuk cabang ini');
    }

    public function test_update_schedule_rejects_missing_booking_unscheduled_locked_and_invalid_room(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        [$photographer, $editor, $room] = $this->crewAndRoom();
        $payload = [
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'studio_room_id' => $room->id,
        ];

        $missingBooking = Project::factory()->make(['id' => 1999, 'booking_id' => 1999]);
        $request = Request::create('/fake', 'PUT', $payload, [], [], ['HTTP_ACCEPT' => 'application/json']);
        $request->setUserResolver(fn () => $admin);
        $this->assertSame(404, app(ScheduleController::class)->update($request, $missingBooking)->getStatusCode());

        $unscheduled = $this->projectFor(Booking::STATUS_PAID, $room->studio_location_id);
        $this->actingAs($admin)
            ->putJson("/projects/{$unscheduled->id}/schedule", $payload)
            ->assertNotFound()
            ->assertJsonPath('message', 'Jadwal belum tersedia');

        $editing = $this->scheduledProject($room, Project::STATUS_EDITING);
        $this->actingAs($admin)
            ->putJson("/projects/{$editing->id}/schedule", $payload)
            ->assertStatus(422)
            ->assertJsonPath('message', 'Jadwal tidak bisa diubah karena project sudah berjalan');

        $scheduled = $this->scheduledProject($room);
        $otherLocation = StudioLocation::create(['name' => 'Cabang Update Lain', 'slug' => 'update-lain', 'is_active' => true]);
        $wrongRoom = StudioRoom::create(['studio_location_id' => $otherLocation->id, 'name' => 'Studio Update Lain', 'is_active' => true]);
        $this->actingAs($admin)
            ->putJson("/projects/{$scheduled->id}/schedule", [
                'photographer_id' => $photographer->id,
                'editor_id' => $editor->id,
                'studio_room_id' => $wrongRoom->id,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Ruangan tidak valid untuk cabang ini');
    }

    public function test_destroy_method_handles_json_paths(): void
    {
        [$photographer, $editor, $room] = $this->crewAndRoom();
        $controller = app(ScheduleController::class);
        $request = Request::create('/fake', 'DELETE', [], [], [], ['HTTP_ACCEPT' => 'application/json']);

        $unscheduled = $this->projectFor(Booking::STATUS_PAID, $room->studio_location_id);
        $this->assertSame(404, $controller->destroy($request, $unscheduled)->getStatusCode());

        $locked = $this->scheduledProject($room, Project::STATUS_EDITING);
        $this->assertSame(422, $controller->destroy($request, $locked)->getStatusCode());

        $scheduled = $this->scheduledProject($room);
        $scheduled->update([
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
        ]);
        $response = $controller->destroy($request, $scheduled);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull($scheduled->fresh()->start_at);
        $this->assertEquals(Project::STATUS_DRAFT, $scheduled->fresh()->status);
    }

    private function crewAndRoom(): array
    {
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER, 'is_active' => true]);
        $editor = User::factory()->create(['role' => Role::EDITOR, 'is_active' => true]);
        $location = StudioLocation::create(['name' => 'Cabang Schedule '.uniqid(), 'slug' => 'schedule-'.uniqid(), 'is_active' => true]);
        $room = StudioRoom::create(['studio_location_id' => $location->id, 'name' => 'Studio Schedule', 'is_active' => true]);

        return [$photographer, $editor, $room];
    }

    private function projectFor(string $bookingStatus, ?int $locationId = null): Project
    {
        $location = $locationId
            ? StudioLocation::find($locationId)
            : StudioLocation::create(['name' => 'Cabang Project '.uniqid(), 'slug' => 'project-'.uniqid(), 'is_active' => true]);
        $package = ServicePackage::factory()->create(['duration_minutes' => 60]);
        $booking = Booking::factory()->create([
            'status' => $bookingStatus,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'booking_date' => now()->addDay()->toDateString(),
            'booking_time' => '13:00',
        ]);

        return Project::factory()->create([
            'booking_id' => $booking->id,
            'status' => Project::STATUS_DRAFT,
        ]);
    }

    private function scheduledProject(StudioRoom $room, string $status = Project::STATUS_SCHEDULED): Project
    {
        $project = $this->projectFor(Booking::STATUS_PAID, $room->studio_location_id);
        $project->booking->update(['studio_room_id' => $room->id]);
        $project->update([
            'status' => $status,
            'start_at' => now()->addDay()->setTime(13, 0),
            'end_at' => now()->addDay()->setTime(14, 0),
        ]);

        return $project;
    }
}

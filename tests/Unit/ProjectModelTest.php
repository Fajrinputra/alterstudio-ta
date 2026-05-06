<?php

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    public function test_schedule_attribute_returns_null_when_schedule_data_is_missing(): void
    {
        $project = new Project([
            'status' => Project::STATUS_DRAFT,
            'photographer_id' => null,
            'editor_id' => null,
            'start_at' => null,
            'end_at' => null,
        ]);

        $this->assertNull($project->schedule);
        $this->assertFalse($project->hasSchedule());
    }

    public function test_schedule_attribute_exposes_virtual_schedule_object(): void
    {
        $project = new Project([
            'status' => Project::STATUS_SCHEDULED,
            'photographer_id' => 10,
            'editor_id' => 20,
            'start_at' => Carbon::parse('2026-04-30 13:00:00'),
            'end_at' => Carbon::parse('2026-04-30 14:00:00'),
        ]);
        $project->setRelation('photographer', new User(['name' => 'Foto 1']));
        $project->setRelation('editor', new User(['name' => 'Editor 1']));

        $schedule = $project->schedule;

        $this->assertNotNull($schedule);
        $this->assertSame(10, $schedule->photographer_id);
        $this->assertSame(20, $schedule->editor_id);
        $this->assertTrue($project->hasSchedule());
    }

    public function test_status_label_maps_project_states(): void
    {
        $labels = [
            Project::STATUS_DRAFT => 'Belum Dijadwalkan',
            Project::STATUS_SCHEDULED => 'Terjadwal',
            Project::STATUS_SHOOT_DONE => 'Link Foto Mentah Tersedia',
            Project::STATUS_EDITING => 'Permintaan Edit Dikirim',
            Project::STATUS_FINAL => 'Hasil Final Siap',
        ];

        foreach ($labels as $status => $label) {
            $project = new Project(['status' => $status]);
            $this->assertSame($label, $project->statusLabel());
        }
    }

    public function test_production_requires_full_payment_while_scheduling_allows_dp(): void
    {
        $project = new Project(['status' => Project::STATUS_SCHEDULED]);
        $project->setRelation('booking', new Booking(['status' => Booking::STATUS_DP_PAID]));

        $this->assertTrue($project->bookingAllowsScheduling());
        $this->assertFalse($project->bookingAllowsProduction());
        $this->assertSame(
            'Proses pasca-produksi hanya dapat dilanjutkan setelah pembayaran lunas.',
            $project->productionBlockMessage()
        );

        $project->setRelation('booking', new Booking(['status' => Booking::STATUS_PAID]));

        $this->assertTrue($project->bookingAllowsScheduling());
        $this->assertTrue($project->bookingAllowsProduction());
    }
}

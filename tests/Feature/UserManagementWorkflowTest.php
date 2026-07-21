<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\ServicePackage;
use App\Models\StudioLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /** Password akun internal wajib diisi dan minimal delapan karakter. */
    public function test_owner_must_provide_valid_password_when_creating_user(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);

        $this->actingAs($owner)
            ->post(route('admin.users.store'), [
                'name' => 'Crew Tanpa Password',
                'email' => 'crew-no-password@example.test',
                'role' => Role::EDITOR->value,
            ])
            ->assertSessionHasErrors(['password']);

        $this->actingAs($owner)
            ->post(route('admin.users.store'), [
                'name' => 'Crew Password Pendek',
                'email' => 'crew-short-password@example.test',
                'role' => Role::EDITOR->value,
                'password' => 'short',
            ])
            ->assertSessionHasErrors(['password']);

        $this->assertDatabaseMissing('users', ['email' => 'crew-no-password@example.test']);
        $this->assertDatabaseMissing('users', ['email' => 'crew-short-password@example.test']);
    }

    /**
     * Pengujian: Owner membuat pengguna internal dengan satu role.
     * Hasil yang diharapkan: akun tersimpan aktif dengan role utama yang sesuai.
     */
    public function test_owner_can_create_internal_user_with_single_role(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);

        $this->actingAs($owner)
            ->post(route('admin.users.store'), [
                'name' => 'Crew Dual',
                'email' => 'crew-dual@example.test',
                'role' => Role::PHOTOGRAPHER->value,
                'password' => 'secret123',
                'no_hp' => '08110000001',
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'crew-dual@example.test')->firstOrFail();

        $this->assertTrue(Hash::check('secret123', $user->password));
        $this->assertSame(Role::PHOTOGRAPHER, $user->role);
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasVerifiedEmail());
    }

    /**
     * Pengujian: Owner memperbarui pengguna tanpa mengisi password baru.
     * Hasil yang diharapkan: data pengguna berubah, tetapi password lama tetap dipertahankan.
     */
    public function test_owner_can_update_user_without_overwriting_password_when_blank(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $user = User::factory()->create([
            'role' => Role::EDITOR,
            'password' => Hash::make('old-password'),
        ]);
        $oldPassword = $user->password;

        $this->actingAs($owner)
            ->put(route('admin.users.update', $user), [
                'name' => 'Editor Updated',
                'email' => 'editor-updated@example.test',
                'role' => Role::EDITOR->value,
                'password' => '',
                'no_hp' => '08110000002',
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.users.index'));

        $user->refresh();

        $this->assertSame('Editor Updated', $user->name);
        $this->assertSame('editor-updated@example.test', $user->email);
        $this->assertSame($oldPassword, $user->password);
        $this->assertSame(Role::EDITOR, $user->role);
    }

    /**
     * Pengujian: perlindungan akun Owner dari nonaktif dan hapus.
     * Hasil yang diharapkan: akun Owner tetap aktif dan tidak terhapus, sedangkan Manajer tanpa pekerjaan aktif dapat dihapus.
     */
    public function test_owner_account_cannot_be_deactivated_or_deleted_but_manager_can_be_deleted(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $otherOwner = User::factory()->create(['role' => Role::OWNER, 'is_active' => true]);
        $manager = User::factory()->create(['role' => Role::MANAGER, 'is_active' => true]);

        $this->actingAs($owner)
            ->post(route('admin.users.toggle', $otherOwner), ['is_active' => false])
            ->assertSessionHas('error', 'Akun owner tidak boleh dinonaktifkan.');

        $this->assertTrue($otherOwner->fresh()->is_active);

        $this->actingAs($owner)
            ->delete(route('admin.users.destroy', $otherOwner))
            ->assertSessionHas('error', 'Akun owner tidak boleh dihapus.');

        $this->assertDatabaseHas('users', ['id' => $otherOwner->id]);

        $this->actingAs($owner)
            ->delete(route('admin.users.destroy', $manager))
            ->assertSessionHas('user_status', 'Pengguna dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $manager->id]);
    }

    /**
     * Pengujian: perlindungan akun yang masih memiliki booking atau project aktif.
     * Hasil yang diharapkan: akun tidak dapat dinonaktifkan atau dihapus selama pekerjaan belum selesai.
     */
    public function test_user_with_active_booking_or_project_cannot_be_deactivated_or_deleted(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang User Guard',
            'slug' => 'cabang-user-guard',
            'address' => 'Jl. Guard',
            'is_active' => true,
        ]);
        Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_PAID,
        ]);

        $this->actingAs($owner)
            ->post(route('admin.users.toggle', $client), ['is_active' => false])
            ->assertSessionHas('error', 'Akun tidak dapat dinonaktifkan karena masih memiliki pemesanan atau project yang belum selesai.');

        $this->assertTrue($client->fresh()->is_active);

        $this->actingAs($owner)
            ->delete(route('admin.users.destroy', $client))
            ->assertSessionHas('error', 'Akun tidak dapat dihapus karena masih memiliki pemesanan atau project yang belum selesai.');

        $this->assertDatabaseHas('users', ['id' => $client->id]);
    }

    /**
     * Pengujian: perubahan status dan penghapusan akun tanpa pekerjaan aktif.
     * Hasil yang diharapkan: akun dapat dinonaktifkan lalu dihapus dari database.
     */
    public function test_user_without_active_work_can_be_toggled_and_deleted(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $crew = User::factory()->create(['role' => Role::PHOTOGRAPHER, 'is_active' => true]);

        $this->actingAs($owner)
            ->post(route('admin.users.toggle', $crew), ['is_active' => false])
            ->assertSessionHas('user_status', 'Status pengguna diperbarui.');

        $this->assertFalse($crew->fresh()->is_active);

        $this->actingAs($owner)
            ->delete(route('admin.users.destroy', $crew))
            ->assertSessionHas('user_status', 'Pengguna dihapus.');

        $this->assertDatabaseMissing('users', ['id' => $crew->id]);
    }

    /**
     * Pengujian: penghapusan kru yang masih memiliki project aktif.
     * Hasil yang diharapkan: sistem menolak penghapusan dan akun kru tetap tersimpan.
     */
    public function test_crew_with_active_project_cannot_be_deleted(): void
    {
        $owner = User::factory()->create(['role' => Role::OWNER]);
        $photographer = User::factory()->create(['role' => Role::PHOTOGRAPHER]);
        $editor = User::factory()->create(['role' => Role::EDITOR]);
        $client = User::factory()->create(['role' => Role::CLIENT]);
        $package = ServicePackage::factory()->create();
        $location = StudioLocation::create([
            'name' => 'Cabang Project Guard',
            'slug' => 'cabang-project-guard',
            'address' => 'Jl. Project Guard',
            'is_active' => true,
        ]);
        $booking = Booking::factory()->create([
            'client_id' => $client->id,
            'package_id' => $package->id,
            'studio_location_id' => $location->id,
            'status' => Booking::STATUS_PAID,
        ]);
        Project::factory()->create([
            'booking_id' => $booking->id,
            'photographer_id' => $photographer->id,
            'editor_id' => $editor->id,
            'status' => Project::STATUS_SCHEDULED,
            'start_at' => now()->addDay()->setTime(11, 0),
            'end_at' => now()->addDay()->setTime(12, 0),
        ]);

        $this->actingAs($owner)
            ->delete(route('admin.users.destroy', $photographer))
            ->assertSessionHas('error', 'Akun tidak dapat dihapus karena masih memiliki pemesanan atau project yang belum selesai.');

        $this->assertDatabaseHas('users', ['id' => $photographer->id]);
    }
}

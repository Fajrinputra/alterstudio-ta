<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: halaman profil pengguna.
     * Hasil yang diharapkan: pengguna yang sudah login dapat membuka halaman profil.
     */
    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    /**
     * Pengujian: pembaruan informasi profil pengguna.
     * Hasil yang diharapkan: nama dan email berhasil diperbarui, serta email baru perlu diverifikasi ulang.
     */
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

    /**
     * Pengujian: status verifikasi email saat email tidak berubah.
     * Hasil yang diharapkan: status verifikasi tetap tersimpan karena alamat email sama.
     */
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

    /**
     * Pengujian: route hapus akun mandiri dari profil.
     * Hasil yang diharapkan: route tidak tersedia dan akun pengguna tidak terhapus.
     */
    public function test_profile_self_delete_route_is_removed(): void
    {
        $user = User::factory()->create();

        $this
            ->actingAs($user)
            ->delete('/profile')
            ->assertStatus(405);

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }
}

<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pengujian: tampilan halaman registrasi klien.
     * Hasil yang diharapkan: halaman registrasi dapat dirender dengan status berhasil.
     */
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    /**
     * Pengujian: proses registrasi pengguna baru.
     * Hasil yang diharapkan: akun baru berhasil dibuat, login otomatis, dan diarahkan ke dashboard.
     */
    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'no_hp' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'no_hp' => '081234567890',
        ]);
    }

    public function test_registration_rejects_invalid_phone_number(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'phone@example.com',
            'no_hp' => 'nomor-tidak-valid',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors('no_hp');

        $this->assertDatabaseMissing('users', ['email' => 'phone@example.com']);
    }
}

<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Http\Requests\Auth\LoginRequest;
use App\Notifications\InactiveClientAccountDeletedNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthAndProfileEdgeTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_changes_email_verification_and_replaces_avatar(): void
    {
        Storage::fake('public');
        $user = User::factory()->create([
            'role' => Role::CLIENT,
            'email_verified_at' => now(),
            'avatar_path' => 'avatars/old.jpg',
        ]);
        Storage::disk('public')->put('avatars/old.jpg', 'old');

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'Nama Baru',
                'email' => 'baru@example.test',
                'no_hp' => '08123456789',
                'avatar' => UploadedFile::fake()->image('new.jpg', 300, 300),
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('status', 'profile-updated');

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('baru@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
        Storage::disk('public')->assertMissing('avatars/old.jpg');
        Storage::disk('public')->assertExists($user->avatar_path);
    }

    public function test_email_verification_notification_redirects_verified_and_sends_for_unverified(): void
    {
        Notification::fake();
        $verified = User::factory()->create([
            'role' => Role::CLIENT,
            'email_verified_at' => now(),
        ]);
        $unverified = User::factory()->unverified()->create(['role' => Role::CLIENT]);

        $this->actingAs($verified)
            ->post(route('verification.send'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->actingAs($unverified)
            ->post(route('verification.send'))
            ->assertRedirect()
            ->assertSessionHas('status', 'verification-link-sent');
    }

    public function test_verification_prompt_redirects_when_user_already_verified(): void
    {
        $verified = User::factory()->create([
            'role' => Role::CLIENT,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($verified)
            ->get(route('verification.notice'))
            ->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_password_reset_link_failure_branch_is_reached(): void
    {
        Notification::fake();
        $user = User::factory()->create(['role' => Role::CLIENT, 'email' => 'reset-fail@example.test']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        $this->from(route('password.request'))
            ->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect(route('password.request'))
            ->assertSessionHasErrors('email');
    }

    public function test_login_request_rate_limit_and_throttle_key_are_covered(): void
    {
        Event::fake();
        RateLimiter::clear('user@example.test|127.0.0.1');
        RateLimiter::hit('user@example.test|127.0.0.1', 60);
        RateLimiter::hit('user@example.test|127.0.0.1', 60);
        RateLimiter::hit('user@example.test|127.0.0.1', 60);
        RateLimiter::hit('user@example.test|127.0.0.1', 60);
        RateLimiter::hit('user@example.test|127.0.0.1', 60);

        $request = LoginRequest::create('/login', 'POST', [
            'email' => 'USER@EXAMPLE.TEST',
            'password' => 'password',
        ], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $request->setLaravelSession(app('session')->driver());

        $this->assertSame('user@example.test|127.0.0.1', $request->throttleKey());
        $this->expectException(ValidationException::class);
        $request->ensureIsNotRateLimited();
    }

    public function test_inactive_client_notification_mail_contains_cutoff_date(): void
    {
        $user = User::factory()->create(['name' => 'Klien Lama']);
        $notification = new InactiveClientAccountDeletedNotification('2026-05-18');

        $this->assertSame(['mail'], $notification->via($user));
        $mail = $notification->toMail($user)->toArray();

        $this->assertSame('[Alter Studio] Akun dihapus karena tidak aktif', $mail['subject']);
        $this->assertStringContainsString('2026-05-18', implode(' ', $mail['introLines']));
    }
}

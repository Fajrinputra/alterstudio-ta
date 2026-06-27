<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class UseCaseScenarioA01A11Test extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_and_rules_are_available_to_guest_and_client(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('data-faq-open', false)
            ->assertSee('Panduan Pemesanan Alter Studio');

        $client = User::factory()->create(['role' => Role::CLIENT]);

        $this->actingAs($client)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('aria-label="Buka rules pemesanan"', false);
    }

    public function test_login_handles_unverified_invalid_and_inactive_accounts(): void
    {
        $unverified = User::factory()->unverified()->create([
            'role' => Role::CLIENT,
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login'), [
            'email' => $unverified->email,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));

        $this->post(route('logout'))->assertRedirect('/');

        $inactive = User::factory()->create([
            'is_active' => false,
            'password' => Hash::make('password'),
        ]);

        $this->post(route('login'), [
            'email' => $inactive->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->post(route('login'), [
            'email' => $inactive->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_registration_validates_input_and_creates_unverified_client(): void
    {
        User::factory()->create(['email' => 'registered@example.test']);

        $this->post(route('register'), [
            'name' => 'Invalid Client',
            'email' => 'registered@example.test',
            'no_hp' => '081234567890',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors(['email', 'password']);

        $response = $this->post(route('register'), [
            'name' => 'New Client',
            'email' => 'new-client@example.test',
            'no_hp' => '081298765432',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();

        $client = User::where('email', 'new-client@example.test')->firstOrFail();
        $this->assertSame(Role::CLIENT, $client->role);
        $this->assertSame('081298765432', $client->no_hp);
        $this->assertFalse($client->hasVerifiedEmail());

        $this->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_email_verification_rejects_expired_link_and_accepts_valid_link(): void
    {
        $user = User::factory()->unverified()->create(['role' => Role::CLIENT]);

        $expiredUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->subMinute(),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)->get($expiredUrl)->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());

        $validUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addHour(),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $this->actingAs($user)
            ->get($validUrl)
            ->assertRedirect(route('dashboard', absolute: false).'?verified=1');
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_forgot_password_validates_email_and_sends_link_for_registered_user(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'not-an-email'])
            ->assertSessionHasErrors('email');

        $this->post(route('password.email'), ['email' => 'missing@example.test'])
            ->assertSessionHasErrors('email');

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_forgot_password_reports_email_delivery_failure(): void
    {
        $user = User::factory()->create();

        $this->mock(Dispatcher::class, function ($mock): void {
            $mock->shouldReceive('send')
                ->once()
                ->andThrow(new \RuntimeException('Mail transport unavailable'));
        });

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('password_reset_tokens', ['user_id' => $user->id]);
    }

    public function test_reset_password_rejects_invalid_token_and_invalid_confirmation(): void
    {
        $user = User::factory()->create();

        $this->post(route('password.store'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('email');

        $this->post(route('password.store'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertSessionHasErrors('password');
    }

    public function test_profile_and_dashboard_are_available_to_every_verified_role(): void
    {
        foreach (Role::cases() as $role) {
            $user = User::factory()->create(['role' => $role]);

            $this->actingAs($user)
                ->get(route('dashboard'))
                ->assertOk()
                ->assertViewHas('role', $role);

            $this->actingAs($user)
                ->get(route('profile.edit'))
                ->assertOk();

            $this->actingAs($user)
                ->get(route('profile.password'))
                ->assertOk();
        }
    }
}

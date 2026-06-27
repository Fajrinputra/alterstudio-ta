<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Permintaan link reset password via email.
 */
class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
        ]);

        $user = User::where('email', $request->email)->firstOrFail();
        $tokenTable = config('auth.passwords.users.table', 'password_reset_tokens');
        $throttleSeconds = (int) config('auth.passwords.users.throttle', 60);

        $recentTokenExists = DB::table($tokenTable)
            ->where('user_id', $user->id)
            ->where('created_at', '>=', now()->subSeconds($throttleSeconds))
            ->exists();

        if ($recentTokenExists) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => __(Password::RESET_THROTTLED)]);
        }

        $token = Str::random(64);

        DB::transaction(function () use ($tokenTable, $user, $token): void {
            DB::table($tokenTable)
                ->where('user_id', $user->id)
                ->orWhere('email', $user->email)
                ->delete();

            DB::table($tokenTable)->insert([
                'user_id' => $user->id,
                'email' => $user->email,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);
        });

        try {
            $user->sendPasswordResetNotification($token);
        } catch (\Throwable $exception) {
            DB::table($tokenTable)->where('user_id', $user->id)->delete();
            Log::error('Password reset email could not be sent.', [
                'user_id' => $user->id,
                'exception' => $exception->getMessage(),
            ]);

            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Tautan reset password gagal dikirim. Silakan coba lagi.']);
        }

        $status = Password::RESET_LINK_SENT;

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}

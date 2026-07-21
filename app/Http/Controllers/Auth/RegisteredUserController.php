<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

/**
 * Registrasi akun baru (default role: CLIENT).
 */
class RegisteredUserController extends Controller
{
    /** Menampilkan halaman registrasi akun. */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Menangani proses registrasi akun baru.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi data dasar registrasi.
        $request->validate([
            'name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:50', 'unique:'.User::class],
            'no_hp' => ['required', 'string', 'max:20', 'regex:/^(?:\+62|62|0)[0-9]{9,13}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'no_hp.required' => 'Nomor HP wajib diisi.',
            'no_hp.regex' => 'Nomor HP harus menggunakan format Indonesia, misalnya 081234567890 atau +6281234567890.',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'password' => Hash::make($request->password),
            'role' => Role::CLIENT->value,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}

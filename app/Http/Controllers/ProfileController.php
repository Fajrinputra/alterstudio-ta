<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

/**
 * Menangani tampilan dan perubahan profil user.
 */
class ProfileController extends Controller
{
    /** Tampilkan ringkasan profil (hanya baca). */
    public function show(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'readOnly' => true,
        ]);
    }

    /** Tampilkan form edit profil. */
    public function edit(Request $request): View
    {
        return view('profile.form', [
            'user' => $request->user(),
            'readOnly' => false,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $user->fill($data);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->storePublicly('avatars', 'public');
            if ($user->avatar_path) {
                \Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $path;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

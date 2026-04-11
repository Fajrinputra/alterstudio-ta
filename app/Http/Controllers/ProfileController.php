<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Booking;
use App\Models\Project;
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

        if ($user->role === \App\Enums\Role::MANAGER) {
            return Redirect::route('profile.edit')
                ->withErrors(['account' => 'Akun manajer tidak boleh dihapus.'], 'userDeletion');
        }

        if ($this->hasActiveOperationalWork($user->id)) {
            return Redirect::route('profile.edit')
                ->withErrors(['account' => 'Akun tidak dapat dihapus karena masih memiliki booking atau project yang belum selesai.'], 'userDeletion');
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    protected function hasActiveOperationalWork(int $userId): bool
    {
        $hasActiveClientBookings = Booking::query()
            ->where('client_id', $userId)
            ->where('status', '!=', Booking::STATUS_CANCELLED)
            ->where(function ($query) {
                $query->whereDoesntHave('project')
                    ->orWhereHas('project', fn ($project) => $project->where('status', '!=', Project::STATUS_FINAL));
            })
            ->exists();

        if ($hasActiveClientBookings) {
            return true;
        }

        return Project::query()
            ->where(function ($query) use ($userId) {
                $query->where('photographer_id', $userId)
                    ->orWhere('editor_id', $userId);
            })
            ->whereHas('booking', fn ($booking) => $booking->where('status', '!=', Booking::STATUS_CANCELLED))
            ->where('status', '!=', Project::STATUS_FINAL)
            ->exists();
    }
}

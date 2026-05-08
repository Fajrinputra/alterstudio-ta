<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Booking;
use App\Models\LandingHeroSlide;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    /** Menyimpan perubahan data profil pengguna. */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $user->fill($data);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->storePublicly('avatars', 'public');
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $path;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /** Menghapus akun pengguna jika tidak masih terlibat pada proses aktif. */
    public function destroy(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->role === Role::MANAGER) {
            return Redirect::route('profile.edit')
                ->withErrors(['account' => 'Akun manajer tidak boleh dihapus.'], 'userDeletion');
        }

        if ($this->hasActiveOperationalWork($user->id)) {
            return Redirect::route('profile.edit')
                ->withErrors(['account' => 'Akun tidak dapat dihapus karena masih memiliki pemesanan atau project yang belum selesai.'], 'userDeletion');
        }

        $hasPersistentReferences = $this->hasPersistentReferences($user);

        Auth::logout();

        if ($hasPersistentReferences) {
            $this->anonymizeUser($user);
        } else {
            $this->deleteUserPermanently($user);
        }

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

    protected function hasPersistentReferences(User $user): bool
    {
        return $user->bookings()->exists()
            || $user->photoSelections()->exists()
            || $user->uploadedMediaAssets()->exists()
            || Project::query()
                ->where(function ($query) use ($user) {
                    $query->where('photographer_id', $user->id)
                        ->orWhere('editor_id', $user->id)
                        ->orWhere('raw_drive_uploaded_by', $user->id)
                        ->orWhere('final_drive_uploaded_by', $user->id);
                })
                ->exists()
            || LandingHeroSlide::query()
                ->where('created_by', $user->id)
                ->orWhere('updated_by', $user->id)
                ->exists();
    }

    protected function anonymizeUser(User $user): void
    {
        $this->deleteResetTokensAndAvatar($user);

        $user->forceFill([
            'name' => 'Akun Dihapus',
            'email' => 'deleted-user-'.$user->id.'-'.Str::lower(Str::random(8)).'@alter.local',
            'no_hp' => null,
            'avatar_path' => null,
            'password' => Hash::make(Str::random(40)),
            'remember_token' => null,
            'email_verified_at' => null,
            'is_active' => false,
        ])->save();
    }

    protected function deleteUserPermanently(User $user): void
    {
        $this->deleteResetTokensAndAvatar($user);
        $user->delete();
    }

    protected function deleteResetTokensAndAvatar(User $user): void
    {
        DB::table('password_reset_tokens')
            ->where('email', $user->email)
            ->orWhere('user_id', $user->id)
            ->delete();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
    }
}

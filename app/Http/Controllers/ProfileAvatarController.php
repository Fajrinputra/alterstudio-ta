<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Upload/hapus avatar profil user.
 */
class ProfileAvatarController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'avatar' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('avatar')) {
            $path = $request->file('avatar')->storePublicly('avatars', 'public');
            $user = $request->user();
            // Delete old avatar if exists
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->avatar_path = $path;
            $user->save();
        }

        return back()->with('status', 'avatar-updated');
    }

    /** Hapus avatar. */
    public function destroy(Request $request)
    {
        $user = $request->user();
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();
        }
        return back()->with('status', 'avatar-updated');
    }
}

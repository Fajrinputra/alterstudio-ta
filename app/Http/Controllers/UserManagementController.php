<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

/**
 * CRUD user internal (admin/manager) + aktivasi/nonaktivasi akun.
 */
class UserManagementController extends Controller
{
    /** Halaman kelola pengguna (admin & manager). */
    public function index()
    {
        $users = User::orderBy('role')->orderBy('name')->paginate(12);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /** Halaman form create. */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /** Halaman edit. */
    public function edit(User $user)
    {
        $roles = Role::all();
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /** Tambah pengguna baru. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(Role::all())],
            'password' => ['nullable', 'string', 'min:8'],
            'no_hp' => ['nullable', 'string', 'max:30'],
        ]);

        $password = $data['password'] ?? 'password';

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'no_hp' => $data['no_hp'] ?? null,
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        return redirect()->route('admin.users.index')->with('user_status', 'Pengguna ditambahkan.');
    }

    /** Update data pengguna. */
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(Role::all())],
            'password' => ['nullable', 'string', 'min:8'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'no_hp' => $data['no_hp'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        // Lindungi akun Manager: tidak boleh dinonaktifkan atau dihapus.
        if ($user->role !== Role::MANAGER && array_key_exists('is_active', $data)) {
            $payload['is_active'] = (bool) $data['is_active'];
        }

        $user->update($payload);

        $redirect = $request->user()->id === $user->id
            ? route('profile.edit')
            : route('admin.users.index');

        return redirect($redirect)->with('user_status', 'Pengguna diperbarui.');
    }

    /** Nonaktif/aktifkan pengguna. */
    public function toggle(User $user)
    {
        if ($user->role === Role::MANAGER) {
            return back()->with('status', 'Akun manajer tidak boleh dinonaktifkan.');
        }

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('user_status', 'Status pengguna diperbarui.');
    }

    /** Hapus akun. */
    public function destroy(User $user)
    {
        if ($user->role === Role::MANAGER) {
            return back()->with('status', 'Akun manajer tidak boleh dihapus.');
        }

        $user->delete();

        return back()->with('user_status', 'Pengguna dihapus.');
    }
}

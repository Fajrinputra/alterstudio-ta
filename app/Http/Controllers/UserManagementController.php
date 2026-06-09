<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Booking;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

/**
 * CRUD user internal oleh owner + aktivasi/nonaktivasi akun.
 */
class UserManagementController extends Controller
{
    /** Halaman kelola pengguna. */
    public function index(Request $request)
    {
        $roleFilter = $request->query('role_filter');

        $users = User::query()
            ->when($roleFilter === 'photographer', fn ($query) => $query->withRole(Role::PHOTOGRAPHER))
            ->when($roleFilter === 'editor', fn ($query) => $query->withRole(Role::EDITOR))
            ->orderBy('role')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles', 'roleFilter'));
    }

    /** Halaman form create. */
    public function create()
    {
        $roles = $this->assignableRoles();
        return view('admin.users.create', compact('roles'));
    }

    /** Halaman edit. */
    public function edit(User $user)
    {
        $roles = $this->assignableRoles($user);
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /** Tambah pengguna baru. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in($this->assignableRoles())],
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
            'role' => ['required', Rule::in($this->assignableRoles($user))],
            'password' => ['nullable', 'string', 'min:8'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $user->role === Role::OWNER ? Role::OWNER->value : $data['role'],
            'no_hp' => $data['no_hp'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        // Lindungi akun owner: tidak boleh dinonaktifkan atau diturunkan rolenya.
        if ($user->role === Role::OWNER) {
            $payload['role'] = Role::OWNER->value;
            $payload['is_active'] = true;
        } elseif (array_key_exists('is_active', $data)) {
            if ((bool) $data['is_active'] === false && $this->hasActiveOperationalWork($user)) {
                return back()->with('status', 'Akun tidak dapat dinonaktifkan karena masih memiliki pemesanan atau project yang belum selesai.');
            }

            $payload['is_active'] = (bool) $data['is_active'];
        }

        $user->update($payload);

        $redirect = $request->user()->id === $user->id
            ? route('profile.edit')
            : route('admin.users.index');

        return redirect($redirect)->with('user_status', 'Pengguna diperbarui.');
    }

    /** Nonaktif/aktifkan pengguna. */
    public function toggle(Request $request, User $user)
    {
        if ($user->role === Role::OWNER) {
            return back()->with('status', 'Akun owner tidak boleh dinonaktifkan.');
        }

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        if ((bool) $data['is_active'] === false && $this->hasActiveOperationalWork($user)) {
            return back()->with('status', 'Akun tidak dapat dinonaktifkan karena masih memiliki pemesanan atau project yang belum selesai.');
        }

        $user->update(['is_active' => (bool) $data['is_active']]);

        return back()->with('user_status', 'Status pengguna diperbarui.');
    }

    /** Hapus akun. */
    public function destroy(User $user)
    {
        if ($user->role === Role::OWNER) {
            return back()->with('status', 'Akun owner tidak boleh dihapus.');
        }

        if ($this->hasActiveOperationalWork($user)) {
            return back()->with('status', 'Akun tidak dapat dihapus karena masih memiliki pemesanan atau project yang belum selesai.');
        }

        $user->delete();

        return back()->with('user_status', 'Pengguna dihapus.');
    }

    /**
     * Role owner tidak dibuat dari form biasa. Akun owner adalah akun inti sistem.
     *
     * @return array<int, string>
     */
    protected function assignableRoles(?User $target = null): array
    {
        if ($target?->role === Role::OWNER) {
            return Role::all();
        }

        return array_values(array_filter(
            Role::all(),
            fn (string $role) => $role !== Role::OWNER->value
        ));
    }

    protected function hasActiveOperationalWork(User $user): bool
    {
        $hasActiveClientBookings = $user->bookings()
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
            ->whereHas('scheduleRecord.users', fn ($query) => $query->where('user_id', $user->id))
            ->whereHas('booking', fn ($booking) => $booking->where('status', '!=', Booking::STATUS_CANCELLED))
            ->where('status', '!=', Project::STATUS_FINAL)
            ->exists();
    }
}

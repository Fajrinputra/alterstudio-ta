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
 * CRUD user internal (admin/manager) + aktivasi/nonaktivasi akun.
 */
class UserManagementController extends Controller
{
    /** Halaman kelola pengguna (admin & manager). */
    public function index(Request $request)
    {
        $roleFilter = $request->query('role_filter');

        $users = User::query()
            ->when($roleFilter === 'photographer', fn ($query) => $query->withRole(Role::PHOTOGRAPHER))
            ->when($roleFilter === 'editor', fn ($query) => $query->withRole(Role::EDITOR))
            ->when($roleFilter === 'dual_crew', function ($query) {
                $query->withRole(Role::PHOTOGRAPHER)
                    ->withRole(Role::EDITOR);
            })
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
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in([Role::PHOTOGRAPHER->value, Role::EDITOR->value])],
            'password' => ['nullable', 'string', 'min:8'],
            'no_hp' => ['nullable', 'string', 'max:30'],
        ]);

        $password = $data['password'] ?? 'password';
        $roles = $this->normalizeRoles($data['role'], $data['roles'] ?? []);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'roles' => $roles,
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
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', Rule::in([Role::PHOTOGRAPHER->value, Role::EDITOR->value])],
            'password' => ['nullable', 'string', 'min:8'],
            'no_hp' => ['nullable', 'string', 'max:30'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'roles' => $this->normalizeRoles($data['role'], $data['roles'] ?? []),
            'no_hp' => $data['no_hp'] ?? null,
        ];

        if (!empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        // Lindungi akun Manager: tidak boleh dinonaktifkan atau dihapus.
        if ($user->role !== Role::MANAGER && array_key_exists('is_active', $data)) {
            if ((bool) $data['is_active'] === false && $this->hasActiveOperationalWork($user)) {
                return back()->with('status', 'Akun tidak dapat dinonaktifkan karena masih memiliki booking atau project yang belum selesai.');
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
        if ($user->role === Role::MANAGER) {
            return back()->with('status', 'Akun manajer tidak boleh dinonaktifkan.');
        }

        $data = $request->validate([
            'is_active' => ['required', 'boolean'],
        ]);

        if ((bool) $data['is_active'] === false && $this->hasActiveOperationalWork($user)) {
            return back()->with('status', 'Akun tidak dapat dinonaktifkan karena masih memiliki booking atau project yang belum selesai.');
        }

        $user->update(['is_active' => (bool) $data['is_active']]);

        return back()->with('user_status', 'Status pengguna diperbarui.');
    }

    /** Hapus akun. */
    public function destroy(User $user)
    {
        if ($user->role === Role::MANAGER) {
            return back()->with('status', 'Akun manajer tidak boleh dihapus.');
        }

        if ($this->hasActiveOperationalWork($user)) {
            return back()->with('status', 'Akun tidak dapat dihapus karena masih memiliki booking atau project yang belum selesai.');
        }

        $user->delete();

        return back()->with('user_status', 'Pengguna dihapus.');
    }

    /**
     * Role utama tetap satu, tetapi akun kru boleh punya akses ganda fotografer+editor.
     *
     * @param array<int, string> $roles
     * @return array<int, string>
     */
    protected function normalizeRoles(string $primaryRole, array $roles): array
    {
        $crewRoles = [Role::PHOTOGRAPHER->value, Role::EDITOR->value];

        if (! in_array($primaryRole, $crewRoles, true)) {
            return [$primaryRole];
        }

        $normalized = array_values(array_unique(array_filter(array_merge([$primaryRole], $roles))));

        return array_values(array_intersect($normalized, $crewRoles));
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
            ->where(function ($query) use ($user) {
                $query->where('photographer_id', $user->id)
                    ->orWhere('editor_id', $user->id);
            })
            ->whereHas('booking', fn ($booking) => $booking->where('status', '!=', Booking::STATUS_CANCELLED))
            ->where('status', '!=', Project::STATUS_FINAL)
            ->exists();
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\Role;

/**
 * Entitas user untuk seluruh role: admin, manager, client, fotografer, editor.
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'roles',
        'is_active',
        'avatar_path',
        'no_hp',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
            'roles' => 'array',
            'is_active' => 'boolean',
            'avatar_path' => 'string',
        ];
    }

    /**
     * Check if user has one of the given roles.
     *
     * @param array<int, string|\App\Enums\Role> $roles
     */
    public function isRole(string|\App\Enums\Role ...$roles): bool
    {
        $target = array_map(function ($role) {
            return $role instanceof Role ? $role->value : $role;
        }, $roles);

        return count(array_intersect($this->effectiveRoles(), $target)) > 0;
    }

    /**
     * Seluruh akses role efektif user (role utama + akses tambahan).
     *
     * @return array<int, string>
     */
    public function effectiveRoles(): array
    {
        $current = $this->role instanceof Role ? $this->role->value : (string) $this->role;
        $extra = is_array($this->roles) ? $this->roles : [];

        return array_values(array_unique(array_filter(array_merge([$current], $extra))));
    }

    public function hasBothCrewRoles(): bool
    {
        return $this->isRole(Role::PHOTOGRAPHER) && $this->isRole(Role::EDITOR);
    }

    public function scopeWithRole($query, string|Role $role)
    {
        $roleValue = $role instanceof Role ? $role->value : $role;

        return $query->where(function ($inner) use ($roleValue) {
            $inner->where('role', $roleValue)
                ->orWhereJsonContains('roles', $roleValue);
        });
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    /** Asset media yang diunggah user (editor/fotografer). */
    public function uploadedMediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class, 'uploaded_by');
    }

    public function photoSelections(): HasMany
    {
        return $this->hasMany(PhotoSelection::class, 'client_id');
    }

    public function schedulesAsPhotographer(): HasMany
    {
        return $this->hasMany(Project::class, 'photographer_id');
    }

    public function schedulesAsEditor(): HasMany
    {
        return $this->hasMany(Project::class, 'editor_id');
    }
}

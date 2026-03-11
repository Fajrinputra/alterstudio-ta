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

        $current = $this->role instanceof Role ? $this->role->value : $this->role;

        return in_array($current, $target, true);
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

    public function revisionPinsCreated(): HasMany
    {
        return $this->hasMany(RevisionPin::class, 'client_id');
    }

    public function revisionPinsResolved(): HasMany
    {
        return $this->hasMany(RevisionPin::class, 'resolved_by');
    }

    public function schedulesAsPhotographer(): HasMany
    {
        return $this->hasMany(Schedule::class, 'photographer_id');
    }

    public function schedulesAsEditor(): HasMany
    {
        return $this->hasMany(Schedule::class, 'editor_id');
    }
}

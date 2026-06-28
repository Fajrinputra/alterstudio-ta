<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Enums\Role;

/**
 * Entitas user untuk seluruh role: admin, manager, client, fotografer, editor.
 */
class User extends Authenticatable implements MustVerifyEmail
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
     * Kirim email reset kata sandi dengan template bahasa Indonesia.
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Kirim email verifikasi akun dengan template bahasa Indonesia.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Check if user has one of the given roles.
     *
     * @param array<int, string|\App\Enums\Role> $roles
     */
    public function isRole(string|\App\Enums\Role ...$roles): bool
    {
        $current = $this->role instanceof Role ? $this->role->value : (string) $this->role;
        $target = array_map(function ($role) {
            return $role instanceof Role ? $role->value : $role;
        }, $roles);

        return in_array($current, $target, true);
    }

    public function scopeWithRole($query, string|Role $role)
    {
        $roleValue = $role instanceof Role ? $role->value : $role;

        return $query->where('role', $roleValue);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    /** Metadata file lama yang pernah diunggah user. */
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
        return $this->hasMany(ProjectScheduleUser::class, 'user_id')
            ->where('role', Role::PHOTOGRAPHER->value);
    }

    public function schedulesAsEditor(): HasMany
    {
        return $this->hasMany(ProjectScheduleUser::class, 'user_id')
            ->where('role', Role::EDITOR->value);
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
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
            'password'          => 'hashed',
        ];
    }

    // ─── Role Helper Methods ─────────────────────────────────────────

    /**
     * Cek apakah user memiliki salah satu dari role yang diberikan.
     * Contoh: $user->hasRole('admin', 'super_admin')
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Super admin punya akses ke semua fitur admin juga.
     */
    public function canManageOrders(): bool
    {
        return $this->hasRole('admin', 'super_admin');
    }

    public function canManageUsers(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canViewReports(): bool
    {
        return $this->hasRole('owner', 'super_admin', 'admin');
    }

    /**
     * Kembalikan label tampilan untuk role.
     */
    public function roleLabel(): string
    {
        return match ($this->role) {
            'super_admin' => 'Super Admin',
            'admin'       => 'Admin',
            'owner'       => 'Owner',
            default       => 'Pengguna',
        };
    }

    /**
     * Kembalikan warna badge CSS untuk role.
     */
    public function roleBadgeClass(): string
    {
        return match ($this->role) {
            'super_admin' => 'role-badge-superadmin',
            'admin'       => 'role-badge-admin',
            'owner'       => 'role-badge-owner',
            default       => 'role-badge-default',
        };
    }
}

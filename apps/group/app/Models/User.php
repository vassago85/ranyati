<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_DEVELOPER = 'developer';
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';

    const ROLES = [
        self::ROLE_DEVELOPER => 'Developer',
        self::ROLE_OWNER => 'Owner',
        self::ROLE_ADMIN => 'Admin',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function isDeveloper(): bool
    {
        return $this->role === self::ROLE_DEVELOPER;
    }

    public function isOwner(): bool
    {
        return $this->role === self::ROLE_OWNER;
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, [self::ROLE_DEVELOPER, self::ROLE_OWNER, self::ROLE_ADMIN]);
    }

    public function getRoleLabelAttribute(): string
    {
        return self::ROLES[$this->role] ?? ucfirst($this->role);
    }

    const ROLE_RANK = [
        self::ROLE_DEVELOPER => 3,
        self::ROLE_OWNER => 2,
        self::ROLE_ADMIN => 1,
    ];

    public function roleRank(): int
    {
        return self::ROLE_RANK[$this->role] ?? 0;
    }

    public function canManageUsers(): bool
    {
        return $this->roleRank() >= 2;
    }

    public function canManage(User $target): bool
    {
        return $this->roleRank() > $target->roleRank();
    }

    public function assignableRoles(): array
    {
        return match ($this->role) {
            self::ROLE_DEVELOPER => [self::ROLE_DEVELOPER, self::ROLE_OWNER, self::ROLE_ADMIN],
            self::ROLE_OWNER => [self::ROLE_OWNER, self::ROLE_ADMIN],
            default => [],
        };
    }
}

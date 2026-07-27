<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Support\AdminAreas;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    const ROLE_DEVELOPER = 'developer';
    const ROLE_OWNER = 'owner';
    const ROLE_ADMIN = 'admin';
    const ROLE_CLIENT = 'client';

    const ROLES = [
        self::ROLE_DEVELOPER => 'Developer',
        self::ROLE_OWNER => 'Owner',
        self::ROLE_ADMIN => 'Admin',
        self::ROLE_CLIENT => 'Member',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'default_admin_area',
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

    public function isClient(): bool
    {
        return $this->role === self::ROLE_CLIENT;
    }

    /**
     * The admin area this user has chosen to land in after signing in, or
     * null when they should be shown the chooser. A preference pointing at
     * an area whose routes are no longer registered resolves to null rather
     * than stranding the user on a dead route.
     *
     * @return array{key: string, label: string, tagline: string, route: string, accent: string, icon: string}|null
     */
    public function defaultAdminArea(): ?array
    {
        return AdminAreas::find($this->default_admin_area);
    }

    /**
     * Where to send this user immediately after authenticating: straight into
     * their saved area, or the chooser when they have not picked one.
     */
    public function adminLandingUrl(): string
    {
        $area = $this->defaultAdminArea();

        return $area ? route($area['route']) : route('admin.choose');
    }

    public function firearmApplications(): HasMany
    {
        return $this->hasMany(FirearmApplication::class);
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

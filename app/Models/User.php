<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'password', 'role'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_ADMIN = 'admin';
    public const ROLE_ADMIN_PMB = 'admin_pmb';

    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasAnyRole(array_keys(self::roleOptions())) || in_array($this->role, array_keys(self::roleOptions()), true);
    }

    public static function roleOptions(): array
    {
        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_ADMIN_PMB => 'Admin PMB',
        ];
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(self::ROLE_SUPER_ADMIN) || $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN) || $this->role === self::ROLE_ADMIN;
    }

    public function isAdminPmb(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN_PMB) || $this->role === self::ROLE_ADMIN_PMB;
    }

    public function canManageAccounts(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canManageContact(): bool
    {
        return $this->isSuperAdmin() || $this->isAdminPmb();
    }

    public function canManageContent(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

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
        ];
    }
}

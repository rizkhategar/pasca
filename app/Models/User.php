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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
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
        return $this->roles()->exists() || filled($this->role);
    }

    public static function roleOptions(): array
    {
        if (Schema::hasTable('roles')) {
            $roles = Role::query()
                ->orderBy('name')
                ->pluck('name')
                ->mapWithKeys(fn (string $name): array => [$name => Str::headline($name)])
                ->all();

            if (! empty($roles)) {
                return $roles;
            }
        }

        return [
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_ADMIN => 'Admin',
            self::ROLE_ADMIN_PMB => 'Admin PMB',
        ];
    }

    public function primaryRoleName(): ?string
    {
        return $this->getRoleNames()->first() ?: $this->role;
    }

    public function isSuperAdmin(): bool
    {
        $superAdminRole = config('filament-shield.super_admin.name', self::ROLE_SUPER_ADMIN);

        return $this->hasRole($superAdminRole) || $this->role === $superAdminRole;
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
        return $this->can('ViewAny:User');
    }

    public function canManageContact(): bool
    {
        return $this->can('ViewAny:Contact');
    }

    public function canManageContent(): bool
    {
        return $this->can('ViewAny:Slider')
            || $this->can('ViewAny:AboutPostgraduate')
            || $this->can('ViewAny:VisionMission')
            || $this->can('ViewAny:OrganizationalStructure');
    }

    public function canImpersonate(): bool
    {
        return $this->isSuperAdmin();
    }

    public function canBeImpersonated(): bool
    {
        return ! $this->isSuperAdmin() && auth()->id() !== $this->getKey();
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

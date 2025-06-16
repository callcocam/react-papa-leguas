<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Models\Auth;

use Callcocam\ReactPapaLeguas\Models\AbstractModel;
use Callcocam\ReactPapaLeguas\Shinobi\Concerns\HasRolesAndPermissions;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract; 
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Callcocam\ReactPapaLeguas\Models\Tenant;
use Callcocam\ReactPapaLeguas\Enums\UserStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends AbstractModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, HasRolesAndPermissions, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_super_admin',
        'cover',
        'slug',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
            'is_super_admin' => 'boolean',
            'status' => UserStatus::class,
        ];
    }

    /**
     * Relationship with tenants that this admin can manage
     */
    public function manageableTenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'admin_tenant', 'admin_id', 'tenant_id')
                    ->withTimestamps();
    }

    /**
     * Check if admin is super admin (can manage all tenants)
     */
    public function isSuperAdmin(): bool
    {
        return $this->is_super_admin ?? false;
    }

    /**
     * Check if admin can manage specific tenant
     */
    public function canManageTenant(Tenant $tenant): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->manageableTenants()->where('tenant_id', $tenant->id)->exists();
    }

    /**
     * Check if admin account is active
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::Published;
    }
} 
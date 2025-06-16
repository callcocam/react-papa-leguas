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
use Callcocam\ReactPapaLeguas\Enums\UserStatus;
use Callcocam\ReactPapaLeguas\Models\Traits\HasTenant;

class User extends AbstractModel implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail, HasRolesAndPermissions, HasTenant;

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
            'status' => UserStatus::class,
        ];
    }

    /**
     * Check if user account is active
     */
    public function isActive(): bool
    {
        return $this->status === UserStatus::Published;
    }
}

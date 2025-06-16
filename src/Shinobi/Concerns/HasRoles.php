<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Shinobi\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Callcocam\ReactPapaLeguas\Shinobi\Contracts\Role;
use Callcocam\ReactPapaLeguas\Models\Role as ModelsRole;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasRoles
{
    /**
     * Users can have many roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('react-papa-leguas.models.role', ModelsRole::class))->withTimestamps();
    }

    /**
     * Checks if the model has the given role assigned.
     * 
     * @param  string|Role  $role
     * @return boolean
     */
    public function hasRole($role): bool
    {
        $slug = $role instanceof Role ? $role->slug : Str::slug($role);

        // Load roles if not loaded to avoid N+1
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles->where('slug', $slug)->isNotEmpty();
    }

    /**
     * Checks if the model has any of the given roles assigned.
     * 
     * @param  array  $roles
     * @return bool
     */
    public function hasAnyRole(...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the model has all of the given roles assigned.
     * 
     * @param  array  $roles
     * @return bool
     */
    public function hasAllRoles(...$roles): bool
    {
        foreach ($roles as $role) {
            if (! $this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks if the model has roles assigned.
     * 
     * @return bool
     */
    public function hasRoles(): bool
    {
        // Load roles if not loaded to avoid N+1
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles->isNotEmpty();
    }

    /**
     * Assign the specified roles to the model.
     * 
     * @param  mixed  $roles,...
     * @return self
     */
    public function assignRoles(...$roles): self
    {
        $roles = Arr::flatten($roles);
        $roles = $this->getRoles($roles);

        if (! $roles) {
            return $this;
        }

        $this->roles()->syncWithoutDetaching($roles);

        return $this;
    }

    /**
     * Remove the specified roles from the model.
     * 
     * @param  mixed  $roles,...
     * @return self
     */
    public function removeRoles(...$roles): self
    {
        $roles = Arr::flatten($roles);
        $roles = $this->getRoles($roles);

        $this->roles()->detach($roles);

        return $this;
    }

    /**
     * Sync the specified roles to the model.
     * 
     * @param  mixed  $roles,...
     * @return self
     */
    public function syncRoles(...$roles): self
    {
        $roles = Arr::flatten($roles);
        $roles = $this->getRoles($roles);

        $this->roles()->sync($roles);

        return $this;
    }

    /**
     * Get the specified roles.
     * 
     * @param  array  $roles
     * @return array
     */
    protected function getRoles(array $roles): array
    {
        if (empty($roles)) {
            return [];
        }
        
        // Separate already resolved IDs from slugs/instances
        $ids = [];
        $slugsToResolve = [];
        
        foreach ($roles as $role) {
            if ($role instanceof Role) {
                $ids[] = $role->id;
            } elseif (is_string($role)) {
                $slugsToResolve[] = Str::slug($role);
            } elseif (is_numeric($role)) {
                $ids[] = $role;
            }
        }
        
        // Resolve all slugs in one query if any
        if (!empty($slugsToResolve)) {
            $resolvedRoles = $this->getRoleModel()
                ->whereIn('slug', $slugsToResolve)
                ->pluck('id')
                ->toArray();
            $ids = array_merge($ids, $resolvedRoles);
        }
        
        return array_unique($ids);
    }

    /**
     * Check if user has permission role flags.
     * 
     * @return bool
     */
    public function hasPermissionRoleFlags(): bool
    {
        if (!$this->hasRoles()) {
            return false;
        }

        // Load roles if not loaded to avoid N+1
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        return $this->roles->where('special', '!=', null)->isNotEmpty();
    }

    /**
     * Check if user has permission through role flag.
     * 
     * @return bool
     */
    public function hasPermissionThroughRoleFlag(): bool
    {
        if (!$this->hasRoles()) {
            return false;
        }

        // Load roles if not loaded to avoid N+1
        if (!$this->relationLoaded('roles')) {
            $this->load('roles');
        }

        // If any role has 'all-access' or similar, grant permission
        return $this->roles
            ->where('special', '!=', null)
            ->where('special', '!=', 'no-access')
            ->isNotEmpty();
    }

    /**
     * Check if user has permission through role.
     * 
     * @param string $permissionSlug
     * @return bool
     */
    public function hasPermissionThroughRole(string $permissionSlug): bool
    {
        if (!$this->hasRoles()) {
            return false;
        }

        // Load roles with permissions if not loaded to avoid N+1
        if (!$this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->where('slug', $permissionSlug)->isNotEmpty()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the model instance responsible for permissions.
     * 
     * @return \Callcocam\ReactPapaLeguas\Shinobi\Contracts\Role
     */
    protected function getRoleModel(): Role
    {
        return app()->make(config('react-papa-leguas.models.role', ModelsRole::class));
    }
}

<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Shinobi\Concerns;

use Illuminate\Support\Arr;
use Callcocam\ReactPapaLeguas\Shinobi\Facades\Shinobi;
use Callcocam\ReactPapaLeguas\Shinobi\Contracts\Permission;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Callcocam\ReactPapaLeguas\Shinobi\Exceptions\PermissionNotFoundException;
use Callcocam\ReactPapaLeguas\Models\Permission as ModelsPermission;

trait HasPermissions
{
    /**
     * Users can have many permissions
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(config('shinobi.models.permission', ModelsPermission::class))->withTimestamps();
    }

    /**
     * The mothergoose check. Runs through each scenario provided
     * by Shinobi - checking for special flags, role permissions, and
     * individual user permissions; in that order.
     * 
     * @param  Permission|String  $permission
     * @return boolean
     */
    public function hasPermissionTo($permission): bool
    {
        // Check role flags first (fastest check)
        if ((method_exists($this, 'hasPermissionRoleFlags') and $this->hasPermissionRoleFlags())) {
            return $this->hasPermissionThroughRoleFlag();
        }
        if ((method_exists($this, 'hasPermissionFlags') and $this->hasPermissionFlags())) {
            return $this->hasPermissionThroughFlag();
        }
        
        // Convert string to permission slug if needed
        $permissionSlug = $permission;
        if ($permission instanceof Permission) {
            $permissionSlug = $permission->slug;
        }
        
        // Load all user permissions once and check in memory (avoid N+1)
        if (!$this->relationLoaded('permissions')) {
            $this->load('permissions');
        }
        
        // Check user permission first (direct permission)
        if ($this->permissions->where('slug', $permissionSlug)->isNotEmpty()) {
            return true;
        }
        
        // Check role permissions (load roles with permissions if not loaded)
        if (method_exists($this, 'hasPermissionThroughRole')) {
            if (!$this->relationLoaded('roles')) {
                $this->load('roles.permissions');
            }
            
            return $this->hasPermissionThroughRole($permissionSlug);
        }

        return false;
    }
    
    /**
     * Check multiple permissions at once (optimized for performance).
     * 
     * @param array $permissions
     * @return array ['permission_slug' => bool, ...]
     */
    public function hasPermissionsTo(array $permissions): array
    {
        if (empty($permissions)) {
            return [];
        }

        // Load all needed relations once
        if (!$this->relationLoaded('permissions')) {
            $this->load('permissions');
        }
        if (!$this->relationLoaded('roles')) {
            $this->load('roles.permissions');
        }

        $results = [];
        
        foreach ($permissions as $permission) {
            $permissionSlug = $permission instanceof Permission ? $permission->slug : $permission;
            $results[$permissionSlug] = $this->hasPermissionTo($permission);
        }

        return $results;
    }

    /**
     * Check if user has ANY of the given permissions.
     * 
     * @param array $permissions
     * @return bool
     */
    public function hasAnyPermission(...$permissions): bool
    {
        $permissions = Arr::flatten($permissions);
        
        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has ALL of the given permissions.
     * 
     * @param array $permissions
     * @return bool
     */
    public function hasAllPermissions(...$permissions): bool
    {
        $permissions = Arr::flatten($permissions);
        
        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }
    
    /**
     * Give the specified permissions to the model.
     * 
     * @param  array  $permissions
     * @return self
     */
    public function givePermissionTo(...$permissions): self
    {        
        $permissions = Arr::flatten($permissions);
        $permissions = $this->getPermissions($permissions);

        if (! $permissions) {
            return $this;
        }

        $this->permissions()->syncWithoutDetaching($permissions);

        return $this;
    }

    /**
     * Revoke the specified permissions from the model.
     * 
     * @param  array  $permissions
     * @return self
     */
    public function revokePermissionTo(...$permissions): self
    {
        $permissions = Arr::flatten($permissions);
        $permissions = $this->getPermissions($permissions);

        $this->permissions()->detach($permissions);

        return $this;
    }

    /**
     * Sync the specified permissions against the model.
     * 
     * @param  array  $permissions
     * @return self
     */
    public function syncPermissions(...$permissions): self
    {
        $permissions = Arr::flatten($permissions);
        $permissions = $this->getPermissions($permissions);

        $this->permissions()->sync($permissions);

        return $this;
    }

    /**
     * Get the specified permissions.
     * 
     * @param  array  $permissions
     * @return array
     */
    protected function getPermissions(array $collection): array
    {
        if (empty($collection)) {
            return [];
        }
        
        // Separate already resolved IDs from slugs/instances
        $ids = [];
        $slugsToResolve = [];
        
        foreach ($collection as $permission) {
            if ($permission instanceof Permission) {
                $ids[] = $permission->id;
            } elseif (is_string($permission)) {
                $slugsToResolve[] = $permission;
            } elseif (is_numeric($permission)) {
                $ids[] = $permission;
            }
        }
        
        // Resolve all slugs in one query if any
        if (!empty($slugsToResolve)) {
            $resolvedPermissions = $this->getPermissionModel()
                ->whereIn('slug', $slugsToResolve)
                ->pluck('id')
                ->toArray();
            $ids = array_merge($ids, $resolvedPermissions);
        }
        
        return array_unique($ids);
    }

    /**
     * Checks if the user has the given permission assigned.
     * 
     * @param  \Callcocam\ReactPapaLeguas\Shinobi\Models\Permission|string  $permission
     * @return boolean
     */
    protected function hasPermission($permission): bool
    {
        $permissionSlug = $permission;
        if ($permission instanceof Permission) {
            $permissionSlug = $permission->slug;
        }

        // Load permissions if not loaded to avoid N+1
        if (!$this->relationLoaded('permissions')) {
            $this->load('permissions');
        }

        return $this->permissions->where('slug', $permissionSlug)->isNotEmpty();
    }

    /**
     * Get the model instance responsible for permissions.
     * 
     * @return \Callcocam\ReactPapaLeguas\Shinobi\Contracts\Permission|\Illuminate\Database\Eloquent\Collection
     */
    protected function getPermissionModel()
    {  
        if (config('shinobi.cache.enabled')) {
            $cacheKey = 'shinobi.permissions.' . (app('tenant.manager')->getCurrentTenantId() ?? 'global');
            
            return cache()->tags(config('shinobi.cache.tag'))->remember(
                $cacheKey,
                config('shinobi.cache.length'),
                function() {
                    return app()->make(config('shinobi.models.permission'))->get();
                }
            );
        }

        return app()->make(config('shinobi.models.permission'));
    }
}

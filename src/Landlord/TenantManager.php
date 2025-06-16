<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Landlord;

use Callcocam\ReactPapaLeguas\Landlord\Exceptions\TenantColumnUnknownException;
use Callcocam\ReactPapaLeguas\Landlord\Exceptions\TenantNullIdException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;

class TenantManager
{
    use Macroable;

    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var Collection
     */
    protected $tenants;

    /**
     * @var Collection
     */
    protected $deferredModels;

    /**
     * Landlord constructor.
     */
    public function __construct()
    {
        $this->tenants = collect();
        $this->deferredModels = collect();
    }

    /**
     * Enable scoping by tenantColumns.
     *
     * @return void
     */
    public function enable()
    {
        $this->enabled = true;
    }

    /**
     * Disable scoping by tenantColumns.
     *
     * @return void
     */
    public function disable()
    {
        $this->enabled = false;
    }

    /**
     * Add a tenant to scope by.
     *
     * @param string|Model $tenant
     * @param mixed|null   $id
     *
     * @throws TenantNullIdException
     */
    public function addTenant($tenant, $id = null)
    {
        if (func_num_args() == 1 && $tenant instanceof Model) {
            $id = $tenant->getKey();
        }

        if (is_null($id)) {
            throw new TenantNullIdException('$id must not be null');
        }

        $this->tenants->put($this->getTenantKey($tenant), $id);
    }

    /**
     * Remove a tenant so that queries are no longer scoped by it.
     *
     * @param string|Model $tenant
     */
    public function removeTenant($tenant)
    {
        $this->tenants->pull($this->getTenantKey($tenant));
    }

    /**
     * Whether a tenant is currently being scoped.
     *
     * @param string|Model $tenant
     *
     * @return bool
     */
    public function hasTenant($tenant)
    {
        return $this->tenants->has($this->getTenantKey($tenant));
    }

    /**
     * @return Collection
     */
    public function getTenants()
    {
        return $this->tenants;
    }

    /**
     * @param $tenant
     *
     * @throws TenantColumnUnknownException
     *
     * @return mixed
     */
    public function getTenantId($tenant)
    {
        if (!$this->hasTenant($tenant)) {
            throw new TenantColumnUnknownException(
                '$tenant must be a string key or an instance of \Illuminate\Database\Eloquent\Model'
            );
        }

        return $this->tenants->get($this->getTenantKey($tenant));
    }

    /**
     * Applies applicable tenant scopes to a model.
     *
     * @param Model|BelongsToTenants $model
     */
    public function applyTenantScopes(Model $model)
    {
        // Skip tenant scoping if disabled
        if (!$this->enabled) {
            return;
        }

        // Skip tenant scoping if user is authenticated via landlord guard
        if ($this->shouldBypassTenantScoping()) {
            return;
        }

        if (method_exists($model, 'isDefault')) {
            if ($model->isDefault()) {
                return;
            }
        }

        if ($this->tenants->isEmpty()) {
            // No tenants yet, defer scoping to a later stage
            $this->deferredModels->push($model);

            return;
        }

        $this->modelTenants($model)->each(function ($id, $tenant) use ($model) {
            $model->addGlobalScope($tenant, function (Builder $builder) use ($tenant, $id, $model) {
                if ($this->getTenants()->first() && $this->getTenants()->first() != $id) {
                    $id = $this->getTenants()->first();
                }

                $builder->where($model->getQualifiedTenant($tenant), '=', $id);
            });
        });
    }

    /**
     * Applies applicable tenant scopes to deferred model booted before tenants setup.
     */
    public function applyTenantScopesToDeferredModels()
    {
        $this->deferredModels->each(function ($model) {
            /* @var Model|BelongsToTenants $model */
            $this->modelTenants($model)->each(function ($id, $tenant) use ($model) {
                if (!isset($model->{$tenant})) {
                    $model->setAttribute($tenant, $id);
                }

                $model->addGlobalScope($tenant, function (Builder $builder) use ($tenant, $id, $model) {
                    if ($this->getTenants()->first() && $this->getTenants()->first() != $id) {
                        $id = $this->getTenants()->first();
                    }

                    $builder->where($model->getQualifiedTenant($tenant), '=', $id);
                });
            });
        });

        $this->deferredModels = collect();
    }

    /**
     * Add tenant columns as needed to a new model instance before it is created.
     *
     * @param Model $model
     */
    public function newModel(Model $model)
    {
        if (!$this->enabled) {
            return;
        }

        if ($this->tenants->isEmpty()) {
            // No tenants yet, defer scoping to a later stage
            $this->deferredModels->push($model);

            return;
        }

        $this->modelTenants($model)->each(function ($tenantId, $tenantColumn) use ($model) {
            if (!isset($model->{$tenantColumn})) {
                $model->setAttribute($tenantColumn, $tenantId);
            }
        });
    }

    /**
     * Get a new Eloquent Builder instance without any of the tenant scopes applied.
     *
     * @param Model $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function newQueryWithoutTenants(Model $model)
    {
        return $model->newQuery()->withoutGlobalScopes($this->tenants->keys()->toArray());
    }

    /**
     * Get the key for a tenant, either from a Model instance or a string.
     *
     * @param string|Model $tenant
     *
     * @throws TenantColumnUnknownException
     *
     * @return string
     */
    protected function getTenantKey($tenant)
    {
        if ($tenant instanceof Model) {
            $tenant = $tenant->getForeignKey();
        }

        if (!is_string($tenant)) {
            throw new TenantColumnUnknownException(
                '$tenant must be a string key or an instance of \Illuminate\Database\Eloquent\Model'
            );
        }

        return $tenant;
    }

    /**
     * Get the tenantColumns that are actually applicable to the given
     * model, in case they've been manually specified.
     *
     * @param Model|BelongsToTenants $model
     *
     * @return Collection
     */
    protected function modelTenants(Model $model)
    {
        return $this->tenants->only($model->getTenantColumns());
    }

    /**
     * Check if tenant scoping should be bypassed.
     *
     * @return bool
     */
    protected function shouldBypassTenantScoping(): bool
    {
        // Check if app has auth and if landlord guard is being used
        if (!app()->bound('auth')) {
            return false;
        }

        try {
            // Check if current user is authenticated via landlord guard
            if (auth()->guard('landlord')->check()) {
                return true;
            }

            // Check if request is coming from landlord routes
            if (request()->is(config('react-papa-leguas.landlord.routes.prefix', 'landlord') . '/*')) {
                return true;
            }

            return false;
        } catch (\Exception $e) {
            // In case of any auth errors, don't bypass
            return false;
        }
    }

    /**
     * Temporarily disable tenant scoping for a callback.
     * Useful for landlord operations that need to access all tenant data.
     *
     * @param callable $callback
     * @return mixed
     */
    public function withoutTenantScoping(callable $callback)
    {
        $wasEnabled = $this->enabled;
        $this->disable();

        try {
            return $callback();
        } finally {
            if ($wasEnabled) {
                $this->enable();
            }
        }
    }

    /**
     * Enable tenant scoping for landlord users (override bypass).
     * Useful when landlord needs to work within specific tenant context.
     *
     * @param callable $callback
     * @return mixed
     */
    public function withTenantScoping(callable $callback)
    {
        // Store current auth state
        $currentGuard = auth()->getDefaultDriver();

        try {
            // Temporarily switch away from landlord guard
            auth()->setDefaultDriver('web');
            return $callback();
        } finally {
            // Restore original guard
            auth()->setDefaultDriver($currentGuard);
        }
    }
}

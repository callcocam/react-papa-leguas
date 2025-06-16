<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Landlord;

use Callcocam\ReactPapaLeguas\Models\Tenant;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class LandlordServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->bootTenant();
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TenantManager::class, function () {
            return new TenantManager();
        });
    }

    public function bootTenant()
    {
        if (app()->runningInConsole()) {
            return false;
        }
        
        // Skip tenant resolution if landlord guard is active
        if ($this->shouldSkipTenantResolution()) {
            return false;
        }

        $tenant = null;
        try {
            $tenant = $this->resolveTenantFromRequest();
            
            if ($tenant) {
                $this->configureTenant($tenant);
            } else {
                $this->handleTenantNotFound();
            }
        } catch (\PDOException $th) {
            throw $th;
        }
    }

    /**
     * Check if tenant resolution should be skipped.
     */
    protected function shouldSkipTenantResolution(): bool
    {
        try {
            // Skip if landlord user is authenticated
            if (auth()->guard('landlord')->check()) {
                return true;
            }
            
            // Skip for landlord routes
            $landlordPrefix = config('react-papa-leguas.landlord.routes.prefix', 'landlord');
            if (request()->is($landlordPrefix . '/*')) {
                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Resolve tenant from current request.
     */
    protected function resolveTenantFromRequest()
    {
        $host = request()->getHost();
        
        // Try exact domain match first
        $tenant = app($this->getModel())->query()->where('domain', $host)->first();
        
        if (!$tenant) {
            // Try subdomain match
            $subdomain = explode('.', $host);
            $host = $subdomain[0];
            $tenant = app($this->getModel())->query()->where('prefix', $host)->first();
        }
        
        return $tenant;
    }

    /**
     * Configure application for the resolved tenant.
     */
    protected function configureTenant($tenant): void
    {
        app(TenantManager::class)->addTenant("tenant_id", data_get($tenant, 'id'));
        
        config([
            'app.tenant_id' => $tenant->id,
            'app.name' => Str::limit($tenant->name, 20, '...'),
            'app.cover_photo_url' => $tenant->cover_photo_url,
            'app.tenant' => $tenant->toArray(),
            'app.tenant.company_id' => $tenant->old_id,
        ]);
    }

    /**
     * Handle when no tenant is found.
     */
    protected function handleTenantNotFound(): void
    {
        $host = request()->getHost();
        
        // For API requests, return JSON response
        if (request()->expectsJson()) {
            abort(404, "No tenant found for domain: {$host}");
        }
        
        // For web requests, you might want to redirect to a landing page
        // or show a custom 404 page
        abort(404, "Nenhuma empresa cadastrada com esse endereço: {$host}");
    }

    public  function getModel(): string
    {
        return config('tenant.models.tenant',  Tenant::class);
    }
}

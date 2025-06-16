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
        
        // Always skip tenant resolution for landlord routes
        $landlordPrefix = config('react-papa-leguas.landlord.routes.prefix', 'landlord'); 
        if (request()->is($landlordPrefix . '/*')) {
            return false;
        }
        
        // Skip tenant resolution if landlord guard is active
        if ($this->shouldSkipTenantResolution()) {
            return false;
        }

        // Skip tenant resolution if explicitly disabled
        if (config('tenant.skip_resolution', false)) {
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
        
        // Check if there are any tenants at all in the system
        $totalTenants = app($this->getModel())->query()->count();
        
        // For API requests, return JSON response
        if (request()->expectsJson()) {
            if ($totalTenants === 0) {
                abort(404, [
                    'message' => 'Nenhum tenant cadastrado no sistema',
                    'action' => 'Cadastre o primeiro tenant para começar',
                    'redirect_url' => url('/landlord/login')
                ]);
            } else {
                abort(404, "Nenhuma empresa cadastrada para o domínio: {$host}");
            }
        } 
        // For web requests, show informative page or redirect
        if ($totalTenants === 0) {
            // No tenants in system - show setup page
            $this->showTenantSetupPage($host);
        } else {
            // Tenants exist but none for this domain - show domain not found page
            $this->showDomainNotFoundPage($host);
        }
    }
    
    /**
     * Show tenant setup page when no tenants exist in the system.
     */
    protected function showTenantSetupPage(string $host): void
    {
        $setupUrl = url('/landlord/login');
        $message = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Papa Leguas - Configuração Inicial</title>
            <style>
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    margin: 0; padding: 0; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    min-height: 100vh; display: flex; align-items: center; justify-content: center;
                }
                .container { 
                    background: white; padding: 3rem; border-radius: 20px; 
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1); text-align: center; max-width: 500px; margin: 2rem;
                }
                .logo { font-size: 3rem; margin-bottom: 1rem; }
                h1 { color: #333; margin-bottom: 1rem; font-size: 1.8rem; }
                p { color: #666; line-height: 1.6; margin-bottom: 2rem; }
                .btn { 
                    background: #667eea; color: white; padding: 1rem 2rem; 
                    text-decoration: none; border-radius: 10px; font-weight: 600;
                    display: inline-block; transition: all 0.3s ease;
                }
                .btn:hover { background: #5a6fd8; transform: translateY(-2px); }
                .info { background: #f8f9ff; padding: 1rem; border-radius: 10px; margin: 1rem 0; }
                .domain { color: #667eea; font-weight: 600; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'>🦘</div>
                <h1>Bem-vindo ao Papa Leguas!</h1>
                <p>Sistema ainda não configurado. Nenhum tenant foi cadastrado no sistema.</p>
                
                <div class='info'>
                    <strong>Domínio atual:</strong> <span class='domain'>{$host}</span>
                </div>
                
                <p>Para começar a usar o sistema, você precisa:</p>
                <ol style='text-align: left; color: #666;'>
                    <li>Fazer login como administrador</li>
                    <li>Cadastrar o primeiro tenant</li>
                    <li>Configurar o domínio <strong>{$host}</strong></li>
                </ol>
                
                <a href='{$setupUrl}' class='btn'>Acessar Painel do Administrador</a>
            </div>
        </body>
        </html>";
        
        response($message, 200, ['Content-Type' => 'text/html'])->send();
        exit;
    }
    
    /**
     * Show domain not found page when tenants exist but none for this domain.
     */
    protected function showDomainNotFoundPage(string $host): void
    {
        $landlordUrl = url('/landlord/login');
        $message = "
        <!DOCTYPE html>
        <html lang='pt-BR'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Domínio não encontrado - Papa Leguas</title>
            <style>
                body { 
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                    margin: 0; padding: 0; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
                    min-height: 100vh; display: flex; align-items: center; justify-content: center;
                }
                .container { 
                    background: white; padding: 3rem; border-radius: 20px; 
                    box-shadow: 0 20px 40px rgba(0,0,0,0.1); text-align: center; max-width: 500px; margin: 2rem;
                }
                .logo { font-size: 3rem; margin-bottom: 1rem; }
                h1 { color: #333; margin-bottom: 1rem; font-size: 1.8rem; }
                p { color: #666; line-height: 1.6; margin-bottom: 2rem; }
                .btn { 
                    background: #f5576c; color: white; padding: 1rem 2rem; 
                    text-decoration: none; border-radius: 10px; font-weight: 600;
                    display: inline-block; transition: all 0.3s ease;
                }
                .btn:hover { background: #e74c5c; transform: translateY(-2px); }
                .error { background: #fff5f5; border: 1px solid #fed7d7; color: #c53030; padding: 1rem; border-radius: 10px; margin: 1rem 0; }
                .domain { font-weight: 600; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='logo'>🚫</div>
                <h1>Domínio não encontrado</h1>
                
                <div class='error'>
                    Nenhuma empresa está cadastrada para o domínio: <span class='domain'>{$host}</span>
                </div>
                
                <p>O sistema Papa Leguas está funcionando, mas este domínio específico não está configurado para nenhuma empresa.</p>
                
                <p><strong>Se você é administrador:</strong><br>
                Acesse o painel administrativo para configurar este domínio.</p>
                
                <a href='{$landlordUrl}' class='btn'>Painel do Administrador</a>
            </div>
        </body>
        </html>";
        
        response($message, 404, ['Content-Type' => 'text/html'])->send();
        exit;
    }

    public  function getModel(): string
    {
        return config('tenant.models.tenant',  Tenant::class);
    }
}

<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
    public function register(): void
    {
        parent::register();

        // Register service providers first
        $this->registerServiceProviders();
    }s://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas;

use Callcocam\ReactPapaLeguas\Commands\ReactPapaLeguasCommand;
use Callcocam\ReactPapaLeguas\Commands\MakeStandardModelCommand;
use Callcocam\ReactPapaLeguas\Commands\MigrateToPapaLeguasStandardsCommand;
use Callcocam\ReactPapaLeguas\Commands\CheckStandardsCommand;
use Callcocam\ReactPapaLeguas\Guards\LandlordGuard;
use Callcocam\ReactPapaLeguas\Http\Middleware\LandlordAuth;
use Callcocam\ReactPapaLeguas\Http\Middleware\DisableTenantScoping;
use Callcocam\ReactPapaLeguas\Providers\LandlordAuthProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ReactPapaLeguasServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('react-papa-leguas')
            ->hasConfigFile()
            ->hasConfigFile('shinobi')
            ->hasConfigFile('tenant')
            ->hasRoutes('web', 'api', 'landlord')
            ->hasViews()
            ->hasMigrations([
                'create_users_table',
                'create_admins_table',
                'create_tenants_table', 
                'create_addresses_table',
                'create_roles_table',
                'create_permissions_table',
                'create_role_user_table',
                'create_permission_user_table',
                'create_permission_role_table',
                'create_admin_role_table',
                'create_admin_tenant_table'
            ])
            ->hasCommand(ReactPapaLeguasCommand::class)
            ->hasCommand(MakeStandardModelCommand::class)
            ->hasCommand(MigrateToPapaLeguasStandardsCommand::class)
            ->hasCommand(CheckStandardsCommand::class)
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishAssets()
                    ->publishMigrations()
                    ->publish('react-papa-leguas:translations')
                    ->askToRunMigrations()
                    ->copyAndRegisterServiceProviderInApp()
                    ->askToStarRepoOnGitHub('callcocam/react-papa-leguas')
                    ->endWith(function (InstallCommand $command) {
                        $command->info('');
                        $command->info('🚀 React Papa Leguas instalado com sucesso!');
                        $command->info('');
                        
                        if ($command->confirm('Deseja migrar seus modelos e migrations para os padrões Papa Leguas?', true)) {
                            $command->call('papa-leguas:migrate-standards', [
                                '--backup' => true,
                                '--force' => true
                            ]);
                            
                            $command->info('');
                            $command->info('✅ Migração concluída! Verifique os arquivos gerados.');
                            $command->info('�️  As migrations necessárias foram publicadas automaticamente.');
                            $command->info('�📝 Consulte packages/callcocam/react-papa-leguas/UPDATES.md para mais detalhes.');
                        } else {
                            $command->info('');
                            $command->warn('⚠️  Para migrar mais tarde, execute: php artisan papa-leguas:migrate-standards --backup');
                            $command->info('📝 Consulte packages/callcocam/react-papa-leguas/UPDATES.md para instruções detalhadas.');
                        }
                        
                        $command->info('');
                        $command->info('📚 Documentação disponível em:');
                        $command->info('   - packages/callcocam/react-papa-leguas/DEVELOPMENT_STANDARDS.md');
                        $command->info('   - packages/callcocam/react-papa-leguas/EXAMPLES.md');
                        $command->info('   - packages/callcocam/react-papa-leguas/OPTIMIZATION_REPORT.md');
                    });
            });
    }

    public function register(): void
    {
        parent::register();

        // Register service providers first
        $this->registerServiceProviders(); // Temporariamente comentado para debug
    }

    public function packageBooted(): void
    {
        // Register the landlord authentication guard
        $this->registerLandlordAuth();

        // Register middleware
        $this->registerMiddleware();

        // Load landlord routes
        $this->loadLandlordRoutes();

        // Show standards update message if needed (only in console)
        $this->showStandardsUpdateMessage();
    }

    /**
     * Register the landlord authentication guard and provider.
     */
    protected function registerLandlordAuth(): void
    {
        Auth::provider('landlord', function ($app, array $config) {
            return new LandlordAuthProvider(
                $app['hash'],
                $config['model'] ?? config('react-papa-leguas.landlord.model')
            );
        });

        Auth::extend('landlord', function ($app, $name, array $config) {
            return new LandlordGuard(
                $name,
                Auth::createUserProvider($config['provider']),
                $app['session.store'],
                $app['request']
            );
        });
    }

    /**
     * Register middleware.
     */
    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];

        $router->aliasMiddleware('landlord.auth', LandlordAuth::class);
        $router->aliasMiddleware('disable.tenant.scoping', DisableTenantScoping::class);
    }

    /**
     * Load landlord routes.
     */
    protected function loadLandlordRoutes(): void
    {
        $config = config('react-papa-leguas.landlord.routes', []);

        Route::middleware($config['middleware'] ?? ['web'])
            ->prefix($config['prefix'] ?? 'landlord')
            ->group(function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/landlord.php');
            });
    }

    /**
     * Register additional service providers.
     */
    protected function registerServiceProviders(): void
    {
        $this->app->register(\Callcocam\ReactPapaLeguas\Shinobi\ShinobiServiceProvider::class);
        $this->app->register(\Callcocam\ReactPapaLeguas\Landlord\LandlordServiceProvider::class);
    }

    /**
     * Check if the project needs to be updated to Papa Leguas standards.
     */
    public function checkForStandardsUpdate(): bool
    {
        $userModelPath = app_path('Models/User.php');
        
        if (!file_exists($userModelPath)) {
            return false;
        }
        
        $userModelContent = file_get_contents($userModelPath);
        
        // Check if User model already follows Papa Leguas standards
        $hasUlid = str_contains($userModelContent, 'HasUlids') || str_contains($userModelContent, 'ulid');
        $hasSlug = str_contains($userModelContent, 'HasSlug') || str_contains($userModelContent, 'slug');
        $hasStatus = str_contains($userModelContent, 'status') || str_contains($userModelContent, 'BaseStatus');
        $hasTenantId = str_contains($userModelContent, 'tenant_id');
        $hasSoftDeletes = str_contains($userModelContent, 'SoftDeletes');
        
        // If missing key Papa Leguas features, needs update
        return !($hasUlid && $hasSlug && $hasStatus && $hasTenantId && $hasSoftDeletes);
    }

    /**
     * Show standards update message if needed.
     */
    public function showStandardsUpdateMessage(): void
    {
        if ($this->checkForStandardsUpdate()) {
            if (app()->runningInConsole()) {
                echo "\n";
                echo "📦 \033[33mPapa Leguas Standards Update Available\033[0m\n";
                echo "   Seu projeto pode se beneficiar dos padrões Papa Leguas mais recentes.\n";
                echo "   Execute: \033[32mphp artisan papa-leguas:migrate-standards --backup\033[0m\n";
                echo "\n";
            }
        }
    }
}

<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas;

use Callcocam\ReactPapaLeguas\Commands\ReactPapaLeguasCommand;
use Callcocam\ReactPapaLeguas\Guards\LandlordGuard;
use Callcocam\ReactPapaLeguas\Http\Middleware\LandlordAuth;
use Callcocam\ReactPapaLeguas\Providers\LandlordAuthProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
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
            ->hasMigration('create_admins_table')
            ->hasCommand(ReactPapaLeguasCommand::class);
    }

    public function register(): void
    {
        parent::register();
        
        // Register service providers first
        $this->registerServiceProviders();
    }

    public function packageBooted(): void
    {
        // Register the landlord authentication guard
        $this->registerLandlordAuth();

        // Register middleware
        $this->registerMiddleware();

        // Load landlord routes
        $this->loadLandlordRoutes();
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
}

<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Callcocam\ReactPapaLeguas\Commands\ReactPapaLeguasCommand;

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
            ->hasRoutes('web', 'api')
            ->hasViews()
            ->hasMigration('create_react_papa_leguas_table')
            ->hasCommand(ReactPapaLeguasCommand::class);
    }
}

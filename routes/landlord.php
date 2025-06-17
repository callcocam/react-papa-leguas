
<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

use Callcocam\ReactPapaLeguas\Http\Controllers\Auth\LandlordLoginController;
use Callcocam\ReactPapaLeguas\Http\Controllers\Landlord\DashboardController;
use Callcocam\ReactPapaLeguas\Http\Controllers\Landlord\TenantController;
use Callcocam\ReactPapaLeguas\Http\Controllers\Landlord\TestController;
use Callcocam\ReactPapaLeguas\Http\Controllers\Landlord\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Landlord Routes
|--------------------------------------------------------------------------
|
| Here are the routes for landlord authentication and dashboard.
| These routes are loaded by the ReactPapaLeguasServiceProvider within
| a group which contains the landlord-specific middleware.
|
*/
// Authentication Routes (Guest only)
Route::middleware(['guest:landlord', 'disable.tenant.scoping'])->group(function () {
    Route::get('/login', [LandlordLoginController::class, 'showLoginForm'])
        ->name('landlord.login');

    Route::post('/login', [LandlordLoginController::class, 'login'])
        ->name('landlord.login.post');
});

// Authenticated Routes
Route::middleware(['landlord.auth', 'disable.tenant.scoping'])
    ->as('landlord.')
    ->group(function () {
        Route::post('/logout', [LandlordLoginController::class, 'logout'])
            ->name('landlord.logout');

        Route::get('/logout', [LandlordLoginController::class, 'logout'])
            ->name('landlord.logout.get');

        Route::get('/', [DashboardController::class, 'index'])
            ->name('landlord.dashboard');

        // Tenant routes
        Route::resource('tenants', TenantController::class);
        Route::resource('users', UserController::class);


        Route::post('tenants/bulk-destroy', [TenantController::class, 'bulkDestroy'])
            ->name('landlord.tenants.bulk-destroy');
        Route::patch('tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])
            ->name('landlord.tenants.toggle-status');
        Route::post('tenants/export', [TenantController::class, 'export'])
            ->name('landlord.tenants.export');

        Route::get('/tests', [TestController::class, 'index'])
            ->name('landlord.tests');
    });

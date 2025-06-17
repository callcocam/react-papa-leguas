
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
Route::middleware(['landlord.auth', 'disable.tenant.scoping'])->group(function () {
    Route::post('/logout', [LandlordLoginController::class, 'logout'])
        ->name('landlord.logout');
    
    Route::get('/logout', [LandlordLoginController::class, 'logout'])
        ->name('landlord.logout.get');
    
    Route::get('/', [DashboardController::class, 'index'])
        ->name('landlord.dashboard');

    Route::resource('tenants', TenantController::class);

    Route::get('/tests', [TestController::class, 'index'])
        ->name('landlord.tests');
});
 
 
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

// Rota de teste pública (sem autenticação)
Route::get('/test-table', [TenantController::class, 'test'])
    ->middleware('disable.tenant.scoping')
    ->name('landlord.test.table');

// Rota de teste para UserTable
Route::get('/test-user-table', function () {
    try {
        $userTable = new \Callcocam\ReactPapaLeguas\Tables\UserTable();
        
        return response()->json([
            'success' => true,
            'message' => 'UserTable funcionando!',
            'table_info' => [
                'id' => $userTable->getId(),
                'model' => $userTable->getModel(),
                'columns_count' => count($userTable->getColumns()),
                'actions_count' => count($userTable->getActions()),
            ]
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
})->middleware('disable.tenant.scoping')->name('landlord.test.user.table');

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
        Route::post('tenants/bulk-destroy', [TenantController::class, 'bulkDestroy'])
            ->name('tenants.bulk-destroy');
        Route::patch('tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])
            ->name('tenants.toggle-status');
        Route::post('tenants/export', [TenantController::class, 'export'])
            ->name('tenants.export');

        // User routes
        Route::resource('users', UserController::class);
        Route::post('users/bulk-destroy', [UserController::class, 'bulkDestroy'])
            ->name('users.bulk-destroy');
        Route::post('users/export', [UserController::class, 'export'])
            ->name('users.export');

        Route::get('/tests', [TestController::class, 'index'])
            ->name('landlord.tests');
    });

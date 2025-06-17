
<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

use Callcocam\ReactPapaLeguas\Facades\ReactPapaLeguas;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::prefix(ReactPapaLeguas::getPrefix())
    ->name(ReactPapaLeguas::getId())
    ->middleware(ReactPapaLeguas::getMiddlewares())
    ->group(function () {

        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('/', function () {
                return Inertia::render('app');
            })->name('dashboard');

            // Rota para página de testes
            

            // Rota para CRUD (produção)
            Route::get('/crud', function () {
                return Inertia::render('crud/index', [
                    'data' => [],
                    'columns' => [],
                    'filters' => [],
                    'actions' => [],
                    'permissions' => [
                        'user_permissions' => auth()->user()->getAllPermissions()->pluck('name') ?? [],
                        'user_roles' => auth()->user()->getRoleNames() ?? [],
                        'is_super_admin' => auth()->user()->hasRole('super-admin') ?? false,
                    ],
                    'config' => [
                        'selectable' => true,
                        'sortable' => true,
                        'filterable' => true
                    ]
                ]);
            })->name('crud');
        });
    });

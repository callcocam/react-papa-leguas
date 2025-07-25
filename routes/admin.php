
<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

use Callcocam\ReactPapaLeguas\Facades\ReactPapaLeguas;
use Callcocam\ReactPapaLeguas\Http\Controllers\Admin\WorkflowController;
use Callcocam\ReactPapaLeguas\Http\Controllers\Admin\WorkflowTemplateController;
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

Route::prefix('admin')
    ->name(ReactPapaLeguas::getId().'.')
    ->middleware(ReactPapaLeguas::getMiddlewares())
    ->group(function () {

        Route::middleware(['auth', 'verified'])->group(function () {
            Route::get('/', function () {
                return Inertia::render('app');
            })->name('dashboard');

            Route::resource('workflows', WorkflowController::class);
            Route::resource('templates', WorkflowTemplateController::class);
        });
    });

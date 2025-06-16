<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

use Callcocam\ReactPapaLeguas\Facades\ReactPapaLeguas;
use Illuminate\Support\Facades\Route;

Route::prefix('api') 
->middleware(['auth:sanctum'])
    ->group(function () {
        Route::prefix(ReactPapaLeguas::getPrefix())
            ->name(ReactPapaLeguas::getId())
            ->middleware(ReactPapaLeguas::getMiddlewares())
            ->group(function () {


                Route::get('/', function () {
                    return view('welcome');
                });
            });
    });

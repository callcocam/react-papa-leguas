<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToModel;
use Callcocam\ReactPapaLeguas\Http\Controllers\Controller;
use Callcocam\ReactPapaLeguas\Support\Concerns\ResolvesModel;
use Callcocam\ReactPapaLeguas\Support\Concerns\ModelQueries;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Class LandlordController
 * @package Callcocam\ReactPapaLeguas\Http\Controllers\Landlord
 */
class LandlordController extends Controller
{
    use BelongsToModel, ResolvesModel, ModelQueries;

     /**
     * Display the landlord dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        return Inertia::render($this->getViewIndex(), $this->getDataForViews($request));
    }

}

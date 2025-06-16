<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Callcocam\ReactPapaLeguas\Http\Controllers\Controller;
use Callcocam\ReactPapaLeguas\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Class LandlordController
 * @package Callcocam\ReactPapaLeguas\Http\Controllers\Landlord
 */
class LandlordController extends Controller
{
    /**
     * Display the landlord dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return Inertia::render($this->getViewIndex(), $this->getDataForViews());
    }
 
}

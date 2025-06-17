<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Inertia\Inertia;
use Illuminate\Http\Request;

class TestController extends LandlordController
{
    /**
     * Display the landlord dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        return Inertia::render('tests/index', $this->getDataForViews($request));
    }
}
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\App\Landlord;
use Callcocam\ReactPapaLeguas\Http\Controllers\Controller;
use Inertia\Inertia;
/**
 * Class LandlordController
 * @package Callcocam\ReactPapaLeguas\Http\Controllers\App\Landlord
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
        return Inertia::render($this->getViewIndex(),  $this->getViewData());
    }

    /**
     * Get view data for the landlord dashboard.
     *
     * @return array
     */
    protected function getViewData()
    {
        return [
            'title' => 'Landlord Dashboard',
            'description' => 'Welcome to the Landlord Dashboard',
        ];
    }
}
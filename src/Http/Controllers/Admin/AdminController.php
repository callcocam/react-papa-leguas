<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Admin;

use Callcocam\ReactPapaLeguas\Http\Controllers\Controller;
use Inertia\Inertia;

/**
 * Class AdminController
 * @package Callcocam\ReactPapaLeguas\Http\Controllers\Admin
 */
class AdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return Inertia::render($this->getViewIndex(),  $this->getViewData());
    }
}

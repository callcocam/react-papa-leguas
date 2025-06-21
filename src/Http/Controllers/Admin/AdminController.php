<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Admin;

use Callcocam\ReactPapaLeguas\Http\Controllers\Controller;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToModel;
use Callcocam\ReactPapaLeguas\Support\Concerns\ModelQueries;
use Callcocam\ReactPapaLeguas\Support\Concerns\ResolvesModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

/**
 * Class AdminController
 * @package Callcocam\ReactPapaLeguas\Http\Controllers\Admin
 */
class AdminController extends Controller
{
    use BelongsToModel, ResolvesModel, ModelQueries;

    /**
     * Display the landlord dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->has('debug')) {
            Storage::put('debug.json', json_encode($this->getDataForViewsIndex($request)));
            return Inertia::render('crud/debug', $this->getDataForViewsIndex($request));
        }
        return Inertia::render($this->getViewIndex(), $this->getDataForViewsIndex($request));
    }

    public function test(Request $request)
    {
        return Inertia::render('crud/debug', $this->getDataForViews($request));
    }

    protected function getDataForViewsIndex(Request $request)
    {
        $table = $this->getTable();
        $data = $table->toArray();
        return array_merge($this->getDataForViews($request), $data);
    }
}

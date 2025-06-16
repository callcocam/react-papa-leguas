<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Http\Controllers;

use Callcocam\ReactPapaLeguas\Facades\ReactPapaLeguas;
use Illuminate\Routing\Controller as BaseController;
/**
 * Class Controller
 * @package Callcocam\ReactPapaLeguas\Http\Controllers
 */
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;

class Controller extends BaseController{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get the ID of the controller.
     *
     * @return string
     */
    protected function getId()
    {
        return ReactPapaLeguas::getId();
    }
    /**
     * Get the prefix for the controller routes.
     *
     * @return string
     */
    protected function getPrefix()
    {
        return ReactPapaLeguas::getPrefix();
    }

    /**
     * Get view path index.
     */
    protected function getViewIndex()
    {
        return 'crud/index';
    }

    /**
     * Get view path create.
     */
    protected function getViewCreate()
    {
        return 'crud/create';
    }
    /**
     * Get view path edit.
     */
    protected function getViewEdit()
    {
        return 'crud/edit';
    }
    /**
     * Get view path show.
     */
    protected function getViewShow()
    {
        return 'crud/show';
    }

    /**
     * Get data for views.
     */
    protected function getDataForViews()
    {
        return [
            'user' => auth()->user(),
            'permissions' => auth()->user()->getAllPermissions(),
        ];
    }

}

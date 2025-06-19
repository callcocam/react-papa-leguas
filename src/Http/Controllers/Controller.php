<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers;

use Callcocam\ReactPapaLeguas\Facades\ReactPapaLeguas;
use Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;
use Illuminate\Routing\Controller as BaseController;

/**
 * Class Controller
 * @package Callcocam\ReactPapaLeguas\Http\Controllers
 */

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Controller extends BaseController
{

    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    use EvaluatesClosures;


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
    protected function getDataForViews(Request $request)
    {
        return [
            'user' => auth()->user(),
            'permissions' => [],
            'request' => $request->query(),
        ];
    }


    /**
     * Get route name for current controller
     */
    protected function getControllerName(): string
    {
        $controllerName = Str::snake(str_replace('Controller', '', class_basename(static::class)));
        $controllerName = Str::plural($controllerName);
        return $controllerName;
    }

    /**
     * Get the route name for the controller.
     *
     * @param string $suffix
     * @param string|null $prefix
     * @param string|null $controller
     * @return string
     */
    protected function getRouteName(string $suffix = 'index', ?string $prefix = null, ?string $controller = null): string
    {
        $prefix = $prefix ?? $this->getPrefix();
        $controller = $controller ?? $this->getControllerName();
        return "{$prefix}.{$controller}.{$suffix}";
    }
}

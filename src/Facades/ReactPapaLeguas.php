<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Callcocam\ReactPapaLeguas\ReactPapaLeguas
 */
class ReactPapaLeguas extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Callcocam\ReactPapaLeguas\ReactPapaLeguas::class;
    }
}

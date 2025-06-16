<?php
/**
* Created by Claudio Campos.
* User: callcocam@gmail.com, contato@sigasmart.com.br
* https://www.sigasmart.com.br
*/
namespace Callcocam\ReactPapaLeguas\Landlord\Facades;

use Callcocam\ReactPapaLeguas\Landlord\TenantManager;
use Illuminate\Support\Facades\Facade;

class Landlord extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return TenantManager::class;
    }
}
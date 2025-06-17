<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Callcocam\ReactPapaLeguas\Examples\TenantTableExample;

class TenantController extends LandlordController
{
    public function index()
    {
        dd(TenantTableExample::create());
        return ;
    }

}
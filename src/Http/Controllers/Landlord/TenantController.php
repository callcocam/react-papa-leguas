<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Callcocam\ReactPapaLeguas\Models\Tenant;

class TenantController extends LandlordController
{
    public function __construct()
    {
        $this->model(Tenant::class); 
    }
}
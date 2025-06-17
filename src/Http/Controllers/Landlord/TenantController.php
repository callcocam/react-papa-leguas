<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Callcocam\ReactPapaLeguas\Examples\CompleteTableExample;

class TenantController extends LandlordController
{
    public function index()
    {
        return CompleteTableExample::createUsersTable();
    }

}
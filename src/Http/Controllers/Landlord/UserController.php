<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use App\Models\User;

class UserController extends LandlordController
{
    public function __construct()
    {
        $this->model(User::class); 
    }

    
}
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Views;

class CardView extends View{

    public function __construct(string $id, string $label)
    {
        parent::__construct($id, $label);
        $this->icon('Grid');
    }
}
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table;

use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToName; 

class View
{
    use BelongsToName;
    

    protected string $id;
    protected string $label;
    protected string $icon;
    protected string $description;
    protected array $config;

    public function __construct(string $id){
        $this->id = $id;
    }



    public function toArray(): array
    {
        return [
            'id' => $this->id,
        ];
    }
}
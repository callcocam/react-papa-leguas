<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions;

use Callcocam\ReactPapaLeguas\Core;

class Action
{
    use Core\Concerns\EvaluatesClosures;
    use Core\Concerns\BelongsToName;
    use Core\Concerns\BelongsToLabel;

    public function __construct(
        protected string $name 
    ) {
    }

    public static function make(string $name): static
    {
        return new static($name);
    }
    
    public function toArray(): array
    {
        return [
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'hiddenLabel' => $this->isHiddenLabel(),
        ];
    }
     
}
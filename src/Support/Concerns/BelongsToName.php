<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToName
{
    protected Closure|string|null $name = null;

    public function name(string|Closure $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getName(): string
    {
        return $this->evaluate($this->name ?? $this->key);
    }
}

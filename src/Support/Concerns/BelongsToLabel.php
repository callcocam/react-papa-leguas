<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToLabel{
    protected Closure|string $label;

    public function label(Closure|string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function labelUsing(Closure $callback): self
    {
        $this->label = $callback;
        return $this;
    }

    public function getLabel(): string
    {
        return $this->evaluate($this->label);
    }

    public function hasLabel(): bool
    {
        return $this->label !== null;
    }
}
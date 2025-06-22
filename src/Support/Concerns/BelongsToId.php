<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToId{
    
    protected Closure|string $id;

    public function id(Closure|string $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getId(): Closure|string
    {
        return $this->evaluate($this->id, $this->context);
    }

    public function hasId(): bool
    {
        return $this->id !== null;
    }

    public function idUsing(Closure $callback): self
    {
        $this->id = $callback;
        return $this;
    }
}
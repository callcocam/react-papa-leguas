<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToIcon{

    protected Closure|string $icon;

    public function icon(Closure|string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function iconUsing(Closure $callback): self
    {
        $this->icon = $callback;
        return $this;
    }

    public function getIcon(): Closure|string
    {
        return $this->evaluate($this->icon);
    }

    public function hasIcon(): bool
    {
        return $this->icon !== null;
    }
}
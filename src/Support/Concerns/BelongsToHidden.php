<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToHidden
{
    protected Closure|bool $hidden = false;

    /**
     * Define se está oculto
     */
    public function hidden(Closure|bool $hidden = true): self
    {
        $this->hidden = $hidden;
        return $this;
    }

    /**
     * Define visibilidade usando callback
     */
    public function hiddenUsing(Closure $callback): self
    {
        $this->hidden = $callback;
        return $this;
    }

    /**
     * Atalho para definir como visível
     */
    public function visible(bool $visible = true): self
    {
        $this->hidden = !$visible;
        return $this;
    }

    /**
     * Define visibilidade usando callback
     */
    public function visibleUsing(Closure $callback): self
    {
        $this->hidden = function (...$args) use ($callback) {
            return !$this->evaluate($callback, $this->context ?? [], ...$args);
        };
        return $this;
    }

    /**
     * Verifica se está oculto
     */
    public function isHidden(): bool
    {
        return $this->evaluate($this->hidden, $this->context ?? []);
    }

    /**
     * Verifica se está visível
     */
    public function isVisible(): bool
    {
        return !$this->isHidden();
    }
} 
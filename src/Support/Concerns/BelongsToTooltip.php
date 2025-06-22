<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToTooltip
{
    protected Closure|string|null $tooltip = null;

    /**
     * Define o tooltip
     */
    public function tooltip(Closure|string $tooltip): self
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    /**
     * Define o tooltip usando callback
     */
    public function tooltipUsing(Closure $callback): self
    {
        $this->tooltip = $callback;
        return $this;
    }

    /**
     * Obtém o tooltip avaliado
     */
    public function getTooltip(): ?string
    {
        if ($this->tooltip === null) {
            return null;
        }

        return $this->evaluate($this->tooltip, $this->context ?? []);
    }

    /**
     * Verifica se tem tooltip definido
     */
    public function hasTooltip(): bool
    {
        return $this->tooltip !== null;
    }
} 
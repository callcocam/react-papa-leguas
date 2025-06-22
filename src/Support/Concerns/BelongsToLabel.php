<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToLabel
{
    protected Closure|string|null $label = null;

    /**
     * Define o label
     */
    public function label(Closure|string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Define o label usando callback
     */
    public function labelUsing(Closure $callback): self
    {
        $this->label = $callback;
        return $this;
    }

    /**
     * Obtém o label avaliado
     */
    public function getLabel(): ?string
    {
        if ($this->label === null) {
            return null;
        }

        return $this->evaluate($this->label, $this->context ?? []);
    }

    /**
     * Verifica se tem label definido
     */
    public function hasLabel(): bool
    {
        return $this->label !== null;
    }
}
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToPlaceholder
{
    protected Closure|string|null $placeholder = null;

    /**
     * Define o placeholder
     */
    public function placeholder(Closure|string $placeholder): self
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Define o placeholder usando callback
     */
    public function placeholderUsing(Closure $callback): self
    {
        $this->placeholder = $callback;
        return $this;
    }

    /**
     * Obtém o placeholder avaliado
     */
    public function getPlaceholder(): ?string
    {
        if ($this->placeholder === null) {
            return null;
        }

        return $this->evaluate($this->placeholder, $this->context ?? []);
    }

    /**
     * Verifica se tem placeholder definido
     */
    public function hasPlaceholder(): bool
    {
        return $this->placeholder !== null;
    }
} 
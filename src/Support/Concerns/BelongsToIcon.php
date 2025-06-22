<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToIcon
{
    protected Closure|string|null $icon = null;

    /**
     * Define o ícone
     */
    public function icon(Closure|string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Define o ícone usando callback
     */
    public function iconUsing(Closure $callback): self
    {
        $this->icon = $callback;
        return $this;
    }

    /**
     * Obtém o ícone avaliado
     */
    public function getIcon(): ?string
    {
        if ($this->icon === null) {
            return null;
        }

        return $this->evaluate($this->icon, $this->context ?? []);
    }

    /**
     * Verifica se tem ícone definido
     */
    public function hasIcon(): bool
    {
        return $this->icon !== null;
    }
} 
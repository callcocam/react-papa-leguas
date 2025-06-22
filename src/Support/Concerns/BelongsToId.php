<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToId
{
    protected Closure|string|null $id = null;

    /**
     * Define o ID
     */
    public function id(Closure|string $id): self
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Define o ID usando callback
     */
    public function idUsing(Closure $callback): self
    {
        $this->id = $callback;
        return $this;
    }

    /**
     * Obtém o ID avaliado
     */
    public function getId(): ?string
    {
        if ($this->id === null) {
            return null;
        }

        return $this->evaluate($this->id, $this->context ?? []);
    }

    /**
     * Verifica se tem ID definido
     */
    public function hasId(): bool
    {
        return $this->id !== null;
    }
}
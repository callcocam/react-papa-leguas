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

    /**
     * Define o nome
     */
    public function name(string|Closure $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Define o nome usando callback
     */
    public function nameUsing(Closure $callback): self
    {
        $this->name = $callback;
        return $this;
    }

    /**
     * Obtém o nome avaliado, usando key como fallback
     */
    public function getName(): string
    {
        $name = $this->name ?? ($this->key ?? 'unnamed');
        return $this->evaluate($name, $this->context ?? []);
    }

    /**
     * Verifica se tem nome definido (não considera fallback)
     */
    public function hasName(): bool
    {
        return $this->name !== null;
    }
}

<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToDisabled
{
    protected Closure|bool $disabled = false;

    /**
     * Define se está desabilitado
     */
    public function disabled(Closure|bool $disabled = true): self
    {
        $this->disabled = $disabled;
        return $this;
    }

    /**
     * Define se está desabilitado usando callback
     */
    public function disabledUsing(Closure $callback): self
    {
        $this->disabled = $callback;
        return $this;
    }

    /**
     * Atalho para definir como habilitado
     */
    public function enabled(bool $enabled = true): self
    {
        $this->disabled = !$enabled;
        return $this;
    }

    /**
     * Define se está habilitado usando callback
     */
    public function enabledUsing(Closure $callback): self
    {
        $this->disabled = function (...$args) use ($callback) {
            return !$this->evaluate($callback, $this->context ?? [], ...$args);
        };
        return $this;
    }

    /**
     * Verifica se está desabilitado
     */
    public function isDisabled(): bool
    {
        return $this->evaluate($this->disabled, $this->context ?? []);
    }

    /**
     * Verifica se está habilitado
     */
    public function isEnabled(): bool
    {
        return !$this->isDisabled();
    }
} 
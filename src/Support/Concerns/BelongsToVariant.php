<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToVariant
{
    protected Closure|string $variant = 'default';

    /**
     * Define a variante de estilo
     */
    public function variant(Closure|string $variant): self
    {
        $this->variant = $variant;
        return $this;
    }

    /**
     * Define a variante usando callback
     */
    public function variantUsing(Closure $callback): self
    {
        $this->variant = $callback;
        return $this;
    }

    /**
     * Atalhos para variantes comuns
     */
    public function primary(): self
    {
        return $this->variant('primary');
    }

    public function secondary(): self
    {
        return $this->variant('secondary');
    }

    public function success(): self
    {
        return $this->variant('success');
    }

    public function danger(): self
    {
        return $this->variant('danger');
    }

    public function warning(): self
    {
        return $this->variant('warning');
    }

    public function info(): self
    {
        return $this->variant('info');
    }

    public function outline(): self
    {
        return $this->variant('outline');
    }

    public function ghost(): self
    {
        return $this->variant('ghost');
    }

    public function link(): self
    {
        return $this->variant('link');
    }

    /**
     * Obtém a variante avaliada
     */
    public function getVariant(): string
    {
        return $this->evaluate($this->variant, $this->context ?? []);
    }

    /**
     * Verifica se tem variante específica
     */
    public function hasVariant(string $variant): bool
    {
        return $this->getVariant() === $variant;
    }

    /**
     * Verifica se é variante de perigo/erro
     */
    public function isDanger(): bool
    {
        return in_array($this->getVariant(), ['danger', 'destructive', 'error']);
    }

    /**
     * Verifica se é variante primária
     */
    public function isPrimary(): bool
    {
        return $this->getVariant() === 'primary';
    }
} 
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToSize
{
    protected Closure|string $size = 'md';

    /**
     * Define o tamanho
     */
    public function size(Closure|string $size): self
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Define o tamanho usando callback
     */
    public function sizeUsing(Closure $callback): self
    {
        $this->size = $callback;
        return $this;
    }

    /**
     * Atalhos para tamanhos comuns
     */
    public function xs(): self
    {
        return $this->size('xs');
    }

    public function sm(): self
    {
        return $this->size('sm');
    }

    public function md(): self
    {
        return $this->size('md');
    }

    public function lg(): self
    {
        return $this->size('lg');
    }

    public function xl(): self
    {
        return $this->size('xl');
    }

    public function small(): self
    {
        return $this->size('sm');
    }

    public function medium(): self
    {
        return $this->size('md');
    }

    public function large(): self
    {
        return $this->size('lg');
    }

    /**
     * Obtém o tamanho avaliado
     */
    public function getSize(): string
    {
        return $this->evaluate($this->size, $this->context ?? []);
    }

    /**
     * Verifica se tem tamanho específico
     */
    public function hasSize(string $size): bool
    {
        return $this->getSize() === $size;
    }

    /**
     * Verifica se é tamanho pequeno
     */
    public function isSmall(): bool
    {
        return in_array($this->getSize(), ['xs', 'sm', 'small']);
    }

    /**
     * Verifica se é tamanho grande
     */
    public function isLarge(): bool
    {
        return in_array($this->getSize(), ['lg', 'xl', 'large']);
    }

    /**
     * Verifica se é tamanho médio
     */
    public function isMedium(): bool
    {
        return in_array($this->getSize(), ['md', 'medium']);
    }
} 
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToOrder
{
    protected Closure|int $order = 0;

    /**
     * Define a ordem/prioridade
     */
    public function order(Closure|int $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Define a ordem usando callback
     */
    public function orderUsing(Closure $callback): self
    {
        $this->order = $callback;
        return $this;
    }

    /**
     * Atalhos para prioridades comuns
     */
    public function first(): self
    {
        return $this->order(-1000);
    }

    public function last(): self
    {
        return $this->order(1000);
    }

    public function high(): self
    {
        return $this->order(-100);
    }

    public function low(): self
    {
        return $this->order(100);
    }

    public function priority(int $priority): self
    {
        return $this->order($priority);
    }

    /**
     * Define ordem antes de outro elemento
     */
    public function before(int $referenceOrder): self
    {
        return $this->order($referenceOrder - 1);
    }

    /**
     * Define ordem depois de outro elemento
     */
    public function after(int $referenceOrder): self
    {
        return $this->order($referenceOrder + 1);
    }

    /**
     * Obtém a ordem avaliada
     */
    public function getOrder(): int
    {
        return (int) $this->evaluate($this->order, $this->context ?? []);
    }

    /**
     * Verifica se é primeira posição
     */
    public function isFirst(): bool
    {
        return $this->getOrder() < 0;
    }

    /**
     * Verifica se é última posição
     */
    public function isLast(): bool
    {
        return $this->getOrder() > 900;
    }

    /**
     * Verifica se tem alta prioridade
     */
    public function isHighPriority(): bool
    {
        return $this->getOrder() <= -100;
    }

    /**
     * Verifica se tem baixa prioridade
     */
    public function isLowPriority(): bool
    {
        return $this->getOrder() >= 100;
    }

    /**
     * Compara ordem com outro elemento
     */
    public function compareOrder(BelongsToOrder|int $other): int
    {
        $thisOrder = $this->getOrder();
        $otherOrder = is_int($other) ? $other : $other->getOrder();
        
        return $thisOrder <=> $otherOrder;
    }
} 
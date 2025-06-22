<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToGroup
{
    protected Closure|string|null $group = null;

    /**
     * Define o grupo
     */
    public function group(Closure|string $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Define o grupo usando callback
     */
    public function groupUsing(Closure $callback): self
    {
        $this->group = $callback;
        return $this;
    }

    /**
     * Remove do grupo (define como null)
     */
    public function ungrouped(): self
    {
        $this->group = null;
        return $this;
    }

    /**
     * Atalhos para grupos comuns
     */
    public function mainGroup(): self
    {
        return $this->group('main');
    }

    public function secondaryGroup(): self
    {
        return $this->group('secondary');
    }

    public function actionsGroup(): self
    {
        return $this->group('actions');
    }

    public function filtersGroup(): self
    {
        return $this->group('filters');
    }

    public function navigationGroup(): self
    {
        return $this->group('navigation');
    }

    public function toolsGroup(): self
    {
        return $this->group('tools');
    }

    /**
     * Obtém o grupo avaliado
     */
    public function getGroup(): ?string
    {
        if ($this->group === null) {
            return null;
        }

        return $this->evaluate($this->group, $this->context ?? []);
    }

    /**
     * Verifica se tem grupo definido
     */
    public function hasGroup(): bool
    {
        return $this->group !== null;
    }

    /**
     * Verifica se pertence a um grupo específico
     */
    public function belongsToGroup(string $group): bool
    {
        return $this->getGroup() === $group;
    }

    /**
     * Verifica se pertence ao grupo principal
     */
    public function isMainGroup(): bool
    {
        return $this->belongsToGroup('main');
    }

    /**
     * Verifica se pertence ao grupo secundário
     */
    public function isSecondaryGroup(): bool
    {
        return $this->belongsToGroup('secondary');
    }

    /**
     * Verifica se não tem grupo (ungrouped)
     */
    public function isUngrouped(): bool
    {
        return $this->getGroup() === null;
    }
} 
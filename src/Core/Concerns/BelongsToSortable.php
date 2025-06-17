<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToSortable
{
    /**
     * Whether the component is sortable.
     *
     * @var bool
     */
    protected Closure|bool $sortable = false;

    /**
     * The sort direction.
     *
     * @var string|null
     */
    protected ?string $sortDirection = null;

    /**
     * Set the component as sortable.
     *
     * @param Closure|bool $condition
     * @param string|null $direction
     * @return $this
     */
    public function sortable(Closure|bool $condition = true, ?string $direction = null): static
    {
        $this->sortable = $condition;
        $this->sortDirection = $direction;

        return $this;
    }

    /**
     * Check if the component is sortable.
     *
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->evaluate($this->sortable);
    }

    /**
     * Get the sort direction.
     *
     * @return string|null
     */
    public function getSortDirection(): ?string
    {
        return $this->sortDirection;
    }
}

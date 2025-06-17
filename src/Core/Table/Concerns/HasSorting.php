<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Concerns;

trait HasSorting
{
    /**
     * Default sort column.
     *
     * @var string|null
     */
    protected ?string $defaultSortColumn = null;

    /**
     * Default sort direction.
     *
     * @var string
     */
    protected string $defaultSortDirection = 'asc';

    /**
     * Whether sorting is enabled.
     *
     * @var bool
     */
    protected bool $sortable = true;

    /**
     * Set default sort column and direction.
     *
     * @param string $column
     * @param string $direction
     * @return static
     */
    public function defaultSort(string $column, string $direction = 'asc'): static
    {
        $this->defaultSortColumn = $column;
        $this->defaultSortDirection = $direction;

        return $this;
    }

    /**
     * Enable or disable sorting.
     *
     * @param bool $sortable
     * @return static
     */
    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;

        return $this;
    }

    /**
     * Disable sorting.
     *
     * @return static
     */
    public function withoutSorting(): static
    {
        return $this->sortable(false);
    }

    /**
     * Get default sort column.
     *
     * @return string|null
     */
    public function getDefaultSortColumn(): ?string
    {
        return $this->defaultSortColumn;
    }

    /**
     * Get default sort direction.
     *
     * @return string
     */
    public function getDefaultSortDirection(): string
    {
        return $this->defaultSortDirection;
    }

    /**
     * Check if sorting is enabled.
     *
     * @return bool
     */
    public function isSortable(): bool
    {
        return $this->sortable;
    }

    /**
     * Apply sorting to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $sortColumn
     * @param string|null $sortDirection
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applySorting($query, ?string $sortColumn = null, ?string $sortDirection = null)
    {
        if (!$this->isSortable()) {
            return $query;
        }

        $column = $sortColumn ?? $this->getDefaultSortColumn();
        $direction = $sortDirection ?? $this->getDefaultSortDirection();

        if ($column) {
            // Validate direction
            $direction = in_array(strtolower($direction), ['asc', 'desc']) ? $direction : 'asc';
            
            // Check if column is sortable
            if ($this->isColumnSortable($column)) {
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }

    /**
     * Check if a column is sortable.
     *
     * @param string $column
     * @return bool
     */
    protected function isColumnSortable(string $column): bool
    {
        if (!$this->isSortable()) {
            return false;
        }

        // If we have columns defined, check if the column is marked as sortable
        if (!empty($this->columns)) {
            foreach ($this->columns as $tableColumn) {
                if ($tableColumn['key'] === $column) {
                    return $tableColumn['sortable'] ?? true;
                }
            }
            return false;
        }

        // If no columns defined, allow sorting on any column
        return true;
    }

    /**
     * Get sorting data for frontend.
     *
     * @param string|null $currentSortColumn
     * @param string|null $currentSortDirection
     * @return array
     */
    public function getSortingData(?string $currentSortColumn = null, ?string $currentSortDirection = null): array
    {
        return [
            'sortable' => $this->isSortable(),
            'defaultSortColumn' => $this->getDefaultSortColumn(),
            'defaultSortDirection' => $this->getDefaultSortDirection(),
            'currentSortColumn' => $currentSortColumn,
            'currentSortDirection' => $currentSortDirection,
        ];
    }
}

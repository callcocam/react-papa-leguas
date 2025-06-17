<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Concerns;

trait HasSearch
{
    /**
     * Whether to show search.
     *
     * @var bool
     */
    protected bool $searchable = true;

    /**
     * Search placeholder text.
     *
     * @var string
     */
    protected string $searchPlaceholder = 'Buscar...';

    /**
     * Columns to search in.
     *
     * @var array|null
     */
    protected ?array $searchColumns = null;

    /**
     * Enable or disable search.
     *
     * @param bool $searchable
     * @return static
     */
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;

        return $this;
    }

    /**
     * Disable search.
     *
     * @return static
     */
    public function withoutSearch(): static
    {
        return $this->searchable(false);
    }

    /**
     * Set search placeholder.
     *
     * @param string $placeholder
     * @return static
     */
    public function searchPlaceholder(string $placeholder): static
    {
        $this->searchPlaceholder = $placeholder;

        return $this;
    }

    /**
     * Set specific columns to search in.
     *
     * @param array $columns
     * @return static
     */
    public function searchColumns(array $columns): static
    {
        $this->searchColumns = $columns;

        return $this;
    }

    /**
     * Check if search is enabled.
     *
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    /**
     * Get search placeholder.
     *
     * @return string
     */
    public function getSearchPlaceholder(): string
    {
        return $this->searchPlaceholder;
    }

    /**
     * Get searchable columns.
     *
     * @return array
     */
    public function getSearchColumns(): array
    {
        if ($this->searchColumns !== null) {
            return $this->searchColumns;
        }

        // If no specific search columns set, get from table columns
        if (!empty($this->columns)) {
            return array_column(
                array_filter($this->columns, function ($column) {
                    return $column['searchable'] ?? true;
                }),
                'key'
            );
        }

        return [];
    }

    /**
     * Apply search to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applySearch($query, ?string $search = null)
    {
        if (!$this->isSearchable() || empty($search)) {
            return $query;
        }

        $searchColumns = $this->getSearchColumns();

        if (empty($searchColumns)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $searchColumns) {
            foreach ($searchColumns as $column) {
                $q->orWhere($column, 'like', "%{$search}%");
            }
        });
    }

    /**
     * Get search data for frontend.
     *
     * @param string|null $currentSearch
     * @return array
     */
    public function getSearchData(?string $currentSearch = null): array
    {
        return [
            'searchable' => $this->isSearchable(),
            'searchPlaceholder' => $this->getSearchPlaceholder(),
            'searchColumns' => $this->getSearchColumns(),
            'currentSearch' => $currentSearch,
        ];
    }
}

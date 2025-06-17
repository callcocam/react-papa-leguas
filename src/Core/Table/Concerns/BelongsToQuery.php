<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Concerns;

use Closure;

trait BelongsToQuery
{
    /**
     * The query callback for modifying the query.
     *
     * @var Closure|null
     */
    protected ?Closure $queryCallback = null;

    /**
     * The default sorting column.
     *
     * @var string|null
     */
    protected ?string $defaultSort = null;

    /**
     * The default sorting direction.
     *
     * @var string
     */
    protected string $defaultSortDirection = 'asc';

    /**
     * Whether to enable pagination.
     *
     * @var bool
     */
    protected bool $paginated = true;

    /**
     * The number of records per page.
     *
     * @var int
     */
    protected int $perPage = 15;

    /**
     * The available per page options.
     *
     * @var array
     */
    protected array $perPageOptions = [10, 15, 25, 50, 100];

    /**
     * Set a query callback to modify the query.
     *
     * @param Closure $callback
     * @return $this
     */
    public function query(Closure $callback): static
    {
        $this->queryCallback = $callback;

        return $this;
    }

    /**
     * Set the default sorting.
     *
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function defaultSort(string $column, string $direction = 'asc'): static
    {
        $this->defaultSort = $column;
        $this->defaultSortDirection = $direction;

        return $this;
    }

    /**
     * Enable or disable pagination.
     *
     * @param bool $paginated
     * @return $this
     */
    public function paginated(bool $paginated = true): static
    {
        $this->paginated = $paginated;

        return $this;
    }

    /**
     * Set the number of records per page.
     *
     * @param int $perPage
     * @return $this
     */
    public function perPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * Set the available per page options.
     *
     * @param array $options
     * @return $this
     */
    public function perPageOptions(array $options): static
    {
        $this->perPageOptions = $options;

        return $this;
    }

    /**
     * Get the query callback.
     *
     * @return Closure|null
     */
    public function getQueryCallback(): ?Closure
    {
        return $this->queryCallback;
    }

    /**
     * Get the default sort column.
     *
     * @return string|null
     */
    public function getDefaultSort(): ?string
    {
        return $this->defaultSort;
    }

    /**
     * Get the default sort direction.
     *
     * @return string
     */
    public function getDefaultSortDirection(): string
    {
        return $this->defaultSortDirection;
    }

    /**
     * Check if pagination is enabled.
     *
     * @return bool
     */
    public function isPaginated(): bool
    {
        return $this->paginated;
    }

    /**
     * Get the number of records per page.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get the available per page options.
     *
     * @return array
     */
    public function getPerPageOptions(): array
    {
        return $this->perPageOptions;
    }

    /**
     * Apply the query modifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyQueryModifications($query)
    {
        if ($this->queryCallback) {
            $query = call_user_func($this->queryCallback, $query);
        }

        if ($this->defaultSort) {
            $query->orderBy($this->defaultSort, $this->defaultSortDirection);
        }

        return $query;
    }
}

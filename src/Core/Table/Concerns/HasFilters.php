<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Concerns;

use Callcocam\ReactPapaLeguas\Core\Table\Filters\Filter;
use Callcocam\ReactPapaLeguas\Core\Table\Filters\TextFilter;
use Callcocam\ReactPapaLeguas\Core\Table\Filters\SelectFilter;
use Callcocam\ReactPapaLeguas\Core\Table\Filters\DateFilter;
use Callcocam\ReactPapaLeguas\Core\Table\Filters\DateRangeFilter;
use Callcocam\ReactPapaLeguas\Core\Table\Filters\NumberFilter;
use Callcocam\ReactPapaLeguas\Core\Table\Filters\BooleanFilter;

trait HasFilters
{
    /**
     * The table filters.
     *
     * @var array
     */
    protected array $filters = [];

    /**
     * Add a filter to the table.
     *
     * @param Filter $filter
     * @return static
     */
    public function filter(Filter $filter): static
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Add multiple filters to the table.
     *
     * @param array $filters
     * @return static
     */
    public function filters(array $filters): static
    {
        foreach ($filters as $filter) {
            $this->filter($filter);
        }
        return $this;
    }

    /**
     * Add a text filter.
     *
     * @param string $key
     * @param string|null $label
     * @return TextFilter
     */
    public function textFilter(string $key, ?string $label = null): TextFilter
    {
        $filter = TextFilter::make($key, $label);
        $this->filter($filter);
        return $filter;
    }

    /**
     * Add a select filter.
     *
     * @param string $key
     * @param string|null $label
     * @return SelectFilter
     */
    public function selectFilter(string $key, ?string $label = null): SelectFilter
    {
        $filter = SelectFilter::make($key, $label);
        $this->filter($filter);
        return $filter;
    }

    /**
     * Add a multiselect filter.
     *
     * @param string $key
     * @param string|null $label
     * @return SelectFilter
     */
    public function multiselectFilter(string $key, ?string $label = null): SelectFilter
    {
        $filter = SelectFilter::make($key, $label)->multiple();
        $this->filter($filter);
        return $filter;
    }

    /**
     * Add a date filter.
     *
     * @param string $key
     * @param string|null $label
     * @return DateFilter
     */
    public function dateFilter(string $key, ?string $label = null): DateFilter
    {
        $filter = DateFilter::make($key, $label);
        $this->filter($filter);
        return $filter;
    }

    /**
     * Add a date range filter.
     *
     * @param string $key
     * @param string|null $label
     * @return DateRangeFilter
     */
    public function dateRangeFilter(string $key, ?string $label = null): DateRangeFilter
    {
        $filter = DateRangeFilter::make($key, $label);
        $this->filter($filter);
        return $filter;
    }

    /**
     * Add a number filter.
     *
     * @param string $key
     * @param string|null $label
     * @return NumberFilter
     */
    public function numberFilter(string $key, ?string $label = null): NumberFilter
    {
        $filter = NumberFilter::make($key, $label);
        $this->filter($filter);
        return $filter;
    }

    /**
     * Add a boolean filter.
     *
     * @param string $key
     * @param string|null $label
     * @return BooleanFilter
     */
    public function booleanFilter(string $key, ?string $label = null): BooleanFilter
    {
        $filter = BooleanFilter::make($key, $label);
        $this->filter($filter);
        return $filter;
    }

    /**
     * Get the table filters.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return array_map(function ($filter) {
            return $filter->toArray();
        }, $this->filters);
    }

    /**
     * Apply filters to the query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $filterValues
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function applyFilters($query, array $filterValues = [])
    {
        foreach ($this->filters as $filter) {
            $key = $filter->getKey();
            $value = $filterValues[$key] ?? null;

            if ($value !== null && $value !== '' && (!is_array($value) || !empty($value))) {
                $filter->apply($query, $value);
            }
        }

        return $query;
    }
}

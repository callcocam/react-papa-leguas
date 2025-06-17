<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Concerns;

trait HasPagination
{
    /**
     * Items per page.
     *
     * @var int
     */
    protected int $perPage = 15;

    /**
     * Available per page options.
     *
     * @var array
     */
    protected array $perPageOptions = [10, 15, 25, 50, 100];

    /**
     * Whether to show pagination.
     *
     * @var bool
     */
    protected bool $paginated = true;

    /**
     * Set items per page.
     *
     * @param int $perPage
     * @return static
     */
    public function perPage(int $perPage): static
    {
        $this->perPage = $perPage;

        return $this;
    }

    /**
     * Set per page options.
     *
     * @param array $options
     * @return static
     */
    public function perPageOptions(array $options): static
    {
        $this->perPageOptions = $options;

        return $this;
    }

    /**
     * Enable or disable pagination.
     *
     * @param bool $paginated
     * @return static
     */
    public function paginated(bool $paginated = true): static
    {
        $this->paginated = $paginated;

        return $this;
    }

    /**
     * Disable pagination.
     *
     * @return static
     */
    public function withoutPagination(): static
    {
        return $this->paginated(false);
    }

    /**
     * Get items per page.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Get per page options.
     *
     * @return array
     */
    public function getPerPageOptions(): array
    {
        return $this->perPageOptions;
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
     * Get pagination data from query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $perPage
     * @return array
     */
    public function getPaginationData($query, ?int $perPage = null): array
    {
        if (!$this->isPaginated()) {
            return [
                'data' => $query->get()->toArray(),
                'pagination' => null,
            ];
        }

        $perPage = $perPage ?? $this->getPerPage();
        $paginated = $query->paginate($perPage);

        return [
            'data' => $paginated->items(),
            'pagination' => [
                'current_page' => $paginated->currentPage(),
                'from' => $paginated->firstItem(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'to' => $paginated->lastItem(),
                'total' => $paginated->total(),
                'links' => $paginated->linkCollection()->toArray(),
                'path' => $paginated->path(),
                'first_page_url' => $paginated->url(1),
                'last_page_url' => $paginated->url($paginated->lastPage()),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ],
        ];
    }
}

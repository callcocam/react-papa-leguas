<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\DataSources;

use Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;
use Callcocam\ReactPapaLeguas\Support\Table\DataSources\Contracts\DataSourceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

abstract class DataSource implements DataSourceInterface
{
    use EvaluatesClosures;

    protected array $filters = [];
    protected ?string $search = null;
    protected array $searchableColumns = [];
    protected ?string $sortColumn = null;
    protected string $sortDirection = 'asc';
    protected array $config = [];
    protected bool $cacheEnabled = false;
    protected int $cacheTtl = 300; // 5 minutos
    protected ?string $cacheKey = null;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
        $this->cacheEnabled = $this->config['cache_enabled'] ?? false;
        $this->cacheTtl = $this->config['cache_ttl'] ?? 300;
    }

    /**
     * Aplicar filtros aos dados
     */
    public function applyFilters(array $filters): static
    {
        $this->filters = array_merge($this->filters, $filters);
        $this->clearCache();
        return $this;
    }

    /**
     * Aplicar busca aos dados
     */
    public function applySearch(string $search, array $searchableColumns = []): static
    {
        $this->search = $search;
        $this->searchableColumns = $searchableColumns;
        $this->clearCache();
        return $this;
    }

    /**
     * Aplicar ordenação aos dados
     */
    public function applySorting(string $column, string $direction = 'asc'): static
    {
        $this->sortColumn = $column;
        $this->sortDirection = strtolower($direction) === 'desc' ? 'desc' : 'asc';
        $this->clearCache();
        return $this;
    }

    /**
     * Obter configurações da fonte
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Limpar cache da fonte
     */
    public function clearCache(): static
    {
        if ($this->cacheEnabled && $this->cacheKey) {
            Cache::forget($this->cacheKey);
        }
        return $this;
    }

    /**
     * Obter informações de debug da fonte
     */
    public function getDebugInfo(): array
    {
        return [
            'type' => $this->getType(),
            'config' => $this->config,
            'filters' => $this->filters,
            'search' => $this->search,
            'searchable_columns' => $this->searchableColumns,
            'sort_column' => $this->sortColumn,
            'sort_direction' => $this->sortDirection,
            'cache_enabled' => $this->cacheEnabled,
            'cache_key' => $this->cacheKey,
            'is_available' => $this->isAvailable(),
            'supports' => [
                'pagination' => $this->supportsPagination(),
                'search' => $this->supportsSearch(),
                'sorting' => $this->supportsSorting(),
                'filters' => $this->supportsFilters(),
            ],
        ];
    }

    /**
     * Obter dados com cache (se habilitado)
     */
    protected function getCachedData(string $method, array $params = []): mixed
    {
        if (!$this->cacheEnabled) {
            return $this->executeMethod($method, $params);
        }

        $cacheKey = $this->generateCacheKey($method, $params);
        $this->cacheKey = $cacheKey;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($method, $params) {
            return $this->executeMethod($method, $params);
        });
    }

    /**
     * Executar método da fonte
     */
    protected function executeMethod(string $method, array $params = []): mixed
    {
        try {
            return $this->{$method}(...$params);
        } catch (\Exception $e) {
            Log::error("Erro ao executar método {$method} na fonte {$this->getType()}", [
                'error' => $e->getMessage(),
                'params' => $params,
                'config' => $this->config,
            ]);
            throw $e;
        }
    }

    /**
     * Gerar chave de cache
     */
    protected function generateCacheKey(string $method, array $params = []): string
    {
        $data = [
            'type' => $this->getType(),
            'method' => $method,
            'params' => $params,
            'filters' => $this->filters,
            'search' => $this->search,
            'sort' => [$this->sortColumn, $this->sortDirection],
            'config' => $this->config,
        ];

        return 'table_data_source_' . md5(serialize($data));
    }

    /**
     * Aplicar filtros a uma collection
     */
    protected function applyFiltersToCollection(Collection $data): Collection
    {
        if (empty($this->filters)) {
            return $data;
        }

        return $data->filter(function ($item) {
            foreach ($this->filters as $column => $value) {
                if (is_null($value) || $value === '') {
                    continue;
                }

                $itemValue = $this->getItemValue($item, $column);

                if (is_array($value)) {
                    if (!in_array($itemValue, $value)) {
                        return false;
                    }
                } else {
                    if ($itemValue != $value) {
                        return false;
                    }
                }
            }
            return true;
        });
    }

    /**
     * Aplicar busca a uma collection
     */
    protected function applySearchToCollection(Collection $data): Collection
    {
        if (!$this->search || empty($this->searchableColumns)) {
            return $data;
        }

        $search = strtolower($this->search);

        return $data->filter(function ($item) use ($search) {
            foreach ($this->searchableColumns as $column) {
                $value = strtolower((string) $this->getItemValue($item, $column));
                if (str_contains($value, $search)) {
                    return true;
                }
            }
            return false;
        });
    }

    /**
     * Aplicar ordenação a uma collection
     */
    protected function applySortingToCollection(Collection $data): Collection
    {
        if (!$this->sortColumn) {
            return $data;
        }

        return $data->sortBy(function ($item) {
            return $this->getItemValue($item, $this->sortColumn);
        }, SORT_REGULAR, $this->sortDirection === 'desc');
    }

    /**
     * Obter valor de um item (array ou objeto)
     */
    protected function getItemValue($item, string $key): mixed
    {
        if (is_array($item)) {
            return data_get($item, $key);
        }

        if (is_object($item)) {
            return data_get($item, $key);
        }

        return null;
    }

    /**
     * Paginar uma collection
     */
    protected function paginateCollection(Collection $data, int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $total = $data->count();
        $items = $data->forPage($page, $perPage)->values();

        return new \Illuminate\Pagination\LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    /**
     * Obter configuração padrão da fonte
     */
    protected function getDefaultConfig(): array
    {
        return [
            'cache_enabled' => false,
            'cache_ttl' => 300,
        ];
    }

    /**
     * Verificar se a fonte está disponível (implementação padrão)
     */
    public function isAvailable(): bool
    {
        return true;
    }

    // Métodos abstratos que devem ser implementados pelas classes filhas
    abstract public function getData(): Collection;
    abstract public function getType(): string;
} 
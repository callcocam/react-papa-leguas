<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\DataSources;

use Callcocam\ReactPapaLeguas\Support\Table\DataSources\DataSource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Closure;

class ModelSource extends DataSource
{
    protected string $modelClass;
    protected ?Closure $queryCallback = null;
    protected ?Builder $baseQuery = null;

    public function __construct(string $modelClass, array $config = [])
    {
        $this->modelClass = $modelClass;
        parent::__construct($config);
        
        $this->validateModel();
    }

    /**
     * Definir callback de query personalizada
     */
    public function query(Closure $callback): static
    {
        $this->queryCallback = $callback;
        $this->baseQuery = null; // Reset query cache
        $this->clearCache();
        return $this;
    }

    /**
     * Obter dados da fonte
     */
    public function getData(): Collection
    {
        return $this->getCachedData('getDataFromDatabase');
    }

    /**
     * Obter dados paginados
     */
    public function getPaginatedData(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->buildQuery();
        
        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Contar total de registros
     */
    public function count(): int
    {
        return $this->buildQuery()->count();
    }

    /**
     * Verificar se a fonte suporta paginação
     */
    public function supportsPagination(): bool
    {
        return true;
    }

    /**
     * Verificar se a fonte suporta busca
     */
    public function supportsSearch(): bool
    {
        return true;
    }

    /**
     * Verificar se a fonte suporta ordenação
     */
    public function supportsSorting(): bool
    {
        return true;
    }

    /**
     * Verificar se a fonte suporta filtros
     */
    public function supportsFilters(): bool
    {
        return true;
    }

    /**
     * Obter tipo da fonte de dados
     */
    public function getType(): string
    {
        return 'model';
    }

    /**
     * Verificar se a fonte está disponível
     */
    public function isAvailable(): bool
    {
        try {
            return class_exists($this->modelClass) && 
                   is_subclass_of($this->modelClass, Model::class);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obter configuração padrão da fonte
     */
    protected function getDefaultConfig(): array
    {
        return array_merge(parent::getDefaultConfig(), [
            'cache_enabled' => true,
            'cache_ttl' => 600, // 10 minutos para models
            'eager_load' => [],
            'select_columns' => ['*'],
        ]);
    }

    /**
     * Obter dados do banco de dados
     */
    protected function getDataFromDatabase(): Collection
    {
        return $this->buildQuery()->get();
    }

    /**
     * Construir query base
     */
    protected function buildQuery(): Builder
    {
        if ($this->baseQuery) {
            return clone $this->baseQuery;
        }

        $query = $this->getBaseQuery();
        
        // Aplicar eager loading se configurado
        if (!empty($this->config['eager_load'])) {
            $query->with($this->config['eager_load']);
        }
        
        // Aplicar seleção de colunas se configurado
        if ($this->config['select_columns'] !== ['*']) {
            $query->select($this->config['select_columns']);
        }
        
        // Aplicar filtros
        $query = $this->applyFiltersToQuery($query);
        
        // Aplicar busca
        $query = $this->applySearchToQuery($query);
        
        // Aplicar ordenação
        $query = $this->applySortingToQuery($query);
        
        $this->baseQuery = $query;
        
        return clone $query;
    }

    /**
     * Obter query base do modelo
     */
    protected function getBaseQuery(): Builder
    {
        if ($this->queryCallback) {
            $query = $this->evaluate($this->queryCallback);
            
            if (!$query instanceof Builder) {
                throw new \InvalidArgumentException(
                    'Query callback deve retornar uma instância de Builder'
                );
            }
            
            return $query;
        }

        return $this->modelClass::query();
    }

    /**
     * Aplicar filtros à query
     */
    protected function applyFiltersToQuery(Builder $query): Builder
    {
        if (empty($this->filters)) {
            return $query;
        }

        foreach ($this->filters as $column => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }

            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        return $query;
    }

    /**
     * Aplicar busca à query
     */
    protected function applySearchToQuery(Builder $query): Builder
    {
        if (!$this->search || empty($this->searchableColumns)) {
            return $query;
        }

        return $query->where(function (Builder $q) {
            foreach ($this->searchableColumns as $column) {
                $q->orWhere($column, 'LIKE', "%{$this->search}%");
            }
        });
    }

    /**
     * Aplicar ordenação à query
     */
    protected function applySortingToQuery(Builder $query): Builder
    {
        if (!$this->sortColumn) {
            return $query;
        }

        return $query->orderBy($this->sortColumn, $this->sortDirection);
    }

    /**
     * Validar se o modelo existe e é válido
     */
    protected function validateModel(): void
    {
        if (!class_exists($this->modelClass)) {
            throw new \InvalidArgumentException(
                "Modelo {$this->modelClass} não encontrado"
            );
        }

        if (!is_subclass_of($this->modelClass, Model::class)) {
            throw new \InvalidArgumentException(
                "Classe {$this->modelClass} deve estender Illuminate\\Database\\Eloquent\\Model"
            );
        }
    }

    /**
     * Obter informações de debug específicas do modelo
     */
    public function getDebugInfo(): array
    {
        $baseInfo = parent::getDebugInfo();
        
        return array_merge($baseInfo, [
            'model_class' => $this->modelClass,
            'has_query_callback' => $this->queryCallback !== null,
            'eager_load' => $this->config['eager_load'] ?? [],
            'select_columns' => $this->config['select_columns'] ?? ['*'],
            'query_sql' => $this->baseQuery ? $this->baseQuery->toSql() : null,
            'query_bindings' => $this->baseQuery ? $this->baseQuery->getBindings() : [],
        ]);
    }
}
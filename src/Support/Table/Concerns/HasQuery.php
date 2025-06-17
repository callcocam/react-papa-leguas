<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Closure;

/**
 * Trait para sistema de query avançado da tabela
 */
trait HasQuery
{
    /**
     * Configurações de query
     */
    protected array $queryConfig = [
        'enabled' => true,
        'eager_loading' => [],
        'scopes' => [],
        'joins' => [],
        'subqueries' => [],
        'raw_selects' => [],
        'group_by' => [],
        'having' => [],
        'search_columns' => [],
        'sortable_columns' => [],
        'default_sort' => null,
        'query_optimizations' => true,
    ];

    /**
     * Query callback customizada
     */
    protected ?Closure $customQueryCallback = null;

    /**
     * Habilitar sistema de query avançado
     */
    public function querySystem(bool $enabled = true): static
    {
        $this->queryConfig['enabled'] = $enabled;
        
        return $this;
    }

    /**
     * Definir eager loading
     */
    public function with(array $relations): static
    {
        $this->queryConfig['eager_loading'] = array_merge(
            $this->queryConfig['eager_loading'],
            $relations
        );
        
        return $this;
    }

    /**
     * Adicionar scope
     */
    public function scope(string $scope, ...$parameters): static
    {
        $this->queryConfig['scopes'][] = [$scope, $parameters];
        
        return $this;
    }

    /**
     * Adicionar join
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'inner'): static
    {
        $this->queryConfig['joins'][] = [$type, $table, $first, $operator, $second];
        
        return $this;
    }

    /**
     * Adicionar left join
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }

    /**
     * Adicionar right join
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): static
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }

    /**
     * Adicionar select raw
     */
    public function selectRaw(string $expression, array $bindings = []): static
    {
        $this->queryConfig['raw_selects'][] = [$expression, $bindings];
        
        return $this;
    }

    /**
     * Adicionar group by
     */
    public function groupBy(...$columns): static
    {
        $this->queryConfig['group_by'] = array_merge(
            $this->queryConfig['group_by'],
            $columns
        );
        
        return $this;
    }

    /**
     * Adicionar having
     */
    public function having(string $column, string $operator, $value): static
    {
        $this->queryConfig['having'][] = [$column, $operator, $value];
        
        return $this;
    }

    /**
     * Definir colunas pesquisáveis
     */
    public function searchableColumns(array $columns): static
    {
        $this->queryConfig['search_columns'] = $columns;
        
        return $this;
    }

    /**
     * Definir colunas ordenáveis
     */
    public function sortableColumns(array $columns): static
    {
        $this->queryConfig['sortable_columns'] = $columns;
        
        return $this;
    }

    /**
     * Definir ordenação padrão
     */
    public function defaultSort(string $column, string $direction = 'asc'): static
    {
        $this->queryConfig['default_sort'] = [$column, $direction];
        
        return $this;
    }

    /**
     * Habilitar otimizações de query
     */
    public function queryOptimizations(bool $enabled = true): static
    {
        $this->queryConfig['query_optimizations'] = $enabled;
        
        return $this;
    }

    /**
     * Definir callback de query customizada
     */
    public function customQuery(Closure $callback): static
    {
        $this->customQueryCallback = $callback;
        
        return $this;
    }

    /**
     * Construir query otimizada
     */
    protected function buildOptimizedQuery(Request $request): Builder
    {
        $query = $this->getBaseQuery();

        // Aplicar callback customizada se definida
        if ($this->customQueryCallback) {
            $query = call_user_func($this->customQueryCallback, $query, $request, $this);
        }

        // Aplicar configurações de query
        $query = $this->applyQueryConfigurations($query);

        // Aplicar otimizações
        if ($this->queryConfig['query_optimizations']) {
            $query = $this->applyQueryOptimizations($query, $request);
        }

        return $query;
    }

    /**
     * Aplicar configurações de query
     */
    protected function applyQueryConfigurations(Builder $query): Builder
    {
        // Eager loading
        if (!empty($this->queryConfig['eager_loading'])) {
            $query->with($this->queryConfig['eager_loading']);
        }

        // Scopes
        foreach ($this->queryConfig['scopes'] as [$scope, $parameters]) {
            $query->{$scope}(...$parameters);
        }

        // Joins
        foreach ($this->queryConfig['joins'] as [$type, $table, $first, $operator, $second]) {
            $method = $type . 'Join';
            $query->{$method}($table, $first, $operator, $second);
        }

        // Select raw
        foreach ($this->queryConfig['raw_selects'] as [$expression, $bindings]) {
            $query->selectRaw($expression, $bindings);
        }

        // Group by
        if (!empty($this->queryConfig['group_by'])) {
            $query->groupBy($this->queryConfig['group_by']);
        }

        // Having
        foreach ($this->queryConfig['having'] as [$column, $operator, $value]) {
            $query->having($column, $operator, $value);
        }

        return $query;
    }

    /**
     * Aplicar otimizações de query
     */
    protected function applyQueryOptimizations(Builder $query, Request $request): Builder
    {
        // Otimização 1: Select apenas colunas necessárias
        $selectedColumns = $this->getSelectedColumns();
        if (!empty($selectedColumns)) {
            $query->select($selectedColumns);
        }

        // Otimização 2: Limitar resultados para performance
        $this->applyQueryLimits($query, $request);

        // Otimização 3: Índices otimizados
        $this->suggestIndexes($query);

        return $query;
    }

    /**
     * Obter colunas selecionadas
     */
    protected function getSelectedColumns(): array
    {
        $columns = [];
        $tableColumns = $this->getColumns();

        foreach ($tableColumns as $key => $column) {
            if (isset($column['database_column'])) {
                $columns[] = $column['database_column'];
            } else {
                $columns[] = $key;
            }
        }

        // Sempre incluir ID e timestamps
        $columns[] = 'id';
        $columns[] = 'created_at';
        $columns[] = 'updated_at';

        return array_unique($columns);
    }

    /**
     * Aplicar limites de query
     */
    protected function applyQueryLimits(Builder $query, Request $request): void
    {
        // Limite máximo de registros por página
        $maxPerPage = config('papa-leguas.max_per_page', 100);
        $perPage = min($request->get('per_page', 15), $maxPerPage);

        // Aplicar limit se não estiver paginando
        if (!$request->has('page')) {
            $query->limit($perPage);
        }
    }

    /**
     * Sugerir índices para otimização
     */
    protected function suggestIndexes(Builder $query): void
    {
        if (!config('papa-leguas.suggest_indexes', false)) {
            return;
        }

        $suggestions = [];

        // Sugerir índices para colunas de busca
        foreach ($this->queryConfig['search_columns'] as $column) {
            $suggestions[] = "CREATE INDEX idx_{$this->getTableName()}_{$column} ON {$this->getTableName()} ({$column})";
        }

        // Sugerir índices para colunas de ordenação
        foreach ($this->queryConfig['sortable_columns'] as $column) {
            $suggestions[] = "CREATE INDEX idx_{$this->getTableName()}_{$column}_sort ON {$this->getTableName()} ({$column})";
        }

        // Log sugestões
        if (!empty($suggestions)) {
            \Illuminate\Support\Facades\Log::info('Papa Leguas Index Suggestions', [
                'table' => $this->getTableName(),
                'suggestions' => $suggestions
            ]);
        }
    }

    /**
     * Aplicar busca avançada
     */
    protected function applyAdvancedSearch(Builder $query, Request $request): Builder
    {
        $search = $request->get('search');
        
        if (!$search) {
            return $query;
        }

        $searchColumns = $this->getQuerySearchableColumns();
        
        if (empty($searchColumns)) {
            return $query;
        }

        return $query->where(function ($q) use ($search, $searchColumns) {
            foreach ($searchColumns as $column) {
                if (str_contains($column, '.')) {
                    // Busca em relacionamento
                    $this->applyRelationSearch($q, $column, $search);
                } else {
                    // Busca em coluna local
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            }
        });
    }

    /**
     * Aplicar busca em relacionamento
     */
    protected function applyRelationSearch(Builder $query, string $relationColumn, string $search): void
    {
        [$relation, $column] = explode('.', $relationColumn, 2);
        
        $query->orWhereHas($relation, function ($q) use ($column, $search) {
            $q->where($column, 'LIKE', "%{$search}%");
        });
    }

    /**
     * Aplicar ordenação avançada
     */
    protected function applyAdvancedSorting(Builder $query, Request $request): Builder
    {
        $sort = $request->get('sort');
        
        if (!$sort) {
            // Aplicar ordenação padrão
            if ($this->queryConfig['default_sort']) {
                [$column, $direction] = $this->queryConfig['default_sort'];
                return $query->orderBy($column, $direction);
            }
            return $query;
        }

        $sortableColumns = $this->getQuerySortableColumns();

        foreach ($sort as $column => $direction) {
            if (!in_array($column, $sortableColumns)) {
                continue;
            }

            $direction = strtolower($direction) === 'desc' ? 'desc' : 'asc';

            if (str_contains($column, '.')) {
                // Ordenação por relacionamento
                $this->applyRelationSorting($query, $column, $direction);
            } else {
                // Ordenação por coluna local
                $query->orderBy($column, $direction);
            }
        }

        return $query;
    }

    /**
     * Aplicar ordenação por relacionamento
     */
    protected function applyRelationSorting(Builder $query, string $relationColumn, string $direction): void
    {
        [$relation, $column] = explode('.', $relationColumn, 2);
        
        // Implementar ordenação por relacionamento usando subquery
        $relatedModel = $query->getModel()->{$relation}()->getRelated();
        $relatedTable = $relatedModel->getTable();
        $foreignKey = $query->getModel()->{$relation}()->getForeignKeyName();
        $localKey = $query->getModel()->{$relation}()->getLocalKeyName();

        $subquery = $relatedModel::select($column)
            ->whereColumn($relatedTable . '.id', $query->getModel()->getTable() . '.' . $foreignKey)
            ->limit(1);

        $query->orderBy($subquery, $direction);
    }

    /**
     * Obter colunas pesquisáveis para query
     */
    protected function getQuerySearchableColumns(): array
    {
        if (!empty($this->queryConfig['search_columns'])) {
            return $this->queryConfig['search_columns'];
        }

        // Auto-detectar colunas pesquisáveis
        $searchableColumns = [];
        foreach ($this->getColumns() as $key => $column) {
            if (isset($column['searchable']) && $column['searchable']) {
                $searchableColumns[] = $key;
            }
        }

        return $searchableColumns;
    }

    /**
     * Obter colunas ordenáveis para query
     */
    protected function getQuerySortableColumns(): array
    {
        if (!empty($this->queryConfig['sortable_columns'])) {
            return $this->queryConfig['sortable_columns'];
        }

        // Auto-detectar colunas ordenáveis
        $sortableColumns = [];
        foreach ($this->getColumns() as $key => $column) {
            if (isset($column['sortable']) && $column['sortable']) {
                $sortableColumns[] = $key;
            }
        }

        return $sortableColumns;
    }

    /**
     * Obter nome da tabela
     */
    protected function getTableName(): string
    {
        if ($this->model) {
            return (new $this->model)->getTable();
        }

        return 'unknown_table';
    }

    /**
     * Métodos de conveniência para configuração rápida
     */
    public function searchable(...$columns): static
    {
        return $this->searchableColumns($columns);
    }

    public function sortable(...$columns): static
    {
        return $this->sortableColumns($columns);
    }

    public function withCount(array $relations): static
    {
        foreach ($relations as $relation) {
            $this->queryConfig['eager_loading'][] = $relation . 'Count';
        }
        
        return $this;
    }

    public function whereHas(string $relation, Closure $callback = null): static
    {
        return $this->customQuery(function ($query) use ($relation, $callback) {
            return $query->whereHas($relation, $callback);
        });
    }

    public function whereDoesntHave(string $relation, Closure $callback = null): static
    {
        return $this->customQuery(function ($query) use ($relation, $callback) {
            return $query->whereDoesntHave($relation, $callback);
        });
    }
} 
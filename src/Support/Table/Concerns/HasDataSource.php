<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

trait HasDataSource
{
    protected string $modelClass;
    protected ?Closure $queryCallback = null;
    protected bool $isSearchable = false;
    protected bool $isSortable = false;
    protected bool $isFilterable = false;
    protected bool $isPaginated = false;
    protected bool $isSelectable = false;
    protected array $meta = [];
    protected int $perPage = 15;
    protected array $searchableColumns = [];
    protected array $sortableColumns = [];
    protected array $filterableColumns = [];

    /**
     * Definir o modelo da tabela
     */
    public function model(string $modelClass): static
    {
        $this->modelClass = $modelClass;
        return $this;
    }

    /**
     * Definir query customizada
     */
    public function query(Closure $callback): static
    {
        $this->queryCallback = $callback;
        return $this;
    }

    /**
     * Habilitar busca
     */
    public function searchable(bool $searchable = true): static
    {
        $this->isSearchable = $searchable;
        return $this;
    }

    /**
     * Habilitar ordenação
     */
    public function sortable(bool $sortable = true): static
    {
        $this->isSortable = $sortable;
        return $this;
    }

    /**
     * Habilitar filtros
     */
    public function filterable(bool $filterable = true): static
    {
        $this->isFilterable = $filterable;
        return $this;
    }

    /**
     * Habilitar paginação
     */
    public function paginated(bool $paginated = true, int $perPage = 15): static
    {
        $this->isPaginated = $paginated;
        $this->perPage = $perPage;
        return $this;
    }

    /**
     * Habilitar seleção
     */
    public function selectable(bool $selectable = true): static
    {
        $this->isSelectable = $selectable;
        return $this;
    }

    /**
     * Definir metadados
     */
    public function meta(array $meta): static
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Obter query base
     */
    protected function getBaseQuery(): Builder
    {
        if (!isset($this->modelClass) || !class_exists($this->modelClass)) {
            throw new \InvalidArgumentException("Modelo não definido ou não existe: " . ($this->modelClass ?? 'null'));
        }

        // Se há callback de query personalizada, usar ela
        if ($this->queryCallback) {
            $query = call_user_func($this->queryCallback);
            
            if (!$query instanceof Builder) {
                throw new \InvalidArgumentException("Callback de query deve retornar uma instância de Builder");
            }
            
            return $query;
        }

        // Senão, usar query padrão do modelo
        return $this->modelClass::query();
    }

    /**
     * Obter dados da tabela
     */
    protected function getData(): Collection
    {
        try {
            $query = $this->getBaseQuery();

            // Aplicar limite se não paginado
            if (!$this->isPaginated) {
                $query->limit($this->perPage);
            }

            return $query->get();
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados da tabela: ' . $e->getMessage(), [
                'model' => $this->modelClass ?? 'undefined',
                'exception' => $e
            ]);

            return collect([]);
        }
    }

    /**
     * Boot method específico do HasDataSource
     */
    protected function bootHasDataSource()
    {
        // Detectar colunas pesquisáveis e ordenáveis das colunas configuradas
        if (method_exists($this, 'getColumns')) {
            $this->detectColumnCapabilities();
        }
    }

    /**
     * Detectar capacidades das colunas
     */
    protected function detectColumnCapabilities(): void
    {
        $columns = $this->getColumns();
        
        foreach ($columns as $column) {
            if (method_exists($column, 'isSearchable') && $column->isSearchable()) {
                $this->searchableColumns[] = $column->getKey();
            }
            
            if (method_exists($column, 'isSortable') && $column->isSortable()) {
                $this->sortableColumns[] = $column->getKey();
            }
        }
    }

    /**
     * Getters para as configurações
     */
    public function getModelClass(): ?string
    {
        return $this->modelClass ?? null;
    }

    public function isSearchable(): bool
    {
        return $this->isSearchable;
    }

    public function isSortable(): bool
    {
        return $this->isSortable;
    }

    public function isFilterable(): bool
    {
        return $this->isFilterable;
    }

    public function isPaginated(): bool
    {
        return $this->isPaginated;
    }

    public function isSelectable(): bool
    {
        return $this->isSelectable;
    }

    public function getPerPage(): int
    {
        return $this->perPage;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getSearchableColumns(): array
    {
        return $this->searchableColumns;
    }

    public function getSortableColumns(): array
    {
        return $this->sortableColumns;
    }

    public function getFilterableColumns(): array
    {
        return $this->filterableColumns;
    }
} 
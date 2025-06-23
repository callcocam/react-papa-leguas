<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\DataSources\Contracts\DataSourceInterface;
use Callcocam\ReactPapaLeguas\Support\Table\DataSources\ModelSource;
use Callcocam\ReactPapaLeguas\Support\Table\DataSources\CollectionSource;
use Callcocam\ReactPapaLeguas\Support\Table\DataSources\ApiSource;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

trait HasDataSource
{
    protected ?DataSourceInterface $dataSource = null;
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

    // Propriedades para compatibilidade com código existente
    protected ?string $modelClass = null;
    protected ?Closure $queryCallback = null;

    /**
     * Definir fonte de dados personalizada
     */
    public function dataSource(DataSourceInterface $dataSource): static
    {
        $this->dataSource = $dataSource;
        return $this;
    }

    /**
     * Definir o modelo da tabela (cria ModelSource automaticamente)
     */
    public function model(string $modelClass): static
    {
        $this->modelClass = $modelClass;
        $this->dataSource = new ModelSource($modelClass);

        // Aplicar query callback se já foi definido
        if ($this->queryCallback) {
            $this->dataSource->query($this->queryCallback);
        }

        return $this;
    }

    /**
     * Definir query customizada
     */
    public function query(Closure $callback): static
    {
        $this->queryCallback = $callback;

        // Se já temos uma fonte de dados do tipo ModelSource, aplicar a query
        if ($this->dataSource instanceof ModelSource) {
            $this->dataSource->query($callback);
        }

        return $this;
    }

    /**
     * Definir fonte de dados como Collection
     */
    public function collection(\Illuminate\Support\Collection|array|Closure $data, array $config = []): static
    {
        $this->dataSource = new CollectionSource($data, $config);
        return $this;
    }

    /**
     * Definir fonte de dados como API
     */
    public function api(string $baseUrl, array $config = []): static
    {
        $this->dataSource = new ApiSource($baseUrl, $config);
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
     * Obter fonte de dados configurada
     */
    public function getDataSource(): ?DataSourceInterface
    {
        return $this->dataSource;
    }

    /**
     * Obter dados da tabela usando a fonte de dados
     */
    protected function getData(): \Illuminate\Support\Collection
    {
        try {
            if (!$this->dataSource) {
                throw new \RuntimeException('Nenhuma fonte de dados configurada');
            }

            // 🎯 WORKFLOW SUPPORT - Carregar relacionamentos automaticamente se workflow configurado
            if (method_exists($this, 'getModelWithWorkflowSupport') && $this->dataSource instanceof ModelSource) {
                $this->dataSource->with($this->getModelWithWorkflowSupport());
            }

            // 🎯 FILTROS POR TAB - Aplicar filtros baseados na tab ativa
            $this->applyTabFiltersToDataSource();

            // Configurar busca se habilitada
            if ($this->isSearchable && !empty($this->searchableColumns)) {
                $search = request('search', '');
                if ($search) {
                    $this->dataSource->applySearch($search, $this->searchableColumns);
                }
            }

            // Configurar filtros se habilitados
            if ($this->isFilterable) {
                $filters = request('filters', []);
                if (!empty($filters)) {
                    $this->dataSource->applyFilters($filters);
                }
            }

            // Configurar ordenação se habilitada
            if ($this->isSortable) {
                $sortColumn = request('sort_column');
                $sortDirection = request('sort_direction', 'asc');
                if ($sortColumn) {
                    $this->dataSource->applySorting($sortColumn, $sortDirection);
                }
            }

            return $this->dataSource->getData();
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados da tabela: ' . $e->getMessage(), [
                'data_source_type' => $this->dataSource?->getType() ?? 'undefined',
                'exception' => $e
            ]);

            return collect([]);
        }
    }

    /**
     * Obter dados paginados da tabela
     */
    protected function getPaginatedData(int $page = 1, ?int $perPage = null): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        try {
            if (!$this->dataSource) {
                throw new \RuntimeException('Nenhuma fonte de dados configurada');
            }

            $perPage = $perPage ?? $this->perPage;

            // 🎯 WORKFLOW SUPPORT - Carregar relacionamentos automaticamente se workflow configurado
            if (method_exists($this, 'getModelWithWorkflowSupport') && $this->dataSource instanceof ModelSource) {
                $this->dataSource->with($this->getModelWithWorkflowSupport());
            }

            // 🎯 FILTROS POR TAB - Aplicar filtros baseados na tab ativa
            $this->applyTabFiltersToDataSource();

            // Aplicar mesmas configurações do getData()
            if ($this->isSearchable && !empty($this->searchableColumns)) {
                $search = request('search', '');
                if ($search) {
                    $this->dataSource->applySearch($search, $this->searchableColumns);
                }
            }

            if ($this->isFilterable) {
                $filters = request('filters', []);
                if (!empty($filters)) {
                    $this->dataSource->applyFilters($filters);
                }
            }

            if ($this->isSortable) {
                $sortColumn = request('sort_column');
                $sortDirection = request('sort_direction', 'asc');
                if ($sortColumn) {
                    $this->dataSource->applySorting($sortColumn, $sortDirection);
                }
            }

            return $this->dataSource->getPaginatedData($page, $perPage);
        } catch (\Exception $e) {
            Log::error('Erro ao obter dados paginados da tabela: ' . $e->getMessage(), [
                'data_source_type' => $this->dataSource?->getType() ?? 'undefined',
                'exception' => $e
            ]);

            // Retornar paginação vazia em caso de erro
            return new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]),
                0,
                $perPage ?? $this->perPage,
                $page,
                ['path' => request()->url()]
            );
        }
    }

    /**
     * Obter query base (compatibilidade com código existente)
     * @deprecated Use getDataSource() instead
     */
    protected function getBaseQuery(): ?Builder
    {
        if ($this->dataSource instanceof ModelSource) {
            // Para compatibilidade, retornar null se não for ModelSource
            return null;
        }

        // Fallback para método antigo se não há fonte de dados configurada
        if (!$this->dataSource && $this->modelClass) {
            if (!class_exists($this->modelClass)) {
                throw new \InvalidArgumentException("Modelo não definido ou não existe: " . $this->modelClass);
            }

            if ($this->queryCallback) {
                $query = call_user_func($this->queryCallback);

                if (!$query instanceof Builder) {
                    throw new \InvalidArgumentException("Callback de query deve retornar uma instância de Builder");
                }

                return $query;
            }

            return $this->modelClass::query();
        }

        return null;
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

    /**
     * Obter informações da fonte de dados
     */
    public function getDataSourceInfo(): array
    {
        if (!$this->dataSource) {
            return [
                'type' => 'none',
                'configured' => false,
                'available' => false,
            ];
        }

        return [
            'type' => $this->dataSource->getType(),
            'configured' => true,
            'available' => $this->dataSource->isAvailable(),
            'supports' => [
                'pagination' => $this->dataSource->supportsPagination(),
                'search' => $this->dataSource->supportsSearch(),
                'sorting' => $this->dataSource->supportsSorting(),
                'filters' => $this->dataSource->supportsFilters(),
            ],
            'config' => $this->dataSource->getConfig(),
        ];
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

    public function getSearchableColumnKeys(): array
    {
        return $this->searchableColumns;
    }

    public function getSortableColumnKeys(): array
    {
        return $this->sortableColumns;
    }

    public function getFilterableColumns(): array
    {
        return $this->filterableColumns;
    }

    /**
     * 🎯 SISTEMA DE FILTROS POR TAB
     * Detecta parâmetro 'tab' na URL e aplica filtros configurados automaticamente
     */
    protected function applyTabFiltersToDataSource(): void
    {
        // Verificar se há parâmetro 'tab' na request
        $activeTab = request('tab');
        if (!$activeTab) {
            return; // Sem tab ativa, não aplicar filtros
        }

        // Verificar se a classe tem método applyTabFilters
        if (!method_exists($this, 'applyTabFilters')) {
            return; // Método não implementado, ignorar
        }

        // Verificar se a fonte de dados é ModelSource (suporta query customizada)
        if (!($this->dataSource instanceof \Callcocam\ReactPapaLeguas\Support\Table\DataSources\ModelSource)) {
            return; // Só funciona com ModelSource por enquanto
        }

        try {
            // Obter query atual da fonte de dados
            $query = $this->dataSource->getBuilder();

            // Aplicar filtros da tab usando o método da classe
            $this->applyTabFilters($query, $activeTab);

            // Log para debug
            Log::info("Filtros de tab aplicados", [
                'tab' => $activeTab,
                'table_class' => get_class($this),
                'model' => $this->getModelClass()
            ]);
        } catch (\Exception $e) {
            Log::warning("Erro ao aplicar filtros de tab", [
                'tab' => $activeTab,
                'table_class' => get_class($this),
                'error' => $e->getMessage()
            ]);
        }
    }
}

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
                // Suportar ambos os formatos: sort_column/sort_direction e sort/direction
                $sortColumn = request('sort_column') ?? request('sort');
                $sortDirection = request('sort_direction') ?? request('direction', 'asc');
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
     * 🎯 SISTEMA DE FILTROS POR TAB GENÉRICO
     * Detecta parâmetro 'tab' na URL e aplica filtros configurados automaticamente
     */
    protected function applyTabFiltersToDataSource(): void
    {
        // Verificar se há parâmetro 'tab' na request
        $activeTabId = request('tab');
        
        if (!$activeTabId) {
            return; // Sem tab ativa, não aplicar filtros
        }

        try {
            // 🎯 Encontrar a Tab ativa nas tabs configuradas
            $activeTab = $this->findActiveTab($activeTabId);
            if (!$activeTab) {
                // Se não encontrou Tab configurada, usar método legado
                if (method_exists($this, 'applyTabFilters')) {
                    $this->applyLegacyTabFilters($activeTabId);
                }
                return;
            }

            // Verificar se a fonte de dados é ModelSource (suporta query customizada)
            if (!($this->dataSource instanceof \Callcocam\ReactPapaLeguas\Support\Table\DataSources\ModelSource)) {
                return; // Só funciona com ModelSource por enquanto
            }

            // 🐛 CORREÇÃO: Limpar cache da query antes de aplicar filtros de tab
            // Isso força a reconstrução da query com os filtros de tab
            if (method_exists($this->dataSource, 'clearQueryCache')) {
                $this->dataSource->clearQueryCache();
            }

            // Obter query atual da fonte de dados
            $query = $this->dataSource->getBuilder();

            // 🎯 Aplicar filtros da Tab de forma genérica
            $this->applyGenericTabFilters($query, $activeTab);

            // 🎯 Aplicar método customizado se existir (compatibilidade)
            if (method_exists($this, 'applyTabFilters')) {
                $this->applyTabFilters($query, $activeTabId, $activeTab);
            }

            // Log para debug
            Log::info("🎯 Filtros de tab aplicados", [
                'tab_id' => $activeTabId,
                'tab_label' => $activeTab->getLabel(),
                'tab_source' => $this->getTabSource($activeTab),
                'params' => $activeTab->getParams(),
                'tabFilters' => $activeTab->getTabFilters(),
                'whereConditions' => $activeTab->getWhereConditions(),
                'scopeParams' => $activeTab->getScopeParams(),
                'hasFilters' => $activeTab->hasFilters(),
                'table_class' => get_class($this),
                'model' => $this->getModelClass()
            ]);
        } catch (\Exception $e) {
            Log::warning("❌ Erro ao aplicar filtros de tab", [
                'tab' => $activeTabId,
                'table_class' => get_class($this),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * 🎯 Encontra a Tab ativa baseada no ID
     */
    protected function findActiveTab(string $tabId): ?\Callcocam\ReactPapaLeguas\Support\Table\Tabs\Tab
    {
        $allTabs = [];

        // 1. 🎯 Verificar método getTabs() (para compatibilidade)
        if (method_exists($this, 'getTabs')) {
            $getTabs = $this->getTabs();
            if (is_array($getTabs)) {
                $allTabs = array_merge($allTabs, $getTabs);
            }
        }

        // 2. 🎯 Verificar método tabs() da própria Table (como TicketTable)
        if (method_exists($this, 'tabs')) {
            $tableTabs = $this->tabs();
            if (is_array($tableTabs)) {
                $allTabs = array_merge($allTabs, $tableTabs);
            }
        }

        // 3. 🎯 Verificar se há classe de Tabs externa (app/Tabs/)
        $tabsClass = $this->getTabsClass();
        if ($tabsClass && class_exists($tabsClass) && method_exists($tabsClass, 'getTabs')) {
            $externalTabs = $tabsClass::getTabs();
            if (is_array($externalTabs)) {
                $allTabs = array_merge($allTabs, $externalTabs);
            }
        }

        if (empty($allTabs)) {
            return null;
        }

        // Procurar a tab pelo ID em todas as fontes
        foreach ($allTabs as $tab) {
            if ($tab instanceof \Callcocam\ReactPapaLeguas\Support\Table\Tabs\Tab && $tab->getId() === $tabId) {
                return $tab;
            }
        }

        return null;
    }

    /**
     * 🎯 Obter classe de Tabs externa (app/Tabs/)
     */
    protected function getTabsClass(): ?string
    {
        // Detectar automaticamente baseado no nome da Table
        $tableClass = get_class($this);
        $baseName = class_basename($tableClass);
        
        // TicketTable -> TicketTabs
        $tabsClassName = str_replace('Table', 'Tabs', $baseName);
        
        // Verificar em app/Tabs/
        $tabsClass = "App\\Tabs\\{$tabsClassName}";
        
        return class_exists($tabsClass) ? $tabsClass : null;
    }

    /**
     * 🎯 Identificar fonte da Tab para debug
     */
    protected function getTabSource(\Callcocam\ReactPapaLeguas\Support\Table\Tabs\Tab $tab): string
    {
        $tabId = $tab->getId();

        // Verificar se vem do método tabs() da Table
        if (method_exists($this, 'tabs')) {
            $tableTabs = $this->tabs();
            if (is_array($tableTabs)) {
                foreach ($tableTabs as $tableTab) {
                    if ($tableTab instanceof \Callcocam\ReactPapaLeguas\Support\Table\Tabs\Tab && 
                        $tableTab->getId() === $tabId) {
                        return 'Table::tabs()';
                    }
                }
            }
        }

        // Verificar se vem do método getTabs()
        if (method_exists($this, 'getTabs')) {
            $getTabs = $this->getTabs();
            if (is_array($getTabs)) {
                foreach ($getTabs as $getTab) {
                    if ($getTab instanceof \Callcocam\ReactPapaLeguas\Support\Table\Tabs\Tab && 
                        $getTab->getId() === $tabId) {
                        return 'Table::getTabs()';
                    }
                }
            }
        }

        // Verificar se vem de classe externa
        $tabsClass = $this->getTabsClass();
        if ($tabsClass) {
            return $tabsClass . '::getTabs()';
        }

        return 'unknown';
    }

    /**
     * 🎯 Aplica filtros genéricos da Tab
     */
    protected function applyGenericTabFilters($query, \Callcocam\ReactPapaLeguas\Support\Table\Tabs\Tab $tab): void
    { 
        // 1. Aplicar condições WHERE
        foreach ($tab->getWhereConditions() as $column => $value) {
            if (is_array($value)) {
                $query->whereIn($column, $value);
            } else {
                $query->where($column, $value);
            }
        }

        // 2. Aplicar parâmetros via scopes (se o modelo tiver)
        foreach ($tab->getScopeParams() as $scope => $params) {
            $scopeMethod = 'scope' . ucfirst($scope);
            if (method_exists($query->getModel(), $scopeMethod)) {
                if (is_array($params)) {
                    $query->$scope(...$params);
                } else {
                    $query->$scope($params);
                }
            }
        }

        // 3. Aplicar callback customizado
        $queryCallback = $tab->getQueryCallback();
        if ($queryCallback) {
            $queryCallback($query, $tab->getParams(), $tab->getTabFilters());
        }
    }

    /**
     * 🎯 Aplica filtros usando método legado (compatibilidade)
     */
    protected function applyLegacyTabFilters(string $activeTabId): void
    {
        if (!($this->dataSource instanceof \Callcocam\ReactPapaLeguas\Support\Table\DataSources\ModelSource)) {
            return;
        }

        $query = $this->dataSource->getBuilder();
        $this->applyTabFilters($query, $activeTabId);
        
        Log::info("🔄 Filtros de tab legados aplicados", [
            'tab' => $activeTabId,
            'table_class' => get_class($this)
        ]);
    }
}

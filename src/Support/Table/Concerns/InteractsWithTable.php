<?php

/**
 * InteractsWithTable
 *
 * @package Callcocam\ReactPapaLeguas\Support\Table\Concerns
 * @author  Callcocam <callcocam@gmail.com>
 * @license MIT
 * @link    https://github.com/callcocam/react-papa-leguas
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToRoutes; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

trait InteractsWithTable
{
    use BelongsToRoutes, HasDataSource;

    protected $data;
    protected $currentPage = 1;
    protected $routeExecuteAction = 'api.landlord.actions.execute';

    /**
     * Boot method - inicializa todos os traits
     */
    protected function boot()
    {
        // Chamar boot methods de todos os traits
        $this->bootTraits();
    }

    /**
     * Boot traits automaticamente
     */
    protected function bootTraits()
    {
        $class = static::class;

        foreach (class_uses_recursive($class) as $trait) {
            $method = 'boot' . class_basename($trait);

            if (method_exists($this, $method) && !in_array($method, ['boot'])) {
                $this->{$method}();
            }
        }
    }

    /**
     * Define o modelo da tabela (delegado para HasDataSource)
     */
    public function setModel(string $modelClass): self
    {
        $this->model($modelClass);
        return $this;
    }

    /**
     * Retorna os dados da tabela em formato de array
     */
    public function toArray(): array
    {
        try {
            // Obter dados formatados (com ou sem paginação)
            $dataResult = $this->getFormattedDataResult();
            
            // Montar resultado base
            $result = [
                'table' => $dataResult['table'],
                'config' => $this->getTableConfig(),
                'routes' => $this->getRouteNames(),
                'capabilities' => $this->getTableCapabilities(),
                'data_source' => $this->getDataSourceInfo(),
            ];

            // Adicionar sistemas configuráveis
            $result = array_merge($result, $this->getTabsConfiguration());
            $result = array_merge($result, $this->getViewsConfiguration()); 
            return $result;
            
        } catch (\Exception $e) {
            Log::error('Erro no método toArray da Table: ' . $e->getMessage(), [
                'data_source_type' => $this->getDataSource()?->getType() ?? 'undefined',
                'model' => $this->getModelClass(),
                'exception' => $e
            ]);

            throw $e;
        }
    }

    /**
     * Obter dados formatados com ou sem paginação
     */
    protected function getFormattedDataResult(): array
    {
        if ($this->isPaginated()) {
            return $this->getPaginatedDataResult();
        } else {
            return $this->getNonPaginatedDataResult();
        }
    }

    /**
     * Obter dados paginados formatados
     */
    protected function getPaginatedDataResult(): array
    {
        $paginatedData = $this->getPaginatedData();
        $formattedData = $paginatedData->getCollection()->map(fn($row) => $this->formatRow($row))->values();

        return [
            'table' => [
                'data' => $formattedData,
                'columns' => $this->getColumnsConfig(),
                'filters' => $this->getFiltersConfig(),
                'actions' => [
                    'row' => [],
                    'bulk' => $this->getBulkActionsConfig(),
                ],
                'pagination' => [
                    'current_page' => $paginatedData->currentPage(),
                    'per_page' => $paginatedData->perPage(),
                    'total' => $paginatedData->total(),
                    'last_page' => $paginatedData->lastPage(),
                    'from' => $paginatedData->firstItem(),
                    'to' => $paginatedData->lastItem(),
                ],
                'meta' => $this->getTableMeta(),
            ]
        ];
    }

    /**
     * Obter dados não paginados formatados
     */
    protected function getNonPaginatedDataResult(): array
    {
        $data = $this->getData();
        $formattedData = $data->map(fn($row) => $this->formatRow($row))->values();

        return [
            'table' => [
                'data' => $formattedData,
                'columns' => $this->getColumnsConfig(),
                'filters' => $this->getFiltersConfig(),
                'actions' => [
                    'row' => [],
                    'bulk' => $this->getBulkActionsConfig(),
                ],
                'route_execute_action' => $this->getRouteExecuteAction(),
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $this->getPerPage(),
                    'total' => $data->count(),
                    'last_page' => 1,
                    'from' => 1,
                    'to' => $data->count(),
                ],
                'meta' => $this->getTableMeta(),
            ]
        ];
    }

    /**
     * Obter configurações da tabela
     */
    protected function getTableConfig(): array
    {
        return [
            'model_name' => $this->getModelClass() ? class_basename($this->getModelClass()) : 'Unknown',
            'page_title' => $this->getTitle(),
            'page_description' => $this->getDescription(),
            'route_prefix' => $this->getRoutePrefix(),
            'can_create' => true,
            'can_edit' => true,
            'can_delete' => true,
            'can_export' => true,
            'can_bulk_delete' => true,
        ];
    }

    /**
     * Obter capacidades da tabela
     */
    protected function getTableCapabilities(): array
    {
        return [
            'searchable_columns' => $this->getSearchableColumnKeys(),
            'sortable_columns' => $this->getSortableColumnKeys(),
            'filterable_columns' => $this->getFilterableColumns(),
        ];
    }

    /**
     * Obter metadados da tabela
     */
    protected function getTableMeta(): array
    {
        return [
            'key' => $this->getKey(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'searchable' => $this->isSearchable(),
            'sortable' => $this->isSortable(),
            'filterable' => $this->isFilterable(),
            'paginated' => $this->isPaginated(),
            'selectable' => $this->isSelectable(),
        ];
    }

    /**
     * Obter configuração do sistema de tabs
     */
    protected function getTabsConfiguration(): array
    {
        
        $tabs = array_map(fn($tab) => $tab->toArray(), $this->tabs());
        if (empty($tabs)) {
            return [];
        }

        // Marcar tab ativa baseada no parâmetro da URL
        $activeTabId = request()->get('tab', $tabs[0]['id'] ?? 'todos');
        foreach ($tabs as &$tab) {
            $tab['active'] = ($tab['id'] === $activeTabId);
        }

        return [
            'tabs' => $tabs,
            'tabsConfig' => $this->getTabsConfig($activeTabId),
        ];
    }

    /**
     * Obter configuração do sistema de views
     */
    protected function getViewsConfiguration(): array
    {
        $views = array_map(fn($view) => $view->toArray(), $this->views());

        if(method_exists($this, 'getViewsWithWorkflowSupport')){
            $views = $this->getViewsWithWorkflowSupport($views);
        }
 
        if (empty($views)) {
            return [];
        }

        // Detectar view ativa baseada no parâmetro da URL
        $activeViewId = request()->get('view', $views[0]['id'] ?? 'list');
        
        // Verificar se a view solicitada existe
        $validView = collect($views)->firstWhere('id', $activeViewId);
        if (!$validView) {
            $activeViewId = $views[0]['id'] ?? 'list';
        }

        // Marcar view ativa
        foreach ($views as &$view) {
            $view['active'] = ($view['id'] === $activeViewId);
        }

        return [
            'views' => $views,
            'activeView' => $activeViewId,
            'viewsConfig' => $this->getViewsConfig($activeViewId),
        ];
    }

    /**
     * Obter configuração das tabs (com fallback padrão)
     */
    protected function getTabsConfig(string $activeTabId): array
    {
        if (method_exists($this, 'tabsConfig')) {
            return $this->tabsConfig();
        }

        return [
            'defaultTab' => $activeTabId,
            'variant' => 'default',
            'size' => 'md',
            'showBadges' => true,
            'showIcons' => true,
            'scrollable' => true,
        ];
    }

    /**
     * Obter configuração das views (com fallback padrão)
     */
    protected function getViewsConfig(string $activeViewId): array
    {
        if (method_exists($this, 'viewsConfig')) {
            return $this->viewsConfig();
        }

        return [
            'defaultView' => $activeViewId,
            'showViewSelector' => true,
            'position' => 'top-right',
            'variant' => 'buttons',
            'size' => 'sm',
        ];
    }

    /**
     * Gera uma chave única para a tabela, baseada no nome da classe.
     * Ex: UserTable se torna 'user-table'
     */
    public function getKey(): string
    {
        return Str::kebab(class_basename($this));
    }

    /**
     * Métodos que devem ser implementados pelos traits ou classes
     */

    /**
     * Obter colunas (implementado por HasColumns)
     */
    abstract public function getColumns(): array;

    /**
     * Obter configuração das colunas (implementado por HasColumns)
     */
    abstract public function getColumnsConfig(): array;

    /**
     * Formatar linha (implementado por HasColumns)
     */
    abstract protected function formatRow($row): array;

    /**
     * Métodos que podem ser sobrescritos pelas classes filhas
     */
    protected function getTitle(): string
    {
        $modelClass = $this->getModelClass();
        if ($modelClass) {
            return class_basename($modelClass) . 's';
        }

        // Fallback para meta configurado
        $meta = $this->getMeta();
        return $meta['title'] ?? 'Tabela';
    }

    protected function getDescription(): string
    {
        // Verificar se há descrição nos meta
        $meta = $this->getMeta();
        if (isset($meta['description'])) {
            return $meta['description'];
        }

        return 'Gerencie ' . strtolower($this->getTitle());
    }

    public function routeExecuteAction(string $routeExecuteAction): self
    {
        $this->routeExecuteAction = $routeExecuteAction;
        return $this;
    }

    public function getRouteExecuteAction(): string
    {
        if (Route::has($this->routeExecuteAction)) {
            return route($this->routeExecuteAction, ['actionKey' => 'name']);
        }

        return $this->routeExecuteAction;
    }

    /**
     * Métodos padrão para traits opcionais
     */
    protected function getTableActions(): array
    {
        // Este método parece ser um candidato a conter a lógica de `_actions`
        $row = $this->getCurrentRow();
        if($row){
            return $this->getRowActions($row);
        }
        return [];
    }

    /**
     * Obter colunas filtráveis
     */
    protected function getFilterableColumns(): array
    {
        // Verificar se o trait HasFilters está sendo usado
        if (method_exists($this, 'getVisibleFilters')) {
            return collect($this->getVisibleFilters())->pluck('key')->toArray();
        }

        // Fallback: retornar colunas que são marcadas como filtráveis
        if (method_exists($this, 'getColumns')) {
            return collect($this->getColumns())
                ->filter(fn($column) => method_exists($column, 'isFilterable') && $column->isFilterable())
                ->pluck('key')
                ->toArray();
        }

        return [];
    }

    /**
     * Verificar se a tabela é filtrável
     */
    protected function isFilterable(): bool
    {
        // Verificar se há filtros disponíveis
        return !empty($this->getFiltersConfig());
    }

    /**
     * Verificar se a tabela tem ações
     */
    protected function hasTableActions(): bool
    {
        // Verificar se o trait HasActions está sendo usado
        if (method_exists($this, 'getActionsCount')) {
            return $this->getActionsCount() > 0;
        }

        // Fallback: verificar se há ações
        return !empty($this->getTableActions());
    }

    /**
     * Obter colunas de ações
     */
    protected function getActionableColumns(): array
    {
        // Verificar se o trait HasActions está sendo usado
        if (method_exists($this, 'getVisibleActions')) {
            return collect($this->getVisibleActions())->pluck('key')->toArray();
        }

        return [];
    }
}

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
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait InteractsWithTable
{
    use BelongsToRoutes, HasDataSource;
    
    protected $data;
    protected $currentPage = 1;

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
            // Usar paginação se habilitada
            if ($this->isPaginated()) {
                $paginatedData = $this->getPaginatedData();
                $formattedData = $paginatedData->getCollection()->map(fn($row) => $this->formatRow($row))->values();
                
                return [
                    'table' => [
                        'data' => $formattedData,
                        'columns' => $this->getColumnsConfig(),
                        'filters' => $this->getFiltersConfig(),
                        'actions' => $this->getTableActions(),
                        'pagination' => [
                            'current_page' => $paginatedData->currentPage(),
                            'per_page' => $paginatedData->perPage(),
                            'total' => $paginatedData->total(),
                            'last_page' => $paginatedData->lastPage(),
                            'from' => $paginatedData->firstItem(),
                            'to' => $paginatedData->lastItem(),
                        ],
                        'meta' => [
                            'title' => $this->getTitle(),
                            'description' => $this->getDescription(),
                            'searchable' => $this->isSearchable(),
                            'sortable' => $this->isSortable(),
                            'filterable' => $this->isFilterable(),
                            'paginated' => $this->isPaginated(),
                            'selectable' => $this->isSelectable(),
                        ]
                    ],
                    'config' => [
                        'model_name' => $this->getModelClass() ? class_basename($this->getModelClass()) : 'Unknown',
                        'page_title' => $this->getTitle(),
                        'page_description' => $this->getDescription(),
                        'route_prefix' => $this->getRoutePrefix(),
                        'can_create' => true,
                        'can_edit' => true,
                        'can_delete' => true,
                        'can_export' => true,
                        'can_bulk_delete' => true,
                    ],
                    'routes' => $this->getRouteNames(),
                    'capabilities' => [
                        'searchable_columns' => $this->getSearchableColumnKeys(),
                        'sortable_columns' => $this->getSortableColumnKeys(),
                        'filterable_columns' => $this->getFilterableColumns(),
                    ],
                    'data_source' => $this->getDataSourceInfo(),
                ];
            } else {
                // Modo sem paginação
                $data = $this->getData();
                $formattedData = $data->map(fn($row) => $this->formatRow($row))->values();

                return [
                    'table' => [
                        'data' => $formattedData,
                        'columns' => $this->getColumnsConfig(),
                        'filters' => $this->getFiltersConfig(),
                        'actions' => $this->getTableActions(),
                        'pagination' => [
                            'current_page' => 1,
                            'per_page' => $this->getPerPage(),
                            'total' => $data->count(),
                            'last_page' => 1,
                            'from' => 1,
                            'to' => $data->count(),
                        ],
                        'meta' => [
                            'title' => $this->getTitle(),
                            'description' => $this->getDescription(),
                            'searchable' => $this->isSearchable(),
                            'sortable' => $this->isSortable(),
                            'filterable' => $this->isFilterable(),
                            'paginated' => $this->isPaginated(),
                            'selectable' => $this->isSelectable(),
                        ]
                    ],
                    'config' => [
                        'model_name' => $this->getModelClass() ? class_basename($this->getModelClass()) : 'Unknown',
                        'page_title' => $this->getTitle(),
                        'page_description' => $this->getDescription(),
                        'route_prefix' => $this->getRoutePrefix(),
                        'can_create' => true,
                        'can_edit' => true,
                        'can_delete' => true,
                        'can_export' => true,
                        'can_bulk_delete' => true,
                    ],
                    'routes' => $this->getRouteNames(),
                    'capabilities' => [
                        'searchable_columns' => $this->getSearchableColumnKeys(),
                        'sortable_columns' => $this->getSortableColumnKeys(),
                        'filterable_columns' => $this->getFilterableColumns(),
                    ],
                    'data_source' => $this->getDataSourceInfo(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('Erro no método toArray da Table: ' . $e->getMessage(), [
                'data_source_type' => $this->getDataSource()?->getType() ?? 'undefined',
                'model' => $this->getModelClass(),
                'exception' => $e
            ]);
            
            throw $e; // Re-throw para que o controller possa capturar
        }
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

    /**
     * Métodos padrão para traits opcionais
     */
    protected function getTableActions(): array
    {
        // Verificar se o trait HasActions está sendo usado
        if (method_exists($this, 'getActionsConfig')) {
            return $this->getActionsConfig();
        }
        
        // Fallback: usar método actions() se existir
        return method_exists($this, 'actions') ? $this->actions() : [];
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
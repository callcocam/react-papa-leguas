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
            $data = $this->getData();
            $formattedData = $data->map(fn($row) => $this->formatRow($row))->values();

            return [
                'table' => [
                    'data' => $formattedData,
                    'columns' => $this->getColumnsConfig(),
                    'filters' => $this->getFilters(),
                    'actions' => $this->getActions(),
                    'pagination' => [
                        'current_page' => $this->currentPage,
                        'per_page' => $this->getPerPage(),
                        'total' => $data->count(),
                        'last_page' => 1,
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
                    'searchable_columns' => $this->getSearchableColumns(),
                    'sortable_columns' => $this->getSortableColumns(),
                    'filterable_columns' => $this->getFilterableColumns(),
                ]
            ];
        } catch (\Exception $e) {
            Log::error('Erro no método toArray da Table: ' . $e->getMessage(), [
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
    protected function getFilters(): array
    {
        return method_exists($this, 'filters') ? $this->filters() : [];
    }

    protected function getActions(): array
    {
        return method_exists($this, 'actions') ? $this->actions() : [];
    }
}
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table;

use Callcocam\ReactPapaLeguas\Support\Table\Contracts\TableInterface;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasColumns;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasActions;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasBulkActions;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasRelations;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasRelationBulkActions;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasFilters;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasFluentFilters;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasCaching;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasPermissions;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasDataTransformation;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasValidation;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasQuery;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasPagination;
// use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasSorting;
// use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasSearch;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Closure;

/**
 * Classe Table moderna com suporte completo a formatação avançada,
 * relacionamentos, badges dinâmicos e componentes React customizados.
 */
class Table implements TableInterface
{
    use HasColumns;
    use HasActions;
    use HasBulkActions;
    use HasRelations;
    use HasRelationBulkActions;
    use HasFilters;
    use HasFluentFilters;
    use HasCaching;
    use HasPermissions;
    use HasDataTransformation;
    use HasValidation;
    use HasQuery;
    use HasPagination;
    // use HasSorting;
    // use HasSearch;

    /**
     * ID único da tabela
     */
    protected string $id;

    /**
     * Modelo Eloquent associado
     */
    protected ?string $model = null;

    /**
     * Query builder personalizada
     */
    protected ?Closure $queryCallback = null;

    /**
     * Nome do componente React
     */
    protected string $component = 'PapaLeguasTable';

    /**
     * Configurações da tabela
     */
    protected array $config = [
        'searchable' => true,
        'sortable' => true,
        'filterable' => true,
        'paginated' => true,
        'exportable' => false,
        'selectable' => true,
        'responsive' => true,
        'striped' => true,
        'bordered' => false,
        'hover' => true,
        'compact' => false,
        'perPage' => 15,
    ];

    /**
     * Metadados da tabela
     */
    protected array $meta = [];

    /**
     * Última coluna adicionada (para configuração fluente)
     */
    protected ?\Callcocam\ReactPapaLeguas\Support\Table\Columns\Column $lastColumn = null;

    /**
     * Criar nova instância da tabela
     */
    public static function make(string $id = null): static
    {
        $instance = new static();
        $instance->id = $id ?? 'table-' . uniqid();
        
        return $instance;
    }

    /**
     * Definir o modelo Eloquent
     */
    public function model(string $model): static
    {
        if (!class_exists($model) || !is_subclass_of($model, Model::class)) {
            throw new \InvalidArgumentException("Model {$model} não existe ou não é um Model Eloquent válido.");
        }

        $this->model = $model;
        
        return $this;
    }

    /**
     * Definir query personalizada
     */
    public function query(Closure $callback): static
    {
        $this->queryCallback = $callback;
        
        return $this;
    }

    /**
     * Definir configurações da tabela
     */
    public function config(array $config): static
    {
        $this->config = array_merge($this->config, $config);
        
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
     * Definir componente React
     */
    public function component(string $component): static
    {
        $this->component = $component;
        
        return $this;
    }

    /**
     * Definir ID da tabela
     */
    public function id(string $id): static
    {
        $this->id = $id;
        
        return $this;
    }

    /**
     * Capturar chamadas de métodos para configuração fluente
     */
    public function __call(string $method, array $arguments)
    {
        // Se há uma última coluna e o método existe nela, aplicar à coluna
        if ($this->lastColumn && method_exists($this->lastColumn, $method)) {
            $result = $this->lastColumn->{$method}(...$arguments);
            
            // Se o método retorna a coluna, manter a fluência retornando a tabela
            if ($result === $this->lastColumn) {
                return $this;
            }
            
            return $result;
        }

        // Tentar com filtros
        if ($this->lastFilter && method_exists($this->lastFilter, $method)) {
            $result = $this->lastFilter->{$method}(...$arguments);
            
            if ($result === $this->lastFilter) {
                return $this;
            }
            
            return $result;
        }
        
        // Se não encontrou o método, lançar exceção
        throw new \BadMethodCallException("Método {$method} não encontrado na classe " . static::class);
    }

    /**
     * Obter ID da tabela
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Obter modelo
     */
    public function getModel(): ?string
    {
        return $this->model;
    }

    /**
     * Obter todas as ações (header + row)
     */
    public function getActions(): array
    {
        return array_merge($this->getHeaderActions(), $this->getRowActions());
    }

    /**
     * Obter query base
     */
    public function getBaseQuery(): Builder
    {
        if ($this->queryCallback) {
            return call_user_func($this->queryCallback);
        }

        if ($this->model) {
            return $this->model::query();
        }

        throw new \RuntimeException('Nenhum modelo ou query foi definido para a tabela.');
    }

    /**
     * Processar dados da tabela para o frontend
     */
    public function getData(Request $request = null): array
    {
        $request = $request ?? request();
        
        // Aplicar cache se habilitado
        if ($this->isCacheEnabled()) {
            return $this->getCachedData($request);
        }

        return $this->buildTableData($request);
    }

    /**
     * Construir dados da tabela
     */
    protected function buildTableData(Request $request): array
    {
        $query = $this->getBaseQuery();

        // Aplicar permissões
        $query = $this->applyPermissions($query, $request);

        // Aplicar busca
        $query = $this->applySearch($query, $request);

        // Aplicar filtros
        if ($this->hasFilters()) {
            $this->filterValues($request->get('filters', []));
            $query = $this->applyFilters($query);
        }

        // Aplicar ordenação
        $query = $this->applySorting($query, $request);

        // Obter dados paginados
        $paginatedData = $this->getPaginatedData($query, $request);

        // Transformar dados
        $transformedData = $this->transformData($paginatedData['data']);

        // Validar dados
        $validatedData = $this->validateData($transformedData);

        return [
            'id' => $this->getId(),
            'component' => $this->component,
            'data' => $validatedData,
            'columns' => $this->getColumnsForFrontend(),
            'actions' => $this->getActionsForFrontend(),
            'filters' => $this->getFiltersForFrontend(),
            'bulkActions' => $this->getBulkActionsForFrontend(),
            'relationBulkActions' => $this->getRelationBulkConfigForFrontend(),
            'relationContext' => $this->getRelationContext(),
            'pagination' => $paginatedData['pagination'],
            'sorting' => $this->getSortingData($request),
            'search' => $this->getSearchData($request),
            'config' => $this->config,
            'meta' => $this->meta,
            'permissions' => $this->getPermissionsForFrontend(),
        ];
    }

    /**
     * Renderizar tabela para Inertia
     */
    public function render(Request $request = null): array
    {
        return $this->getData($request);
    }

    /**
     * Converter para array
     */
    public function toArray(): array
    {
        return $this->getData();
    }

    /**
     * Métodos de conveniência para configuração rápida
     */
    public function searchable(bool $searchable = true): static
    {
        $this->config['searchable'] = $searchable;
        return $this;
    }

    public function sortable(bool $sortable = true): static
    {
        $this->config['sortable'] = $sortable;
        return $this;
    }

    public function filterable(bool $filterable = true): static
    {
        $this->config['filterable'] = $filterable;
        return $this;
    }

    public function paginated(bool $paginated = true): static
    {
        $this->config['paginated'] = $paginated;
        return $this;
    }

    public function exportable(bool $exportable = true): static
    {
        $this->config['exportable'] = $exportable;
        return $this;
    }

    public function selectable(bool $selectable = true): static
    {
        $this->config['selectable'] = $selectable;
        return $this;
    }

    public function striped(bool $striped = true): static
    {
        $this->config['striped'] = $striped;
        return $this;
    }

    public function bordered(bool $bordered = true): static
    {
        $this->config['bordered'] = $bordered;
        return $this;
    }

    public function hover(bool $hover = true): static
    {
        $this->config['hover'] = $hover;
        return $this;
    }

    public function compact(bool $compact = true): static
    {
        $this->config['compact'] = $compact;
        return $this;
    }

    public function responsive(bool $responsive = true): static
    {
        $this->config['responsive'] = $responsive;
        return $this;
    }

    /**
     * Métodos auxiliares para processamento de dados
     */
    protected function applyPermissions(Builder $query, Request $request): Builder
    {
        // Implementar lógica de permissões se necessário
        return $query;
    }

    protected function applySearch(Builder $query, Request $request): Builder
    {
        $search = $request->get('search');
        if (empty($search)) {
            return $query;
        }

        // Implementar lógica de busca se necessário
        return $query;
    }

    protected function applySorting(Builder $query, Request $request): Builder
    {
        $sort = $request->get('sort');
        $direction = $request->get('direction', 'asc');
        
        if ($sort) {
            return $query->orderBy($sort, $direction);
        }

        return $query;
    }

    protected function getPaginatedData(Builder $query, Request $request): array
    {
        $perPage = $request->get('per_page', $this->config['perPage'] ?? 15);
        $page = $request->get('page', 1);

        if (!($this->config['paginated'] ?? true)) {
            $data = $query->get();
            return [
                'data' => $data->toArray(),
                'pagination' => [
                    'enabled' => false,
                    'total' => $data->count(),
                ],
            ];
        }

        $paginated = $query->paginate($perPage, ['*'], 'page', $page);

        return [
            'data' => collect($paginated->items())->toArray(),
            'pagination' => [
                'enabled' => true,
                'current_page' => $paginated->currentPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
                'last_page' => $paginated->lastPage(),
                'from' => $paginated->firstItem(),
                'to' => $paginated->lastItem(),
                'has_more_pages' => $paginated->hasMorePages(),
                'links' => $paginated->toArray()['links'] ?? [],
            ],
        ];
    }

    protected function transformData(array $data): array
    {
        // Implementar transformação de dados se necessário
        return $data;
    }

    protected function validateData(array $data): array
    {
        // Implementar validação de dados se necessário
        return $data;
    }

    protected function getSortingData(Request $request): array
    {
        return [
            'enabled' => $this->config['sortable'],
            'field' => $request->get('sort'),
            'direction' => $request->get('direction', 'asc'),
        ];
    }

    protected function getSearchData(Request $request): array
    {
        return [
            'enabled' => $this->config['searchable'],
            'value' => $request->get('search', ''),
        ];
    }

    protected function getPermissionsForFrontend(): array
    {
        return [
            'canCreate' => true,
            'canEdit' => true,
            'canDelete' => true,
            'canExport' => $this->config['exportable'],
        ];
    }

    protected function isCacheEnabled(): bool
    {
        return $this->config['cache']['enabled'] ?? false;
    }

    protected function getCachedData(Request $request): array
    {
        // Implementar cache se necessário
        return $this->buildTableData($request);
    }
} 
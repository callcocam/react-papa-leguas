<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Classe base para sub-tabelas aninhadas/hierárquicas.
 * 
 * Funcionalidades:
 * - Configuração específica para sub-tabelas
 * - Filtros baseados no item pai
 * - Paginação reduzida
 * - Interface compacta
 * - Lazy loading de dados
 */
abstract class NestedTable extends Table
{
    /**
     * ID do item pai (usado para filtrar dados)
     */
    protected mixed $parentId = null;

    /**
     * Modelo do item pai
     */
    protected ?Model $parentItem = null;

    /**
     * Configurações padrão para sub-tabelas
     */
    protected array $defaultConfig = [
        'per_page' => 5,
        'show_header' => true,
        'show_pagination' => true,
        'searchable' => false,
        'sortable' => false,
        'compact' => true,
        'show_bulk_actions' => false,
        'show_filters' => false,
    ];

    /**
     * Configurações atuais da sub-tabela
     */
    protected array $config = [];

    /**
     * Closure para customizar a query baseada no pai
     */
    protected ?Closure $parentFilter = null;

    /**
     * Inicializa a sub-tabela
     */
    public function __construct()
    {
        parent::__construct();
        
        // Aplicar configurações padrão
        $this->config = array_merge($this->defaultConfig, $this->config);
    }

    /**
     * Configura a sub-tabela para um item pai específico
     */
    public function forParent(mixed $parentId, ?Model $parentItem = null): static
    {
        $this->parentId = $parentId;
        $this->parentItem = $parentItem;
        return $this;
    }

    /**
     * Define um filtro baseado no item pai
     */
    public function parentFilter(Closure $callback): static
    {
        $this->parentFilter = $callback;
        return $this;
    }

    /**
     * Configura a sub-tabela
     */
    public function configure(array $config): static
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Define quantos itens por página
     */
    public function perPage(int $perPage): static
    {
        $this->config['per_page'] = $perPage;
        return $this;
    }

    /**
     * Define se deve mostrar cabeçalho
     */
    public function showHeader(bool $show = true): static
    {
        $this->config['show_header'] = $show;
        return $this;
    }

    /**
     * Define se deve mostrar paginação
     */
    public function showPagination(bool $show = true): static
    {
        $this->config['show_pagination'] = $show;
        return $this;
    }

    /**
     * Define se é pesquisável
     */
    public function searchable(bool $searchable = true): static
    {
        $this->config['searchable'] = $searchable;
        return $this;
    }

    /**
     * Define se é ordenável
     */
    public function sortable(bool $sortable = true): static
    {
        $this->config['sortable'] = $sortable;
        return $this;
    }

    /**
     * Define se deve ser compacta
     */
    public function compact(bool $compact = true): static
    {
        $this->config['compact'] = $compact;
        return $this;
    }

    /**
     * Define se deve mostrar ações em lote
     */
    public function showBulkActions(bool $show = false): static
    {
        $this->config['show_bulk_actions'] = $show;
        return $this;
    }

    /**
     * Define se deve mostrar filtros
     */
    public function showFilters(bool $show = false): static
    {
        $this->config['show_filters'] = $show;
        return $this;
    }

    /**
     * Obtém os dados da sub-tabela filtrados pelo pai
     */
    public function getNestedData(Request $request = null): array
    {
        // Obter query base
        $query = $this->getQuery();

        // Aplicar filtro do pai
        if ($this->parentId && $this->parentFilter) {
            $query = $this->evaluate($this->parentFilter, [
                'query' => $query,
                'parentId' => $this->parentId,
                'parentItem' => $this->parentItem,
            ]);
        }

        // Aplicar filtros da requisição se habilitado
        if ($request && $this->config['searchable']) {
            $search = $request->get('search');
            if ($search) {
                // Aplicar busca nas colunas pesquisáveis
                $searchableColumns = $this->getNestedSearchableColumns();
                if (!empty($searchableColumns)) {
                    $query->where(function ($q) use ($search, $searchableColumns) {
                        foreach ($searchableColumns as $column) {
                            $q->orWhere($column, 'LIKE', "%{$search}%");
                        }
                    });
                }
            }
        }

        // Aplicar ordenação se habilitado
        if ($request && $this->config['sortable']) {
            $sortColumn = $request->get('sort');
            $sortDirection = $request->get('direction', 'asc');
            
            if ($sortColumn && $this->isNestedColumnSortable($sortColumn)) {
                $query->orderBy($sortColumn, $sortDirection);
            }
        }

        // Paginar se habilitado
        if ($this->config['show_pagination']) {
            $data = $query->paginate($this->config['per_page']);
            
            return [
                'data' => $data->items(),
                'pagination' => [
                    'current_page' => $data->currentPage(),
                    'last_page' => $data->lastPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                ],
                'config' => $this->config,
                'columns' => $this->getNestedColumnsConfig(),
                'actions' => $this->getActionsConfig(),
            ];
        }

        // Sem paginação
        $data = $query->get();
        
        return [
            'data' => $data->toArray(),
            'pagination' => null,
            'config' => $this->config,
            'columns' => $this->getNestedColumnsConfig(),
            'actions' => $this->getActionsConfig(),
        ];
    }

    /**
     * Obtém colunas pesquisáveis da sub-tabela
     */
    protected function getNestedSearchableColumns(): array
    {
        $columns = $this->columns();
        $searchable = [];
        
        foreach ($columns as $column) {
            if ($column->isSearchable()) {
                $searchable[] = $column->getKey();
            }
        }
        
        return $searchable;
    }

    /**
     * Verifica se uma coluna da sub-tabela é ordenável
     */
    protected function isNestedColumnSortable(string $column): bool
    {
        $columns = $this->columns();
        
        foreach ($columns as $col) {
            if ($col->getKey() === $column && $col->isSortable()) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Obtém configuração das colunas da sub-tabela para o frontend
     */
    protected function getNestedColumnsConfig(): array
    {
        $columns = $this->columns();
        $config = [];
        
        foreach ($columns as $column) {
            $config[] = $column->toArray();
        }
        
        return $config;
    }

    /**
     * Obtém configuração das ações para o frontend
     */
    protected function getActionsConfig(): array
    {
        $actions = $this->actions();
        $config = [];
        
        foreach ($actions as $action) {
            $config[] = $action->toArray();
        }
        
        return $config;
    }

    /**
     * Obtém as configurações da sub-tabela
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Obtém o ID do pai
     */
    public function getParentId(): mixed
    {
        return $this->parentId;
    }

    /**
     * Obtém o item pai
     */
    public function getParentItem(): ?Model
    {
        return $this->parentItem;
    }

    /**
     * Método abstrato para definir colunas da sub-tabela
     */
    abstract protected function columns(): array;

    /**
     * Método para definir ações da sub-tabela (opcional)
     */
    protected function actions(): array
    {
        return [];
    }

    /**
     * Método para obter a query base (deve ser implementado ou usar trait)
     */
    abstract protected function getQuery(): Builder;
} 
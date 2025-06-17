<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;

/**
 * Trait para sistema de paginação avançado da tabela
 */
trait HasPagination
{
    /**
     * Configurações de paginação
     */
    protected array $paginationConfig = [
        'enabled' => true,
        'per_page' => 15,
        'max_per_page' => 100,
        'per_page_options' => [10, 15, 25, 50, 100],
        'simple_pagination' => false,
        'show_disabled' => true,
        'show_first_last' => true,
        'page_name' => 'page',
        'path' => null,
        'cursor_pagination' => false,
        'cursor_column' => 'id',
    ];

    /**
     * Cache de contagem total
     */
    protected ?int $totalCountCache = null;

    /**
     * Habilitar paginação
     */
    public function pagination(bool $enabled = true): static
    {
        $this->paginationConfig['enabled'] = $enabled;
        
        return $this;
    }

    /**
     * Definir itens por página
     */
    public function perPage(int $perPage): static
    {
        $this->paginationConfig['per_page'] = $perPage;
        
        return $this;
    }

    /**
     * Definir máximo de itens por página
     */
    public function maxPerPage(int $maxPerPage): static
    {
        $this->paginationConfig['max_per_page'] = $maxPerPage;
        
        return $this;
    }

    /**
     * Definir opções de itens por página
     */
    public function perPageOptions(array $options): static
    {
        $this->paginationConfig['per_page_options'] = $options;
        
        return $this;
    }

    /**
     * Habilitar paginação simples
     */
    public function simplePagination(bool $simple = true): static
    {
        $this->paginationConfig['simple_pagination'] = $simple;
        
        return $this;
    }

    /**
     * Habilitar paginação por cursor
     */
    public function cursorPagination(bool $enabled = true, string $column = 'id'): static
    {
        $this->paginationConfig['cursor_pagination'] = $enabled;
        $this->paginationConfig['cursor_column'] = $column;
        
        return $this;
    }

    /**
     * Definir nome do parâmetro de página
     */
    public function pageName(string $pageName): static
    {
        $this->paginationConfig['page_name'] = $pageName;
        
        return $this;
    }

    /**
     * Definir path para paginação
     */
    public function paginationPath(string $path): static
    {
        $this->paginationConfig['path'] = $path;
        
        return $this;
    }

    /**
     * Obter dados paginados otimizados
     */
    protected function getOptimizedPaginatedData(Builder $query, Request $request): array
    {
        if (!$this->paginationConfig['enabled']) {
            return $this->getAllData($query);
        }

        // Determinar tipo de paginação
        if ($this->paginationConfig['cursor_pagination']) {
            return $this->getCursorPaginatedData($query, $request);
        }

        if ($this->paginationConfig['simple_pagination']) {
            return $this->getSimplePaginatedData($query, $request);
        }

        return $this->getStandardPaginatedData($query, $request);
    }

    /**
     * Obter dados com paginação padrão
     */
    protected function getStandardPaginatedData(Builder $query, Request $request): array
    {
        $perPage = $this->getPerPageFromRequest($request);
        $page = $request->get($this->paginationConfig['page_name'], 1);

        // Otimização: usar contagem em cache se disponível
        $total = $this->getCachedTotalCount($query);

        $paginator = new LengthAwarePaginator(
            $query->forPage($page, $perPage)->get(),
            $total,
            $perPage,
            $page,
            [
                'path' => $this->getPaginationPath($request),
                'pageName' => $this->paginationConfig['page_name'],
            ]
        );

        return [
            'data' => $paginator->items(),
            'pagination' => $this->formatPaginationData($paginator),
        ];
    }

    /**
     * Obter dados com paginação simples
     */
    protected function getSimplePaginatedData(Builder $query, Request $request): array
    {
        $perPage = $this->getPerPageFromRequest($request);
        
        $paginator = $query->simplePaginate(
            $perPage,
            ['*'],
            $this->paginationConfig['page_name']
        );

        if ($this->paginationConfig['path']) {
            $paginator->withPath($this->paginationConfig['path']);
        }

        return [
            'data' => $paginator->items(),
            'pagination' => $this->formatSimplePaginationData($paginator),
        ];
    }

    /**
     * Obter dados com paginação por cursor
     */
    protected function getCursorPaginatedData(Builder $query, Request $request): array
    {
        $perPage = $this->getPerPageFromRequest($request);
        $cursor = $request->get('cursor');
        $column = $this->paginationConfig['cursor_column'];

        if ($cursor) {
            $query->where($column, '>', $cursor);
        }

        $results = $query->orderBy($column)->limit($perPage + 1)->get();
        
        $hasMore = $results->count() > $perPage;
        if ($hasMore) {
            $results->pop();
        }

        $nextCursor = $hasMore && $results->isNotEmpty() 
            ? $results->last()->{$column} 
            : null;

        return [
            'data' => $results->toArray(),
            'pagination' => [
                'type' => 'cursor',
                'per_page' => $perPage,
                'has_more' => $hasMore,
                'next_cursor' => $nextCursor,
                'cursor_column' => $column,
            ],
        ];
    }

    /**
     * Obter todos os dados (sem paginação)
     */
    protected function getAllData(Builder $query): array
    {
        $results = $query->get();

        return [
            'data' => $results->toArray(),
            'pagination' => [
                'type' => 'none',
                'total' => $results->count(),
                'per_page' => $results->count(),
                'current_page' => 1,
                'last_page' => 1,
            ],
        ];
    }

    /**
     * Obter itens por página da requisição
     */
    protected function getPerPageFromRequest(Request $request): int
    {
        $requestedPerPage = (int) $request->get('per_page', $this->paginationConfig['per_page']);
        
        // Validar se está nas opções permitidas
        if (!in_array($requestedPerPage, $this->paginationConfig['per_page_options'])) {
            $requestedPerPage = $this->paginationConfig['per_page'];
        }

        // Aplicar limite máximo
        return min($requestedPerPage, $this->paginationConfig['max_per_page']);
    }

    /**
     * Obter contagem total com cache
     */
    protected function getCachedTotalCount(Builder $query): int
    {
        if ($this->totalCountCache !== null) {
            return $this->totalCountCache;
        }

        // Otimização: usar count otimizado
        $this->totalCountCache = $this->getOptimizedCount($query);
        
        return $this->totalCountCache;
    }

    /**
     * Obter contagem otimizada
     */
    protected function getOptimizedCount(Builder $query): int
    {
        // Clone da query para não afetar a original
        $countQuery = clone $query;

        // Remover ordenação para otimizar contagem
        $countQuery->getQuery()->orders = null;
        $countQuery->getQuery()->unionOrders = null;

        // Remover select para otimizar
        $countQuery->getQuery()->columns = null;

        // Se tem group by, usar subquery
        if (!empty($countQuery->getQuery()->groups)) {
            return DB::table(DB::raw("({$countQuery->toSql()}) as sub"))
                ->mergeBindings($countQuery->getQuery())
                ->count();
        }

        return $countQuery->count();
    }

    /**
     * Obter path para paginação
     */
    protected function getPaginationPath(Request $request): string
    {
        if ($this->paginationConfig['path']) {
            return $this->paginationConfig['path'];
        }

        return $request->url();
    }

    /**
     * Formatar dados de paginação
     */
    protected function formatPaginationData(LengthAwarePaginator $paginator): array
    {
        return [
            'type' => 'standard',
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
            'has_pages' => $paginator->hasPages(),
            'has_more_pages' => $paginator->hasMorePages(),
            'on_first_page' => $paginator->onFirstPage(),
            'links' => $this->generatePaginationLinks($paginator),
            'per_page_options' => $this->paginationConfig['per_page_options'],
        ];
    }

    /**
     * Formatar dados de paginação simples
     */
    protected function formatSimplePaginationData($paginator): array
    {
        return [
            'type' => 'simple',
            'current_page' => $paginator->currentPage(),
            'per_page' => $paginator->perPage(),
            'has_more_pages' => $paginator->hasMorePages(),
            'on_first_page' => $paginator->onFirstPage(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
            'per_page_options' => $this->paginationConfig['per_page_options'],
        ];
    }

    /**
     * Gerar links de paginação
     */
    protected function generatePaginationLinks(LengthAwarePaginator $paginator): array
    {
        $links = [];
        $currentPage = $paginator->currentPage();
        $lastPage = $paginator->lastPage();

        // Link primeira página
        if ($this->paginationConfig['show_first_last'] && $currentPage > 3) {
            $links[] = [
                'url' => $paginator->url(1),
                'label' => '1',
                'active' => false,
                'type' => 'first',
            ];

            if ($currentPage > 4) {
                $links[] = [
                    'url' => null,
                    'label' => '...',
                    'active' => false,
                    'type' => 'gap',
                ];
            }
        }

        // Links das páginas próximas
        $start = max(1, $currentPage - 2);
        $end = min($lastPage, $currentPage + 2);

        for ($page = $start; $page <= $end; $page++) {
            $links[] = [
                'url' => $paginator->url($page),
                'label' => (string) $page,
                'active' => $page === $currentPage,
                'type' => 'page',
            ];
        }

        // Link última página
        if ($this->paginationConfig['show_first_last'] && $currentPage < $lastPage - 2) {
            if ($currentPage < $lastPage - 3) {
                $links[] = [
                    'url' => null,
                    'label' => '...',
                    'active' => false,
                    'type' => 'gap',
                ];
            }

            $links[] = [
                'url' => $paginator->url($lastPage),
                'label' => (string) $lastPage,
                'active' => false,
                'type' => 'last',
            ];
        }

        return $links;
    }

    /**
     * Limpar cache de contagem
     */
    public function clearCountCache(): static
    {
        $this->totalCountCache = null;
        
        return $this;
    }

    /**
     * Obter estatísticas de paginação
     */
    public function getPaginationStats(): array
    {
        return [
            'enabled' => $this->paginationConfig['enabled'],
            'type' => $this->paginationConfig['cursor_pagination'] ? 'cursor' : 
                     ($this->paginationConfig['simple_pagination'] ? 'simple' : 'standard'),
            'per_page' => $this->paginationConfig['per_page'],
            'max_per_page' => $this->paginationConfig['max_per_page'],
            'options' => $this->paginationConfig['per_page_options'],
            'cached_count' => $this->totalCountCache,
        ];
    }

    /**
     * Métodos de conveniência para configuração rápida
     */
    public function paginate(int $perPage = 15): static
    {
        return $this->pagination(true)->perPage($perPage);
    }

    public function noPagination(): static
    {
        return $this->pagination(false);
    }

    public function quickPagination(): static
    {
        return $this->simplePagination(true);
    }

    public function infiniteScroll(string $column = 'id'): static
    {
        return $this->cursorPagination(true, $column);
    }
} 
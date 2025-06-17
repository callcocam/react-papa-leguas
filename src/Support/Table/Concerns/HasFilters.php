<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\Filters\Filter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\TextFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\SelectFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\DateFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\BooleanFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\RelationFilter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait para gerenciar filtros da tabela (React Frontend)
 */
trait HasFilters
{
    /**
     * Filtros da tabela
     */
    protected array $filters = [];

    /**
     * Valores dos filtros aplicados
     */
    protected array $filterValues = [];

    /**
     * Configurações dos filtros para React
     */
    protected array $filtersConfig = [
        'layout' => 'horizontal', // horizontal, vertical, grid, sidebar
        'collapsible' => true,
        'collapsed' => false,
        'showClearAll' => true,
        'showApplyButton' => false,
        'autoApply' => true,
        'showActiveCount' => true,
        'position' => 'top', // top, bottom, left, right
        'size' => 'default', // sm, default, lg
        'spacing' => 'default', // tight, default, loose
        'grouping' => [
            'enabled' => false,
            'groups' => [],
        ],
        'persistence' => [
            'enabled' => false,
            'key' => null,
            'storage' => 'localStorage', // localStorage, sessionStorage
        ],
    ];

    /**
     * Adicionar filtro
     */
    public function filter(Filter $filter): static
    {
        $this->filters[$filter->getId()] = $filter;
        return $this;
    }

    /**
     * Adicionar múltiplos filtros
     */
    public function filters(array $filters): static
    {
        foreach ($filters as $filter) {
            if ($filter instanceof Filter) {
                $this->filter($filter);
            }
        }
        return $this;
    }

    /**
     * Configurar layout dos filtros React
     */
    public function filtersLayout(string $layout): static
    {
        $this->filtersConfig['layout'] = $layout;
        return $this;
    }

    /**
     * Configurar se filtros são colapsáveis React
     */
    public function filtersCollapsible(bool $collapsible = true, bool $collapsed = false): static
    {
        $this->filtersConfig['collapsible'] = $collapsible;
        $this->filtersConfig['collapsed'] = $collapsed;
        return $this;
    }

    /**
     * Configurar aplicação automática dos filtros React
     */
    public function filtersAutoApply(bool $autoApply = true): static
    {
        $this->filtersConfig['autoApply'] = $autoApply;
        $this->filtersConfig['showApplyButton'] = !$autoApply;
        return $this;
    }

    /**
     * Configurar posição dos filtros React
     */
    public function filtersPosition(string $position): static
    {
        $this->filtersConfig['position'] = $position;
        return $this;
    }

    /**
     * Configurar agrupamento de filtros React
     */
    public function filtersGrouping(array $groups): static
    {
        $this->filtersConfig['grouping'] = [
            'enabled' => true,
            'groups' => $groups,
        ];
        return $this;
    }

    /**
     * Configurar persistência dos filtros React
     */
    public function filtersPersistence(string $key, string $storage = 'localStorage'): static
    {
        $this->filtersConfig['persistence'] = [
            'enabled' => true,
            'key' => $key,
            'storage' => $storage,
        ];
        return $this;
    }

    /**
     * Definir valores dos filtros
     */
    public function filterValues(array $values): static
    {
        $this->filterValues = $values;
        
        // Aplicar valores aos filtros
        foreach ($values as $filterId => $value) {
            if (isset($this->filters[$filterId])) {
                $this->filters[$filterId]->value($value);
            }
        }
        
        return $this;
    }

    /**
     * Filtros básicos comuns
     */
    public function withBasicFilters(): static
    {
        $this->filters([
            TextFilter::globalSearch(['name', 'title', 'description']),
            SelectFilter::status(),
            DateFilter::period('created_at'),
            BooleanFilter::active(),
        ]);
        
        return $this;
    }

    /**
     * Filtros para posts/artigos
     */
    public function withPostFilters(): static
    {
        $this->filters([
            TextFilter::globalSearch(['title', 'content', 'excerpt']),
            RelationFilter::category(),
            RelationFilter::author(),
            RelationFilter::tags(),
            SelectFilter::status([
                'draft' => 'Rascunho',
                'published' => 'Publicado',
                'archived' => 'Arquivado',
            ]),
            BooleanFilter::featured(),
            DateFilter::period('published_at'),
        ]);
        
        return $this;
    }

    /**
     * Filtros para usuários
     */
    public function withUserFilters(): static
    {
        $this->filters([
            TextFilter::globalSearch(['name', 'email']),
            SelectFilter::status([
                'active' => 'Ativo',
                'inactive' => 'Inativo',
                'suspended' => 'Suspenso',
            ]),
            BooleanFilter::verified(),
            RelationFilter::department(),
            DateFilter::period('created_at'),
        ]);
        
        return $this;
    }

    /**
     * Filtros para produtos
     */
    public function withProductFilters(): static
    {
        $this->filters([
            TextFilter::globalSearch(['name', 'description', 'sku']),
            RelationFilter::category(),
            SelectFilter::status([
                'active' => 'Ativo',
                'inactive' => 'Inativo',
                'out_of_stock' => 'Sem Estoque',
            ]),
            BooleanFilter::featured(),
            DateFilter::period('created_at'),
        ]);
        
        return $this;
    }

    /**
     * Filtros para relacionamentos
     */
    public function withRelationFilters(string $parentModel): static
    {
        $this->filters([
            TextFilter::globalSearch(['name', 'title']),
            DateFilter::period('created_at'),
            BooleanFilter::active(),
        ]);
        
        return $this;
    }

    /**
     * Obter filtros
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Obter filtros visíveis
     */
    public function getVisibleFilters(): array
    {
        return array_filter($this->filters, fn($filter) => $filter->isVisible());
    }

    /**
     * Obter filtros com valores
     */
    public function getActiveFilters(): array
    {
        return array_filter($this->filters, fn($filter) => $filter->hasValue());
    }

    /**
     * Verificar se tem filtros
     */
    public function hasFilters(): bool
    {
        return !empty($this->filters);
    }

    /**
     * Verificar se tem filtros ativos
     */
    public function hasActiveFilters(): bool
    {
        return !empty($this->getActiveFilters());
    }

    /**
     * Aplicar filtros à query
     */
    public function applyFilters(Builder $query): Builder
    {
        foreach ($this->getActiveFilters() as $filter) {
            $query = $filter->apply($query);
        }
        
        return $query;
    }

    /**
     * Limpar todos os filtros
     */
    public function clearFilters(): static
    {
        foreach ($this->filters as $filter) {
            $filter->value(null);
        }
        
        $this->filterValues = [];
        return $this;
    }

    /**
     * Remover filtro específico
     */
    public function removeFilter(string $id): static
    {
        unset($this->filters[$id]);
        unset($this->filterValues[$id]);
        return $this;
    }

    /**
     * Obter filtros formatados para React
     */
    public function getFiltersForFrontend(): array
    {
        if (empty($this->filters)) {
            return [];
        }

        $filters = [];
        foreach ($this->getVisibleFilters() as $filter) {
            $filters[] = $filter->toArray();
        }

        return [
            'enabled' => true,
            'filters' => $filters,
            'activeCount' => count($this->getActiveFilters()),
            'totalCount' => count($this->getVisibleFilters()),
            'config' => $this->filtersConfig,
            'values' => $this->filterValues,
            'frontend' => [
                'component' => 'FilterBar',
                'layout' => $this->filtersConfig['layout'],
                'position' => $this->filtersConfig['position'],
                'config' => $this->filtersConfig,
            ],
        ];
    }

    /**
     * Obter resumo dos filtros ativos para React
     */
    public function getActiveFiltersSummary(): array
    {
        $activeFilters = $this->getActiveFilters();
        
        if (empty($activeFilters)) {
            return [];
        }

        $summary = [];
        foreach ($activeFilters as $filter) {
            $summary[] = [
                'id' => $filter->getId(),
                'label' => $filter->getLabel(),
                'value' => $filter->getValue(),
                'displayValue' => $this->getFilterDisplayValue($filter),
                'removable' => true,
            ];
        }

        return [
            'enabled' => true,
            'filters' => $summary,
            'count' => count($summary),
            'clearAllLabel' => 'Limpar todos os filtros',
            'frontend' => [
                'component' => 'ActiveFiltersSummary',
            ],
        ];
    }

    /**
     * Obter valor de exibição do filtro
     */
    protected function getFilterDisplayValue(Filter $filter): string
    {
        $value = $filter->getValue();
        
        if (is_array($value)) {
            return implode(', ', $value);
        }
        
        if (is_bool($value)) {
            return $value ? 'Sim' : 'Não';
        }
        
        return (string) $value;
    }
} 
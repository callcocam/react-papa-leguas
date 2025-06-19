<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\Filters\Filter;
use Illuminate\Http\Request;

/**
 * Trait para gerenciar filtros de tabela
 */
trait HasFilters
{
    protected array $filters = [];
    protected array $appliedFilters = [];

    /**
     * Método abstrato para definir filtros
     * Deve ser implementado pelas classes que usam este trait
     */
    abstract protected function filters(): array;

    /**
     * Boot do trait HasFilters
     */
    protected function bootHasFilters(): void
    {
        $this->loadFilters();
    }

    /**
     * Carregar filtros definidos
     */
    protected function loadFilters(): void
    {
        $filters = $this->filters();
        
        foreach ($filters as $filter) {
            if ($filter instanceof Filter) {
                $this->filters[$filter->getKey()] = $filter;
            }
        }
    }

    /**
     * Obter todos os filtros
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Obter filtro específico por key
     */
    public function getFilter(string $key): ?Filter
    {
        return $this->filters[$key] ?? null;
    }

    /**
     * Verificar se filtro existe
     */
    public function hasFilter(string $key): bool
    {
        return isset($this->filters[$key]);
    }

    /**
     * Obter filtros visíveis (não ocultos)
     */
    public function getVisibleFilters(): array
    {
        return array_filter($this->filters, fn($filter) => !$filter->isHidden());
    }

    /**
     * Processar valores de filtros da requisição
     */
    public function processFiltersFromRequest(Request $request): static
    {
        $filterValues = $request->get('filters', []);
        
        foreach ($this->filters as $key => $filter) {
            if (isset($filterValues[$key])) {
                $filter->setValue($filterValues[$key]);
                $this->appliedFilters[$key] = $filterValues[$key];
            }
        }

        return $this;
    }

    /**
     * Aplicar filtros à query
     */
    public function applyFilters($query): void
    {
        foreach ($this->filters as $filter) {
            if ($filter->hasValue()) {
                $filter->applyToQuery($query);
            }
        }
    }

    /**
     * Aplicar filtros específicos à query
     */
    public function applySpecificFilters($query, array $filterKeys): void
    {
        foreach ($filterKeys as $key) {
            $filter = $this->getFilter($key);
            if ($filter && $filter->hasValue()) {
                $filter->applyToQuery($query);
            }
        }
    }

    /**
     * Definir valores de filtros programaticamente
     */
    public function setFilterValues(array $values): static
    {
        foreach ($values as $key => $value) {
            $filter = $this->getFilter($key);
            if ($filter) {
                $filter->setValue($value);
                $this->appliedFilters[$key] = $value;
            }
        }

        return $this;
    }

    /**
     * Obter valores aplicados dos filtros
     */
    public function getAppliedFilters(): array
    {
        return $this->appliedFilters;
    }

    /**
     * Verificar se há filtros aplicados
     */
    public function hasAppliedFilters(): bool
    {
        return !empty($this->appliedFilters);
    }

    /**
     * Limpar todos os filtros
     */
    public function clearFilters(): static
    {
        foreach ($this->filters as $filter) {
            $filter->setValue(null);
        }
        
        $this->appliedFilters = [];
        
        return $this;
    }

    /**
     * Limpar filtros específicos
     */
    public function clearSpecificFilters(array $filterKeys): static
    {
        foreach ($filterKeys as $key) {
            $filter = $this->getFilter($key);
            if ($filter) {
                $filter->setValue(null);
                unset($this->appliedFilters[$key]);
            }
        }

        return $this;
    }

    /**
     * Obter configuração dos filtros para serialização
     */
    public function getFiltersConfig(): array
    {
        $config = [];
        
        foreach ($this->getVisibleFilters() as $filter) {
            $config[] = $filter->toArray();
        }

        return $config;
    }

    /**
     * Contar filtros visíveis
     */
    public function countVisibleFilters(): int
    {
        return count($this->getVisibleFilters());
    }

    /**
     * Contar total de filtros
     */
    public function countFilters(): int
    {
        return count($this->filters);
    }

    /**
     * Contar filtros aplicados
     */
    public function countAppliedFilters(): int
    {
        return count(array_filter($this->filters, fn($filter) => $filter->hasValue()));
    }

    /**
     * Verificar se filtro específico está aplicado
     */
    public function isFilterApplied(string $key): bool
    {
        $filter = $this->getFilter($key);
        return $filter && $filter->hasValue();
    }

    /**
     * Obter resumo dos filtros aplicados
     */
    public function getAppliedFiltersSummary(): array
    {
        $summary = [];
        
        foreach ($this->filters as $key => $filter) {
            if ($filter->hasValue()) {
                $summary[] = [
                    'key' => $key,
                    'label' => $filter->getLabel(),
                    'value' => $filter->getValue(),
                    'type' => $filter->getType(),
                ];
            }
        }

        return $summary;
    }

    /**
     * Gerar URL com filtros aplicados
     */
    public function getFilteredUrl(string $baseUrl = ''): string
    {
        if (empty($this->appliedFilters)) {
            return $baseUrl;
        }

        $queryParams = ['filters' => $this->appliedFilters];
        $queryString = http_build_query($queryParams);
        
        $separator = str_contains($baseUrl, '?') ? '&' : '?';
        
        return $baseUrl . $separator . $queryString;
    }

    /**
     * Validar valores de filtros
     */
    public function validateFilters(array $filterValues): array
    {
        $errors = [];
        
        foreach ($filterValues as $key => $value) {
            $filter = $this->getFilter($key);
            
            if (!$filter) {
                $errors[$key] = "Filtro '{$key}' não existe.";
                continue;
            }

            // Validações específicas podem ser adicionadas aqui
            // Por exemplo, validar formato de data, valores permitidos, etc.
        }

        return $errors;
    }

    /**
     * Obter meta informações dos filtros
     */
    public function getFiltersMeta(): array
    {
        return [
            'total_filters' => $this->countFilters(),
            'visible_filters' => $this->countVisibleFilters(),
            'applied_filters' => $this->countAppliedFilters(),
            'has_applied_filters' => $this->hasAppliedFilters(),
            'applied_summary' => $this->getAppliedFiltersSummary(),
        ];
    }
} 
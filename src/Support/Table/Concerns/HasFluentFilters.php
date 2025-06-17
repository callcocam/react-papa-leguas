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

/**
 * Trait para configuração fluente de filtros
 */
trait HasFluentFilters
{
    /**
     * Último filtro adicionado (para configuração fluente)
     */
    protected ?Filter $lastFilter = null;

    /**
     * Adicionar filtro de texto
     */
    public function textFilter(string $key, string $label = null): static
    {
        $filter = TextFilter::make($key, $label);
        $this->filters[$key] = $filter;
        $this->lastFilter = $filter;
        
        return $this;
    }

    /**
     * Adicionar filtro de seleção
     */
    public function selectFilter(string $key, string $label = null): static
    {
        $filter = SelectFilter::make($key, $label);
        $this->filters[$key] = $filter;
        $this->lastFilter = $filter;
        
        return $this;
    }

    /**
     * Adicionar filtro de data
     */
    public function dateFilter(string $key, string $label = null): static
    {
        $filter = DateFilter::make($key, $label);
        $this->filters[$key] = $filter;
        $this->lastFilter = $filter;
        
        return $this;
    }

    /**
     * Adicionar filtro de range de data
     */
    public function dateRangeFilter(string $key, string $label = null): static
    {
        $filter = DateFilter::make($key, $label)->range();
        $this->filters[$key] = $filter;
        $this->lastFilter = $filter;
        
        return $this;
    }

    /**
     * Adicionar filtro booleano
     */
    public function booleanFilter(string $key, string $label = null): static
    {
        $filter = BooleanFilter::make($key, $label);
        $this->filters[$key] = $filter;
        $this->lastFilter = $filter;
        
        return $this;
    }

    /**
     * Adicionar filtro de relacionamento
     */
    public function relationFilter(string $key, string $label = null): static
    {
        $filter = RelationFilter::make($key, $label);
        $this->filters[$key] = $filter;
        $this->lastFilter = $filter;
        
        return $this;
    }

    /**
     * Capturar chamadas de métodos para filtros fluentes
     */
    protected function handleFilterMethod(string $method, array $arguments)
    {
        // Se há um último filtro e o método existe nele, aplicar ao filtro
        if ($this->lastFilter && method_exists($this->lastFilter, $method)) {
            $result = $this->lastFilter->{$method}(...$arguments);
            
            // Se o método retorna o filtro, manter a fluência retornando a tabela
            if ($result === $this->lastFilter) {
                return $this;
            }
            
            return $result;
        }
        
        return null; // Método não encontrado nos filtros
    }

    /**
     * Limpar último filtro (usado internamente)
     */
    protected function clearLastFilter(): static
    {
        $this->lastFilter = null;
        return $this;
    }
} 
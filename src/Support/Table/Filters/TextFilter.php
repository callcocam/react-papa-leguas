<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

/**
 * Filtro de texto para busca em colunas
 */
class TextFilter extends Filter
{
    protected array $searchColumns = [];
    protected string $operator = 'LIKE';
    protected bool $caseSensitive = false;
    protected int $minLength = 0;

    /**
     * Definir colunas para busca
     */
    public function searchColumns(array $columns): static
    {
        $this->searchColumns = $columns;
        return $this;
    }

    /**
     * Definir operador de busca
     */
    public function operator(string $operator): static
    {
        $this->operator = strtoupper($operator);
        return $this;
    }

    /**
     * Definir se busca é case sensitive
     */
    public function caseSensitive(bool $sensitive = true): static
    {
        $this->caseSensitive = $sensitive;
        return $this;
    }

    /**
     * Definir comprimento mínimo para busca
     */
    public function minLength(int $length): static
    {
        $this->minLength = $length;
        return $this;
    }

    /**
     * Configuração para busca global (múltiplas colunas)
     */
    public function global(array $columns = []): static
    {
        $this->searchColumns = $columns;
        $this->placeholder = $this->placeholder ?? 'Buscar...';
        return $this;
    }

    /**
     * Configuração para busca exata
     */
    public function exact(): static
    {
        $this->operator = '=';
        return $this;
    }

    /**
     * Configuração para busca que começa com
     */
    public function startsWith(): static
    {
        $this->config('search_type', 'starts_with');
        return $this;
    }

    /**
     * Configuração para busca que termina com
     */
    public function endsWith(): static
    {
        $this->config('search_type', 'ends_with');
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function apply($query, mixed $value): void
    {
        $searchValue = trim((string) $value);
        
        // Verificar comprimento mínimo
        if (strlen($searchValue) < $this->minLength) {
            return;
        }

        // Se não há colunas definidas, usar a key do filtro
        $columns = !empty($this->searchColumns) ? $this->searchColumns : [$this->key];

        // Preparar valor de busca baseado no tipo
        $searchType = $this->getConfig('search_type', 'contains');
        $searchPattern = $this->prepareSearchPattern($searchValue, $searchType);

        // Aplicar busca
        if (count($columns) === 1) {
            // Busca em uma coluna
            $this->applySingleColumnSearch($query, $columns[0], $searchPattern);
        } else {
            // Busca em múltiplas colunas (OR)
            $query->where(function ($q) use ($columns, $searchPattern) {
                foreach ($columns as $column) {
                    $q->orWhere(function ($subQ) use ($column, $searchPattern) {
                        $this->applySingleColumnSearch($subQ, $column, $searchPattern);
                    });
                }
            });
        }
    }

    /**
     * Preparar padrão de busca baseado no tipo
     */
    protected function prepareSearchPattern(string $value, string $type): string
    {
        if (!$this->caseSensitive) {
            $value = strtolower($value);
        }

        return match ($type) {
            'exact' => $value,
            'starts_with' => $value . '%',
            'ends_with' => '%' . $value,
            'contains' => '%' . $value . '%',
            default => '%' . $value . '%',
        };
    }

    /**
     * Aplicar busca em uma coluna
     */
    protected function applySingleColumnSearch($query, string $column, string $pattern): void
    {
        // Verificar se é busca em relacionamento
        if (str_contains($column, '.')) {
            $this->applyRelationshipSearch($query, $column, $pattern);
            return;
        }

        // Busca normal na coluna
        if ($this->operator === 'LIKE') {
            if ($this->caseSensitive) {
                $query->where($column, 'LIKE', $pattern);
            } else {
                $query->whereRaw("LOWER({$column}) LIKE ?", [strtolower($pattern)]);
            }
        } else {
            $query->where($column, $this->operator, $pattern);
        }
    }

    /**
     * Aplicar busca em relacionamento
     */
    protected function applyRelationshipSearch($query, string $column, string $pattern): void
    {
        [$relation, $relationColumn] = explode('.', $column, 2);

        $query->whereHas($relation, function ($q) use ($relationColumn, $pattern) {
            $this->applySingleColumnSearch($q, $relationColumn, $pattern);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'search_columns' => $this->searchColumns,
            'operator' => $this->operator,
            'case_sensitive' => $this->caseSensitive,
            'min_length' => $this->minLength,
        ]);
    }
} 
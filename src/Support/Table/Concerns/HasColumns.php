<?php

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\Columns\Column;

trait HasColumns
{
    protected array $columns = [];

    /**
     * Define as colunas da tabela
     * Deve ser implementado pelas classes que usam este trait
     * Deve retornar array de instâncias de Column
     */
    abstract protected function columns(): array;

    /**
     * Inicializar colunas
     */
    protected function bootHasColumns()
    {
        $this->columns = $this->columns();
        $this->validateColumns();
    }

    /**
     * Validar se todas as colunas são instâncias de Column
     */
    protected function validateColumns(): void
    {
        foreach ($this->columns as $index => $column) {
            if (!$column instanceof Column) {
                $type = is_object($column) ? get_class($column) : gettype($column);
                throw new \InvalidArgumentException(
                    "Coluna no índice {$index} deve ser uma instância de Column, {$type} fornecido."
                );
            }
        }
    }

    /**
     * Formatar uma linha de dados usando as classes de coluna
     */
    protected function formatRow($row): array
    {
        $formatted = [];
        
        foreach ($this->columns as $column) {
            if (!$column->isHidden()) {
                $key = $column->getKey();
                $formatted[$key] = $column->formatValue($row);
            }
        }

        return $formatted;
    }

    /**
     * Obter configuração das colunas para serialização
     */
    public function getColumnsConfig(): array
    {
        $config = [];
        
        foreach ($this->columns as $column) {
            if (!$column->isHidden()) {
                $config[] = $column->toArray();
            }
        }

        return $config;
    }

    /**
     * Obter colunas configuradas (instâncias de Column)
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Verificar se tem colunas configuradas
     */
    public function hasColumns(): bool
    {
        return !empty($this->columns);
    }

    /**
     * Obter coluna específica por key
     */
    public function getColumn(string $key): ?Column
    {
        foreach ($this->columns as $column) {
            if ($column->getKey() === $key) {
                return $column;
            }
        }
        return null;
    }

    /**
     * Verificar se coluna existe
     */
    public function hasColumn(string $key): bool
    {
        return $this->getColumn($key) !== null;
    }

    /**
     * Obter colunas pesquisáveis
     */
    public function getSearchableColumns(): array
    {
        return array_filter($this->columns, fn($column) => $column->isSearchable());
    }

    /**
     * Obter colunas ordenáveis
     */
    public function getSortableColumns(): array
    {
        return array_filter($this->columns, fn($column) => $column->isSortable());
    }

    /**
     * Obter keys das colunas pesquisáveis
     */
    public function getSearchableColumnKeys(): array
    {
        return array_map(
            fn($column) => $column->getKey(),
            $this->getSearchableColumns()
        );
    }

    /**
     * Obter keys das colunas ordenáveis
     */
    public function getSortableColumnKeys(): array
    {
        return array_map(
            fn($column) => $column->getKey(),
            $this->getSortableColumns()
        );
    }

    /**
     * Verificar se uma coluna é pesquisável
     */
    public function isColumnSearchable(string $key): bool
    {
        $column = $this->getColumn($key);
        return $column ? $column->isSearchable() : false;
    }

    /**
     * Verificar se uma coluna é ordenável
     */
    public function isColumnSortable(string $key): bool
    {
        $column = $this->getColumn($key);
        return $column ? $column->isSortable() : false;
    }

    /**
     * Contar colunas visíveis
     */
    public function countVisibleColumns(): int
    {
        return count(array_filter($this->columns, fn($column) => !$column->isHidden()));
    }

    /**
     * Contar total de colunas
     */
    public function countColumns(): int
    {
        return count($this->columns);
    }
}
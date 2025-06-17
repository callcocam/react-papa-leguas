<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\Columns\Column;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\EditableColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BooleanColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\ImageColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\NumberColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\CurrencyColumn;
use Closure;

/**
 * Trait para gerenciar colunas da tabela com formatação avançada
 */
trait HasColumns
{
    /**
     * Coleção de colunas
     */
    protected array $columns = [];

    /**
     * Adicionar coluna genérica
     */
    public function column(string $key, string $label = null): static
    {
        $column = new Column($key, $label);
        $this->columns[$key] = $column;
        
        return $this;
    }

    /**
     * Adicionar coluna de texto
     */
    public function textColumn(string $key, string $label = null): static
    {
        $column = new TextColumn($key, $label);
        $this->columns[$key] = $column;
        
        // Definir como última coluna para configuração fluente
        if (property_exists($this, 'lastColumn')) {
            $this->lastColumn = $column;
        }
        
        return $this;
    }

    /**
     * Adicionar coluna com badge
     */
    public function badgeColumn(string $key, string $label = null): static
    {
        $column = new BadgeColumn($key, $label);
        $this->columns[$key] = $column;
        
        if (property_exists($this, 'lastColumn')) {
            $this->lastColumn = $column;
        }
        
        return $this;
    }

    /**
     * Adicionar coluna de data
     */
    public function dateColumn(string $key, string $label = null): static
    {
        $column = new DateColumn($key, $label);
        $this->columns[$key] = $column;
        
        if (property_exists($this, 'lastColumn')) {
            $this->lastColumn = $column;
        }
        
        return $this;
    }

    /**
     * Adicionar coluna editável
     */
    public function editableColumn(string $key, string $label = null): static
    {
        $column = new EditableColumn($key, $label);
        $this->columns[$key] = $column;
        
        if (property_exists($this, 'lastColumn')) {
            $this->lastColumn = $column;
        }
        
        return $this;
    }

    /**
     * Adicionar coluna booleana
     */
    public function booleanColumn(string $key, string $label = null): static
    {
        $column = new BooleanColumn($key, $label);
        $this->columns[$key] = $column;
        
        if (property_exists($this, 'lastColumn')) {
            $this->lastColumn = $column;
        }
        
        return $this;
    }

    /**
     * Adicionar coluna de imagem
     */
    public function imageColumn(string $key, string $label = null): static
    {
        $column = new ImageColumn($key, $label);
        $this->columns[$key] = $column;
        
        return $this;
    }

    /**
     * Adicionar coluna numérica
     */
    public function numberColumn(string $key, string $label = null): static
    {
        $column = new NumberColumn($key, $label);
        $this->columns[$key] = $column;
        
        return $this;
    }

    /**
     * Adicionar coluna de moeda
     */
    public function currencyColumn(string $key, string $label = null): static
    {
        $column = new CurrencyColumn($key, $label);
        $this->columns[$key] = $column;
        
        return $this;
    }

    /**
     * Adicionar múltiplas colunas
     */
    public function columns(array $columns): static
    {
        foreach ($columns as $key => $column) {
            if ($column instanceof Column) {
                $this->columns[$key] = $column;
            } elseif (is_array($column)) {
                $this->column($key, $column['label'] ?? null)
                     ->configure($column);
            }
        }
        
        return $this;
    }

    /**
     * Obter todas as colunas
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Obter coluna específica
     */
    public function getColumn(string $key): ?Column
    {
        return $this->columns[$key] ?? null;
    }

    /**
     * Verificar se coluna existe
     */
    public function hasColumn(string $key): bool
    {
        return isset($this->columns[$key]);
    }

    /**
     * Remover coluna
     */
    public function removeColumn(string $key): static
    {
        unset($this->columns[$key]);
        
        return $this;
    }

    /**
     * Obter colunas formatadas para o frontend
     */
    public function getColumnsForFrontend(): array
    {
        $columns = [];
        
        foreach ($this->columns as $key => $column) {
            $columns[] = [
                'key' => $key,
                'label' => $column->getLabel(),
                'type' => $column->getType(),
                'component' => $column->getComponent(),
                'sortable' => $column->isSortable(),
                'searchable' => $column->isSearchable(),
                'visible' => $column->isVisible(),
                'width' => $column->getWidth(),
                'align' => $column->getAlign(),
                'format' => $column->getFormatConfig(),
                'validation' => $column->getValidationRules(),
                'permissions' => $column->getPermissions(),
                'meta' => $column->getMeta(),
            ];
        }
        
        return $columns;
    }

    /**
     * Aplicar formatação das colunas aos dados
     */
    protected function applyColumnFormatting(array $data): array
    {
        $formattedData = [];
        
        foreach ($data as $record) {
            $formattedRecord = [];
            
            foreach ($this->columns as $key => $column) {
                $value = $this->getNestedValue($record, $key);
                $formattedRecord[$key] = $column->formatValue($value, $record);
            }
            
            // Manter dados originais para referência
            $formattedRecord['_original'] = $record;
            $formattedData[] = $formattedRecord;
        }
        
        return $formattedData;
    }

    /**
     * Obter valor aninhado de um array/objeto
     */
    protected function getNestedValue($data, string $key)
    {
        if (is_array($data)) {
            return data_get($data, $key);
        }
        
        if (is_object($data)) {
            return data_get($data, $key);
        }
        
        return null;
    }

    /**
     * Obter colunas visíveis
     */
    public function getVisibleColumns(): array
    {
        return array_filter($this->columns, fn($column) => $column->isVisible());
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
     * Configurar todas as colunas
     */
    public function configureColumns(Closure $callback): static
    {
        foreach ($this->columns as $column) {
            $callback($column);
        }
        
        return $this;
    }
} 
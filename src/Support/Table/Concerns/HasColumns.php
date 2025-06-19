<?php

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

trait HasColumns
{
    protected $columns = [];

    /**
     * Define as colunas da tabela
     * Deve ser implementado pelas classes que usam este trait
     */
    abstract protected function columns(): array;

    /**
     * Inicializar colunas
     */
    protected function bootHasColumns()
    {
        $this->columns = $this->columns();
    }

    /**
     * Formatar uma linha de dados
     */
    protected function formatRow($row): array
    {
        $formatted = [];
        
        foreach ($this->columns as $column) {
            $key = $column['key'] ?? $column['field'] ?? null;
            if ($key) {
                $formatted[$key] = $this->formatValue($row, $column, $key);
            }
        }

        return $formatted;
    }

    /**
     * Formatar um valor específico
     */
    protected function formatValue($row, array $column, string $key)
    {
        $value = data_get($row, $key);

        // Se for um enum, obter o valor string
        if (is_object($value) && enum_exists(get_class($value))) {
            $enumValue = $value->value;
            $enumLabel = method_exists($value, 'label') ? $value->label() : ucfirst($enumValue);
            
            // Para badges, usar métodos do enum se disponíveis
            if (isset($column['type']) && $column['type'] === 'badge') {
                return [
                    'value' => $enumValue,
                    'type' => 'badge',
                    'variant' => method_exists($value, 'badgeVariant') ? $value->badgeVariant() : $this->getBadgeVariant($enumValue),
                    'label' => $enumLabel
                ];
            }
            
            // Para outros tipos, usar o valor do enum
            $value = $enumValue;
        }

        // Aplicar formatação baseada no tipo da coluna
        if (isset($column['type'])) {
            switch ($column['type']) {
                case 'badge':
                    return [
                        'value' => $value,
                        'type' => 'badge',
                        'variant' => $this->getBadgeVariant($value),
                        'label' => $this->getBadgeLabel($value)
                    ];
                case 'currency':
                    return [
                        'value' => $value,
                        'type' => 'currency',
                        'formatted' => 'R$ ' . number_format($value, 2, ',', '.')
                    ];
                case 'date':
                    return [
                        'value' => $value,
                        'type' => 'date',
                        'formatted' => $value ? $value->format('d/m/Y') : null
                    ];
            }
        }

        return $value;
    }

    /**
     * Obter variante do badge baseado no valor
     */
    protected function getBadgeVariant($value): string
    {
        return match($value) {
            'active', 'published' => 'success',
            'inactive', 'draft' => 'secondary',
            'archived' => 'warning',
            'deleted' => 'destructive',
            default => 'default'
        };
    }

    /**
     * Obter label do badge baseado no valor
     */
    protected function getBadgeLabel($value): string
    {
        return match($value) {
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'published' => 'Publicado',
            'draft' => 'Rascunho',
            'archived' => 'Arquivado',
            'deleted' => 'Excluído',
            default => ucfirst($value)
        };
    }

    /**
     * Obter colunas configuradas
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
    public function getColumn(string $key): ?array
    {
        foreach ($this->columns as $column) {
            if (($column['key'] ?? $column['field'] ?? null) === $key) {
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
}
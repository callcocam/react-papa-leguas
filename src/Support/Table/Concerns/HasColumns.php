<?php

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\Columns\Column;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasCasts;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\CurrencyCast;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\DateCast;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\StatusCast;

trait HasColumns
{
    use HasCasts;
    
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
        $this->registerDefaultCasts();
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
                
                // Aplicar casts automáticos antes da formatação da coluna
                $value = $this->getColumnValue($row, $key);
                $castedValue = $this->applyCastsToColumn($value, $key, $row);
                
                // Usar valor com cast aplicado
                $formatted[$key] = $column->formatValue($row, $castedValue);
            }
        }

        // ✅ PROCESSAR AÇÕES POR ITEM - Contexto correto com $row
        $formatted['_actions'] = $this->getActionsForItem($row);

        return $formatted;
    }

    /**
     * Obter ações específicas para um item
     */
    protected function getActionsForItem($item): array
    {
        // Verificar se o trait HasActions está sendo usado
        if (!method_exists($this, 'getActions')) {
            return [];
        }

        try {
            $actions = [];
            $context = ['table' => $this];

            foreach ($this->getActions() as $action) {
                // Definir contexto do item para a ação
                $action->setContext($context);
                
                // Verificar visibilidade com o item específico
                if ($action->isVisible($item, $context)) {
                    // ✅ CHAMADA DIRETA: toArray() resolve todas as closures com o contexto
                    $actionData = $action->toArray($item, $context);
                    
                    // Adicionar ID do item para referência no frontend
                    $actionData['item_id'] = $item->id ?? null;
                    
                    $actions[] = $actionData;
                }
            }

            return $actions;
        } catch (\Exception $e) {
            // Log do erro mas não quebrar a tabela
            \Illuminate\Support\Facades\Log::warning('Erro ao processar ações para item: ' . $e->getMessage(), [
                'item_id' => $item->id ?? 'unknown',
                'exception' => $e
            ]);
            
            return [];
        }
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

    /**
     * Registra casts padrão do sistema
     */
    protected function registerDefaultCasts(): void
    {
        // Registrar casts padrão ordenados por prioridade
        $this->registerCasts([
            DateCast::brazilian(),
            CurrencyCast::brl(),
            StatusCast::statusBadge(),
        ]);
    }

    /**
     * Aplica casts automáticos a uma coluna específica
     */
    protected function applyCastsToColumn(mixed $value, string $column, $row): mixed
    {
        // Obter instância da coluna
        $columnInstance = $this->getColumn($column);
        
        if (!$columnInstance) {
            return $value;
        }

        // Preparar contexto para os casts
        $context = [
            'column' => $columnInstance,
            'column_key' => $column,
            'row' => $row,
            'table' => $this,
            'column_casts' => $this->getColumnCasts(),
        ];

        // 1. Aplicar casts específicos da coluna primeiro
        if ($columnInstance->hasCasts()) {
            $value = $columnInstance->applyCasts($value, $context);
        }

        // 2. Aplicar casts automáticos apenas se não desabilitados
        if (!$columnInstance->isAutoCastsDisabled()) {
            $value = $this->applyCasts($value, $column, $context);
        }

        return $value;
    }

    /**
     * Obtém valor de uma coluna do row
     */
    protected function getColumnValue($row, string $key): mixed
    {
        if (is_array($row)) {
            return $row[$key] ?? null;
        }
        
        if (is_object($row)) {
            // Tentar propriedade direta
            if (property_exists($row, $key)) {
                return $row->{$key};
            }
            
            // Tentar método getter
            $getter = 'get' . ucfirst($key);
            if (method_exists($row, $getter)) {
                return $row->{$getter}();
            }
            
            // Tentar acessor mágico
            if (method_exists($row, '__get')) {
                return $row->{$key};
            }
        }
        
        return null;
    }

    /**
     * Obtém configurações de casts por coluna
     */
    protected function getColumnCasts(): array
    {
        $casts = [];
        
        foreach ($this->columns as $column) {
            $key = $column->getKey();
            
            // Detectar cast baseado no tipo da coluna
            $columnType = $column->getType();
            
            switch ($columnType) {
                case 'date':
                    $casts[$key] = 'date';
                    break;
                case 'currency':
                    $casts[$key] = 'currency';
                    break;
                case 'badge':
                    $casts[$key] = 'status';
                    break;
                case 'boolean':
                    $casts[$key] = 'status';
                    break;
            }
        }
        
        return $casts;
    }

    /**
     * Registra cast personalizado para uma coluna
     */
    public function setCastForColumn(string $column, string $castType): static
    {
        // Implementar se necessário para configuração manual
        return $this;
    }

    /**
     * Obtém informações sobre casts aplicados
     */
    public function getCastsInfo(): array
    {
        $columnCastsInfo = [];
        
        foreach ($this->getColumns() as $column) {
            $key = $column->getKey();
            $columnCastsInfo[$key] = $column->getCastsConfig();
        }

        return [
            'registered_casts' => count($this->getCasts()),
            'column_casts' => $this->getColumnCasts(),
            'cast_types' => array_map(fn($cast) => $cast->getType(), $this->getCasts()),
            'column_specific_casts' => $columnCastsInfo,
            'total_column_casts' => array_sum(array_map(fn($col) => count($col->getCasts()), $this->getColumns())),
        ];
    }
}
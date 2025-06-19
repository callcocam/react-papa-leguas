<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\Casts\Contracts\CastInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait HasCasts
{
    /**
     * Casts registrados
     */
    protected array $casts = [];

    /**
     * Cache de casts por tipo
     */
    protected array $castsByType = [];

    /**
     * Registra um cast
     */
    public function registerCast(CastInterface $cast): static
    {
        $this->casts[] = $cast;
        
        // Limpar cache
        $this->castsByType = [];
        
        return $this;
    }

    /**
     * Registra múltiplos casts
     */
    public function registerCasts(array $casts): static
    {
        foreach ($casts as $cast) {
            if ($cast instanceof CastInterface) {
                $this->registerCast($cast);
            }
        }
        
        return $this;
    }

    /**
     * Remove um cast por tipo
     */
    public function removeCast(string $type): static
    {
        $this->casts = array_filter($this->casts, function ($cast) use ($type) {
            return $cast->getType() !== $type;
        });
        
        // Limpar cache
        $this->castsByType = [];
        
        return $this;
    }

    /**
     * Obtém todos os casts registrados
     */
    public function getCasts(): array
    {
        return $this->casts;
    }

    /**
     * Obtém casts por tipo
     */
    public function getCastsByType(string $type): array
    {
        if (!isset($this->castsByType[$type])) {
            $this->castsByType[$type] = array_filter($this->casts, function ($cast) use ($type) {
                return $cast->getType() === $type;
            });
            
            // Ordenar por prioridade (maior primeiro)
            usort($this->castsByType[$type], function ($a, $b) {
                return $b->getPriority() <=> $a->getPriority();
            });
        }
        
        return $this->castsByType[$type];
    }

    /**
     * Aplica casts a um valor
     */
    public function applyCasts(mixed $value, string $column, array $context = []): mixed
    {
        // Detectar tipo baseado no valor ou contexto
        $detectedType = $this->detectCastType($value, $column, $context);
        
        if (!$detectedType) {
            return $value;
        }
        
        // Obter casts para o tipo detectado
        $casts = $this->getCastsByType($detectedType);
        
        if (empty($casts)) {
            return $value;
        }
        
        // Aplicar pipeline de casts
        return $this->applyCastPipeline($value, $casts, $context);
    }

    /**
     * Aplica pipeline de casts
     */
    protected function applyCastPipeline(mixed $value, array $casts, array $context): mixed
    {
        $result = $value;
        
        foreach ($casts as $cast) {
            try {
                // Verificar se o cast pode processar o valor atual
                if ($cast->canCast($result)) {
                    $result = $cast->cast($result, $context);
                }
            } catch (\Exception $e) {
                Log::warning("Erro no pipeline de casts: {$e->getMessage()}", [
                    'cast' => get_class($cast),
                    'value' => $result,
                    'context' => $context,
                ]);
                
                // Continuar com o próximo cast em caso de erro
                continue;
            }
        }
        
        return $result;
    }

    /**
     * Detecta o tipo de cast baseado no valor e contexto
     */
    protected function detectCastType(mixed $value, string $column, array $context): ?string
    {
        // 1. Verificar se há cast explícito no contexto
        if (isset($context['cast_type'])) {
            return $context['cast_type'];
        }
        
        // 2. Verificar se há cast definido para a coluna
        if (isset($context['column_casts'][$column])) {
            return $context['column_casts'][$column];
        }
        
        // 3. Detectar automaticamente baseado no valor
        return $this->autoDetectCastType($value, $column, $context);
    }

    /**
     * Auto-detecta o tipo de cast baseado no valor
     */
    protected function autoDetectCastType(mixed $value, string $column, array $context): ?string
    {
        // Verificar cada cast automático registrado
        foreach ($this->casts as $cast) {
            if ($cast->isAutomatic() && $cast->canCast($value)) {
                return $cast->getType();
            }
        }
        
        // Detecção baseada em padrões comuns
        if ($this->isDateValue($value, $column)) {
            return 'date';
        }
        
        if ($this->isCurrencyValue($value, $column)) {
            return 'currency';
        }
        
        if ($this->isStatusValue($value, $column)) {
            return 'status';
        }
        
        if ($this->isBooleanValue($value, $column)) {
            return 'boolean';
        }
        
        return null;
    }

    /**
     * Verifica se é um valor de data
     */
    protected function isDateValue(mixed $value, string $column): bool
    {
        // Verificar por nome da coluna
        $dateColumns = ['created_at', 'updated_at', 'deleted_at', 'date', 'datetime', 'timestamp'];
        if (in_array($column, $dateColumns)) {
            return true;
        }
        
        // Verificar por sufixo da coluna
        $dateSuffixes = ['_at', '_date', '_time'];
        foreach ($dateSuffixes as $suffix) {
            if (str_ends_with($column, $suffix)) {
                return true;
            }
        }
        
        // Verificar se o valor é uma data válida
        if (is_string($value) && strtotime($value) !== false) {
            return true;
        }
        
        return false;
    }

    /**
     * Verifica se é um valor monetário
     */
    protected function isCurrencyValue(mixed $value, string $column): bool
    {
        // Verificar por nome da coluna
        $currencyColumns = ['price', 'cost', 'amount', 'total', 'subtotal', 'value'];
        if (in_array($column, $currencyColumns)) {
            return true;
        }
        
        // Verificar por sufixo da coluna
        $currencySuffixes = ['_price', '_cost', '_amount', '_total', '_value'];
        foreach ($currencySuffixes as $suffix) {
            if (str_ends_with($column, $suffix)) {
                return true;
            }
        }
        
        // Verificar se é um valor numérico
        return is_numeric($value);
    }

    /**
     * Verifica se é um valor de status
     */
    protected function isStatusValue(mixed $value, string $column): bool
    {
        // Verificar por nome da coluna
        $statusColumns = ['status', 'state', 'condition'];
        if (in_array($column, $statusColumns)) {
            return true;
        }
        
        // Verificar se é um enum conhecido
        if (is_object($value) && enum_exists(get_class($value))) {
            return true;
        }
        
        return false;
    }

    /**
     * Verifica se é um valor booleano
     */
    protected function isBooleanValue(mixed $value, string $column): bool
    {
        // Verificar por prefixo da coluna
        $booleanPrefixes = ['is_', 'has_', 'can_', 'should_', 'will_'];
        foreach ($booleanPrefixes as $prefix) {
            if (str_starts_with($column, $prefix)) {
                return true;
            }
        }
        
        // Verificar se é um valor booleano
        return is_bool($value) || in_array($value, [0, 1, '0', '1', 'true', 'false'], true);
    }

    /**
     * Registra casts padrão
     */
    protected function registerDefaultCasts(): void
    {
        // Método será sobrescrito por HasColumns
        // Mantém estrutura base para outras implementações
    }
} 
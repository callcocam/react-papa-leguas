<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

use Closure;

class BadgeColumn extends Column
{
    protected array $variants = [];
    protected array $labels = [];
    protected string $defaultVariant = 'default';
    protected ?Closure $variantCallback = null;
    protected ?Closure $labelCallback = null;

    /**
     * Definir variantes de cores para valores específicos
     */
    public function variants(array $variants): static
    {
        $this->variants = $variants;
        return $this;
    }

    /**
     * Definir labels personalizados para valores específicos
     */
    public function labels(array $labels): static
    {
        $this->labels = $labels;
        return $this;
    }

    /**
     * Definir variante padrão
     */
    public function defaultVariant(string $variant): static
    {
        $this->defaultVariant = $variant;
        return $this;
    }

    /**
     * Definir callback para determinar variante dinamicamente
     */
    public function variantUsing(Closure $callback): static
    {
        $this->variantCallback = $callback;
        return $this;
    }

    /**
     * Definir callback para determinar label dinamicamente
     */
    public function labelUsing(Closure $callback): static
    {
        $this->labelCallback = $callback;
        return $this;
    }

    /**
     * Configuração rápida para status comuns
     */
    public function statusBadge(): static
    {
        return $this->variants([
            'active' => 'success',
            'inactive' => 'secondary',
            'published' => 'success',
            'draft' => 'warning',
            'archived' => 'secondary',
            'deleted' => 'destructive',
        ])->labels([
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'published' => 'Publicado',
            'draft' => 'Rascunho',
            'archived' => 'Arquivado',
            'deleted' => 'Excluído',
        ]);
    }

    /**
     * Configuração rápida para badges booleanos
     */
    public function booleanBadge(string $trueLabel = 'Sim', string $falseLabel = 'Não'): static
    {
        return $this->variants([
            true => 'success',
            1 => 'success',
            '1' => 'success',
            false => 'secondary',
            0 => 'secondary',
            '0' => 'secondary',
        ])->labels([
            true => $trueLabel,
            1 => $trueLabel,
            '1' => $trueLabel,
            false => $falseLabel,
            0 => $falseLabel,
            '0' => $falseLabel,
        ]);
    }

    /**
     * Formatar valor como badge
     */
    protected function format(mixed $value, $row): mixed
    {
        // Se o valor já foi processado por um cast e é um array, usar ele
        if (is_array($value) && isset($value['type']) && $value['type'] === 'badge') {
            // Valor já processado por cast - apenas retornar
            return $value;
        }
        
        // Se é um array mas não é um badge, extrair o valor original
        $originalValue = is_array($value) && isset($value['value']) ? $value['value'] : $value;
        
        // Determinar variante
        $variant = $this->defaultVariant;
        if ($this->variantCallback) {
            $variant = $this->evaluate($this->variantCallback, [
                'value' => $originalValue,
                'row' => $row,
                'column' => $this
            ]);
        } elseif (!is_array($originalValue) && isset($this->variants[$originalValue])) {
            $variant = $this->variants[$originalValue];
        }

        // Determinar label
        $label = $originalValue;
        if ($this->labelCallback) {
            $label = $this->evaluate($this->labelCallback, [
                'value' => $originalValue,
                'row' => $row,
                'column' => $this
            ]);
        } elseif (!is_array($originalValue) && isset($this->labels[$originalValue])) {
            $label = $this->labels[$originalValue];
        }

        return [
            'value' => $originalValue,
            'type' => 'badge',
            'variant' => $variant,
            'label' => $label,
        ];
    }

    /**
     * Obter tipo da coluna
     */
    public function getType(): string
    {
        return 'badge';
    }

    /**
     * Converter para array incluindo configurações específicas
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'variants' => $this->variants,
            'labels' => $this->labels,
            'defaultVariant' => $this->defaultVariant,
        ]);
    }
} 
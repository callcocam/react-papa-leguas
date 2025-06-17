<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

/**
 * Coluna booleana
 */
class BooleanColumn extends Column
{
    protected string $type = 'boolean';

    /**
     * Exibir como badge
     */
    public function asBadge(): static
    {
        $this->formatConfig['display'] = 'badge';
        return $this;
    }

    /**
     * Exibir como switch
     */
    public function asSwitch(): static
    {
        $this->formatConfig['display'] = 'switch';
        return $this;
    }

    /**
     * Exibir como ícone
     */
    public function asIcon(): static
    {
        $this->formatConfig['display'] = 'icon';
        return $this;
    }

    /**
     * Labels para verdadeiro/falso
     */
    public function activeInactive(): static
    {
        return $this->labels(['Ativo', 'Inativo']);
    }

    /**
     * Labels personalizados
     */
    public function labels(array $labels): static
    {
        $this->formatConfig['labels'] = $labels;
        return $this;
    }

    /**
     * Cores para verdadeiro/falso
     */
    public function colors(array $colors): static
    {
        $this->formatConfig['colors'] = $colors;
        return $this;
    }

    /**
     * Ícone para valor verdadeiro
     */
    public function trueIcon(string $icon): static
    {
        $this->formatConfig['trueIcon'] = $icon;
        return $this;
    }

    /**
     * Ícone para valor falso
     */
    public function falseIcon(string $icon): static
    {
        $this->formatConfig['falseIcon'] = $icon;
        return $this;
    }

    /**
     * Cor para valor verdadeiro
     */
    public function trueColor(string $color): static
    {
        $this->formatConfig['trueColor'] = $color;
        return $this;
    }

    /**
     * Cor para valor falso
     */
    public function falseColor(string $color): static
    {
        $this->formatConfig['falseColor'] = $color;
        return $this;
    }

    /**
     * Label para valor verdadeiro
     */
    public function trueLabel(string $label): static
    {
        $this->formatConfig['trueLabel'] = $label;
        return $this;
    }

    /**
     * Label para valor falso
     */
    public function falseLabel(string $label): static
    {
        $this->formatConfig['falseLabel'] = $label;
        return $this;
    }

    /**
     * Aplicar formatação padrão
     */
    protected function applyDefaultFormatting($value, $record): mixed
    {
        $boolValue = (bool) $value;
        
        // Labels padrão ou específicos
        $trueLabel = $this->formatConfig['trueLabel'] ?? ($this->formatConfig['labels'][0] ?? 'Sim');
        $falseLabel = $this->formatConfig['falseLabel'] ?? ($this->formatConfig['labels'][1] ?? 'Não');
        
        // Cores padrão ou específicas
        $trueColor = $this->formatConfig['trueColor'] ?? ($this->formatConfig['colors'][0] ?? 'green');
        $falseColor = $this->formatConfig['falseColor'] ?? ($this->formatConfig['colors'][1] ?? 'red');
        
        // Ícones se definidos
        $trueIcon = $this->formatConfig['trueIcon'] ?? null;
        $falseIcon = $this->formatConfig['falseIcon'] ?? null;

        return [
            'value' => $boolValue,
            'text' => $boolValue ? $trueLabel : $falseLabel,
            'color' => $boolValue ? $trueColor : $falseColor,
            'icon' => $boolValue ? $trueIcon : $falseIcon,
            'display' => $this->formatConfig['display'] ?? 'text',
        ];
    }
} 
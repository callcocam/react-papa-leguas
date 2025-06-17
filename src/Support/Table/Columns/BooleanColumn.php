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
     * Aplicar formatação padrão
     */
    protected function applyDefaultFormatting($value, $record): mixed
    {
        $boolValue = (bool) $value;
        $labels = $this->formatConfig['labels'] ?? ['Sim', 'Não'];
        $colors = $this->formatConfig['colors'] ?? ['green', 'red'];

        return [
            'value' => $boolValue,
            'text' => $boolValue ? $labels[0] : $labels[1],
            'color' => $boolValue ? $colors[0] : $colors[1],
            'display' => $this->formatConfig['display'] ?? 'text',
        ];
    }
} 
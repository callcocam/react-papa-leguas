<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

class BooleanColumn extends Column
{
    protected string $trueLabel = 'Sim';
    protected string $falseLabel = 'Não';
    protected string $trueIcon = 'check';
    protected string $falseIcon = 'x';
    protected string $trueColor = 'success';
    protected string $falseColor = 'secondary';
    protected string $displayAs = 'badge'; // badge, icon, text
    protected ?string $placeholder = null;

    /**
     * Definir labels para valores verdadeiro/falso
     */
    public function labels(string $trueLabel, string $falseLabel): static
    {
        $this->trueLabel = $trueLabel;
        $this->falseLabel = $falseLabel;
        return $this;
    }

    /**
     * Definir ícones para valores verdadeiro/falso
     */
    public function icons(string $trueIcon, string $falseIcon): static
    {
        $this->trueIcon = $trueIcon;
        $this->falseIcon = $falseIcon;
        return $this;
    }

    /**
     * Definir cores para valores verdadeiro/falso
     */
    public function colors(string $trueColor, string $falseColor): static
    {
        $this->trueColor = $trueColor;
        $this->falseColor = $falseColor;
        return $this;
    }

    /**
     * Exibir como badge
     */
    public function asBadge(): static
    {
        $this->displayAs = 'badge';
        return $this;
    }

    /**
     * Exibir como ícone
     */
    public function asIcon(): static
    {
        $this->displayAs = 'icon';
        return $this;
    }

    /**
     * Exibir como texto
     */
    public function asText(): static
    {
        $this->displayAs = 'text';
        return $this;
    }

    /**
     * Configuração para Ativo/Inativo
     */
    public function activeInactive(): static
    {
        return $this->labels('Ativo', 'Inativo')
                   ->colors('success', 'secondary')
                   ->icons('check-circle', 'x-circle');
    }

    /**
     * Configuração para Sim/Não
     */
    public function yesNo(): static
    {
        return $this->labels('Sim', 'Não')
                   ->colors('success', 'secondary')
                   ->icons('check', 'x');
    }

    /**
     * Configuração para Habilitado/Desabilitado
     */
    public function enabledDisabled(): static
    {
        return $this->labels('Habilitado', 'Desabilitado')
                   ->colors('success', 'secondary')
                   ->icons('toggle-right', 'toggle-left');
    }

    /**
     * Definir placeholder para valores nulos
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Formatar valor booleano
     */
    protected function format(mixed $value, $row): mixed
    {
        if (is_null($value)) {
            return $this->placeholder ?? '';
        }

        $boolValue = $this->toBool($value);
        $label = $boolValue ? $this->trueLabel : $this->falseLabel;
        $icon = $boolValue ? $this->trueIcon : $this->falseIcon;
        $color = $boolValue ? $this->trueColor : $this->falseColor;

        $result = [
            'value' => $value,
            'type' => 'boolean',
            'boolean' => $boolValue,
            'label' => $label,
            'icon' => $icon,
            'color' => $color,
            'displayAs' => $this->displayAs,
        ];

        // Adicionar formatação específica baseada no tipo de exibição
        switch ($this->displayAs) {
            case 'badge':
                $result['variant'] = $color;
                break;
            case 'icon':
                $result['iconColor'] = $color;
                break;
            case 'text':
                $result['textColor'] = $color;
                break;
        }

        return $result;
    }

    /**
     * Converter valor para booleano
     */
    protected function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (bool) $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on', 'sim']);
        }

        return (bool) $value;
    }

    /**
     * Obter tipo da coluna
     */
    public function getType(): string
    {
        return 'boolean';
    }

    /**
     * Converter para array incluindo configurações específicas
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'trueLabel' => $this->trueLabel,
            'falseLabel' => $this->falseLabel,
            'trueIcon' => $this->trueIcon,
            'falseIcon' => $this->falseIcon,
            'trueColor' => $this->trueColor,
            'falseColor' => $this->falseColor,
            'displayAs' => $this->displayAs,
            'placeholder' => $this->placeholder,
        ]);
    }
} 
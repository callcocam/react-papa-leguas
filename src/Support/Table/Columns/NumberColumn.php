<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

/**
 * Coluna numérica
 */
class NumberColumn extends Column
{
    protected string $type = 'number';

    /**
     * Casas decimais
     */
    public function decimals(int $decimals): static
    {
        $this->formatConfig['decimals'] = $decimals;
        return $this;
    }

    /**
     * Separador decimal
     */
    public function decimalSeparator(string $separator): static
    {
        $this->formatConfig['decimalSeparator'] = $separator;
        return $this;
    }

    /**
     * Separador de milhares
     */
    public function thousandsSeparator(string $separator): static
    {
        $this->formatConfig['thousandsSeparator'] = $separator;
        return $this;
    }

    /**
     * Prefixo
     */
    public function prefix(string $prefix): static
    {
        $this->formatConfig['prefix'] = $prefix;
        return $this;
    }

    /**
     * Sufixo
     */
    public function suffix(string $suffix): static
    {
        $this->formatConfig['suffix'] = $suffix;
        return $this;
    }

    /**
     * Formatação brasileira
     */
    public function brazilian(): static
    {
        return $this->decimals(2)
                   ->decimalSeparator(',')
                   ->thousandsSeparator('.');
    }

    /**
     * Aplicar formatação padrão
     */
    protected function applyDefaultFormatting($value, $record): mixed
    {
        if (is_null($value) || !is_numeric($value)) {
            return [
                'value' => $value,
                'formatted' => $value,
                'raw' => $value,
            ];
        }

        $decimals = $this->formatConfig['decimals'] ?? 0;
        $decimalSeparator = $this->formatConfig['decimalSeparator'] ?? '.';
        $thousandsSeparator = $this->formatConfig['thousandsSeparator'] ?? ',';
        $prefix = $this->formatConfig['prefix'] ?? '';
        $suffix = $this->formatConfig['suffix'] ?? '';

        $formatted = number_format($value, $decimals, $decimalSeparator, $thousandsSeparator);
        $formatted = $prefix . $formatted . $suffix;

        return [
            'value' => $formatted,
            'raw' => $value,
            'formatted' => $formatted,
        ];
    }
} 
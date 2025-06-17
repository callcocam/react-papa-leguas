<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

/**
 * Coluna de moeda
 */
class CurrencyColumn extends Column
{
    protected string $type = 'currency';

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        
        // Configuração padrão para Real brasileiro
        $this->formatConfig = [
            'currency' => 'BRL',
            'symbol' => 'R$',
            'decimals' => 2,
            'decimalSeparator' => ',',
            'thousandsSeparator' => '.',
            'symbolPosition' => 'before',
        ];
    }

    /**
     * Definir moeda
     */
    public function currency(string $currency, string $symbol = null): static
    {
        $this->formatConfig['currency'] = $currency;
        if ($symbol) {
            $this->formatConfig['symbol'] = $symbol;
        }
        return $this;
    }

    /**
     * Real brasileiro
     */
    public function brl(): static
    {
        return $this->currency('BRL', 'R$')
                   ->decimals(2)
                   ->decimalSeparator(',')
                   ->thousandsSeparator('.');
    }

    /**
     * Dólar americano
     */
    public function usd(): static
    {
        return $this->currency('USD', '$')
                   ->decimals(2)
                   ->decimalSeparator('.')
                   ->thousandsSeparator(',');
    }

    /**
     * Euro
     */
    public function eur(): static
    {
        return $this->currency('EUR', '€')
                   ->decimals(2)
                   ->decimalSeparator(',')
                   ->thousandsSeparator('.');
    }

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
     * Posição do símbolo
     */
    public function symbolPosition(string $position): static
    {
        $this->formatConfig['symbolPosition'] = $position;
        return $this;
    }

    /**
     * Símbolo antes do valor
     */
    public function symbolBefore(): static
    {
        return $this->symbolPosition('before');
    }

    /**
     * Símbolo depois do valor
     */
    public function symbolAfter(): static
    {
        return $this->symbolPosition('after');
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
                'currency' => $this->formatConfig['currency'],
            ];
        }

        $decimals = $this->formatConfig['decimals'];
        $decimalSeparator = $this->formatConfig['decimalSeparator'];
        $thousandsSeparator = $this->formatConfig['thousandsSeparator'];
        $symbol = $this->formatConfig['symbol'];
        $symbolPosition = $this->formatConfig['symbolPosition'];

        $formatted = number_format($value, $decimals, $decimalSeparator, $thousandsSeparator);
        
        if ($symbolPosition === 'before') {
            $formatted = $symbol . ' ' . $formatted;
        } else {
            $formatted = $formatted . ' ' . $symbol;
        }

        return [
            'value' => $formatted,
            'raw' => $value,
            'formatted' => $formatted,
            'currency' => $this->formatConfig['currency'],
            'symbol' => $symbol,
        ];
    }
} 
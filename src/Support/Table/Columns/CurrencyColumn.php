<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

class CurrencyColumn extends Column
{
    protected string $currency = 'BRL';
    protected string $locale = 'pt_BR';
    protected int $decimals = 2;
    protected string $decimalSeparator = ',';
    protected string $thousandsSeparator = '.';
    protected string $symbol = 'R$';
    protected bool $symbolBefore = true;
    protected ?string $placeholder = null;

    /**
     * Definir moeda
     */
    public function setCurrency(string $currency): static
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * Definir locale para formatação
     */
    public function locale(string $locale): static
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Definir número de casas decimais
     */
    public function decimals(int $decimals): static
    {
        $this->decimals = $decimals;
        return $this;
    }

    /**
     * Definir separadores
     */
    public function separators(string $decimal = ',', string $thousands = '.'): static
    {
        $this->decimalSeparator = $decimal;
        $this->thousandsSeparator = $thousands;
        return $this;
    }

    /**
     * Definir símbolo da moeda
     */
    public function symbol(string $symbol, bool $before = true): static
    {
        $this->symbol = $symbol;
        $this->symbolBefore = $before;
        return $this;
    }

    /**
     * Configuração para Real Brasileiro
     */
    public function brl(): static
    {
        return $this->setCurrency('BRL')
                   ->locale('pt_BR')
                   ->symbol('R$', true)
                   ->separators(',', '.');
    }

    /**
     * Configuração para Dólar Americano
     */
    public function usd(): static
    {
        return $this->setCurrency('USD')
                   ->locale('en_US')
                   ->symbol('$', true)
                   ->separators('.', ',');
    }

    /**
     * Configuração para Euro
     */
    public function eur(): static
    {
        return $this->setCurrency('EUR')
                   ->locale('en_EU')
                   ->symbol('€', true)
                   ->separators(',', '.');
    }

    /**
     * Definir placeholder para valores vazios
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Formatar valor como moeda
     */
    protected function format(mixed $value, $row): mixed
    {
        if (is_null($value) || $value === '') {
            return $this->placeholder ?? '';
        }

        $numericValue = (float) $value;
        
        // Formatar número com separadores
        $formatted = number_format(
            $numericValue,
            $this->decimals,
            $this->decimalSeparator,
            $this->thousandsSeparator
        );

        // Adicionar símbolo da moeda
        $displayValue = $this->symbolBefore 
            ? $this->symbol . ' ' . $formatted
            : $formatted . ' ' . $this->symbol;

        return [
            'value' => $numericValue,
            'type' => 'currency',
            'formatted' => $displayValue,
            'currency' => $this->currency,
            'symbol' => $this->symbol,
            'locale' => $this->locale,
        ];
    }

    /**
     * Obter tipo da coluna
     */
    public function getType(): string
    {
        return 'currency';
    }

    /**
     * Converter para array incluindo configurações específicas
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'currency' => $this->currency,
            'locale' => $this->locale,
            'decimals' => $this->decimals,
            'decimalSeparator' => $this->decimalSeparator,
            'thousandsSeparator' => $this->thousandsSeparator,
            'symbol' => $this->symbol,
            'symbolBefore' => $this->symbolBefore,
            'placeholder' => $this->placeholder,
        ]);
    }
} 
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Casts;

use NumberFormatter;

class CurrencyCast extends Cast
{
    /**
     * Prioridade alta para valores monetários
     */
    protected int $priority = 80;

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'currency';
    }

    /**
     * Configurações padrão do cast
     */
    protected function getDefaultConfig(): array
    {
        return array_merge(parent::getDefaultConfig(), [
            'currency' => 'BRL',
            'locale' => 'pt_BR',
            'decimals' => 2,
            'symbol' => 'R$',
            'symbol_position' => 'before', // before, after
            'thousands_separator' => '.',
            'decimal_separator' => ',',
            'show_zero' => true,
            'show_symbol' => true,
            'format_type' => 'currency', // currency, decimal, custom
        ]);
    }

    /**
     * Configuração rápida para Real Brasileiro
     */
    public static function brl(array $config = []): static
    {
        return static::make(array_merge([
            'currency' => 'BRL',
            'locale' => 'pt_BR',
            'symbol' => 'R$',
            'thousands_separator' => '.',
            'decimal_separator' => ',',
        ], $config));
    }

    /**
     * Configuração rápida para Dólar Americano
     */
    public static function usd(array $config = []): static
    {
        return static::make(array_merge([
            'currency' => 'USD',
            'locale' => 'en_US',
            'symbol' => '$',
            'thousands_separator' => ',',
            'decimal_separator' => '.',
        ], $config));
    }

    /**
     * Configuração rápida para Euro
     */
    public static function eur(array $config = []): static
    {
        return static::make(array_merge([
            'currency' => 'EUR',
            'locale' => 'de_DE',
            'symbol' => '€',
            'thousands_separator' => '.',
            'decimal_separator' => ',',
        ], $config));
    }

    /**
     * Define a moeda
     */
    public function currency(string $currency): static
    {
        return $this->config('currency', $currency);
    }

    /**
     * Define o locale
     */
    public function locale(string $locale): static
    {
        return $this->config('locale', $locale);
    }

    /**
     * Define o número de casas decimais
     */
    public function decimals(int $decimals): static
    {
        return $this->config('decimals', $decimals);
    }

    /**
     * Define o símbolo da moeda
     */
    public function symbol(string $symbol): static
    {
        return $this->config('symbol', $symbol);
    }

    /**
     * Define os separadores
     */
    public function separators(string $decimal, string $thousands): static
    {
        return $this->config('decimal_separator', $decimal)
                   ->config('thousands_separator', $thousands);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCast(mixed $value, array $context): mixed
    {
        // Converter para número se necessário
        $numericValue = $this->toNumeric($value);
        
        if ($numericValue === null) {
            return $value;
        }

        // Verificar se deve mostrar zero
        if ($numericValue == 0 && !$this->getConfig('show_zero')) {
            return '-';
        }

        // Aplicar formatação baseada no tipo
        $formatType = $this->getConfig('format_type');
        
        return match ($formatType) {
            'currency' => $this->formatAsCurrency($numericValue),
            'decimal' => $this->formatAsDecimal($numericValue),
            'custom' => $this->formatAsCustom($numericValue),
            default => $this->formatAsCurrency($numericValue),
        };
    }

    /**
     * Formata como moeda usando NumberFormatter
     */
    protected function formatAsCurrency(float $value): array
    {
        $locale = $this->getConfig('locale');
        $currency = $this->getConfig('currency');
        
        try {
            $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
            $formatted = $formatter->formatCurrency($value, $currency);
            
            return [
                'value' => $value,
                'formatted' => $formatted,
                'currency' => $currency,
                'locale' => $locale,
                'symbol' => $this->getConfig('symbol'),
                'type' => 'currency',
            ];
        } catch (\Exception $e) {
            // Fallback para formatação manual
            return $this->formatAsCustom($value);
        }
    }

    /**
     * Formata como decimal
     */
    protected function formatAsDecimal(float $value): array
    {
        $decimals = $this->getConfig('decimals');
        $decimalSep = $this->getConfig('decimal_separator');
        $thousandsSep = $this->getConfig('thousands_separator');
        
        $formatted = number_format($value, $decimals, $decimalSep, $thousandsSep);
        
        return [
            'value' => $value,
            'formatted' => $formatted,
            'decimals' => $decimals,
            'type' => 'decimal',
        ];
    }

    /**
     * Formata com configuração customizada
     */
    protected function formatAsCustom(float $value): array
    {
        $decimals = $this->getConfig('decimals');
        $decimalSep = $this->getConfig('decimal_separator');
        $thousandsSep = $this->getConfig('thousands_separator');
        $symbol = $this->getConfig('symbol');
        $symbolPosition = $this->getConfig('symbol_position');
        $showSymbol = $this->getConfig('show_symbol');
        
        // Formatar número
        $formatted = number_format($value, $decimals, $decimalSep, $thousandsSep);
        
        // Adicionar símbolo se configurado
        if ($showSymbol && $symbol) {
            $formatted = $symbolPosition === 'before' 
                ? $symbol . ' ' . $formatted 
                : $formatted . ' ' . $symbol;
        }
        
        return [
            'value' => $value,
            'formatted' => $formatted,
            'currency' => $this->getConfig('currency'),
            'symbol' => $symbol,
            'decimals' => $decimals,
            'type' => 'currency',
        ];
    }

    /**
     * Converte valor para numérico
     */
    protected function toNumeric(mixed $value): ?float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }
        
        if (is_string($value)) {
            // Remover símbolos de moeda e espaços
            $cleaned = preg_replace('/[^\d,.-]/', '', $value);
            
            // Converter separadores
            $cleaned = str_replace(',', '.', $cleaned);
            
            if (is_numeric($cleaned)) {
                return (float) $cleaned;
            }
        }
        
        return null;
    }

    /**
     * Verifica se o cast pode ser aplicado
     *
     * CONDIÇÕES PARA APLICAÇÃO:
     * 1. A coluna deve ser do tipo 'currency'.
     * 2. O valor deve ser numérico.
     */
    protected function checkCanCast(mixed $value, ?string $type = null): bool
    {
        // 🎯 REGRA 1: Só aplicar se a coluna for do tipo 'currency'
        if ($type !== 'currency') {
            return false;
        }
        
        // REGRA 2: Só aplicar se o valor for numérico
        if (!is_numeric($this->toNumeric($value))) {
            return false;
        }
        
                    return true;
    }
} 
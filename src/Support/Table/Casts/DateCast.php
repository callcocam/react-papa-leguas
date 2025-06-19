<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Casts;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class DateCast extends Cast
{
    /**
     * Prioridade alta para valores de data
     */
    protected int $priority = 85;

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'date';
    }

    /**
     * Configurações padrão do cast
     */
    protected function getDefaultConfig(): array
    {
        return array_merge(parent::getDefaultConfig(), [
            'format' => 'd/m/Y H:i',
            'input_format' => null,
            'timezone' => 'America/Sao_Paulo',
            'locale' => 'pt_BR',
            'show_relative' => false,
            'relative_threshold' => 7, // dias
            'show_time' => true,
            'null_display' => '-',
            'invalid_display' => 'Data inválida',
        ]);
    }

    /**
     * Configuração rápida para formato brasileiro
     */
    public static function brazilian(array $config = []): static
    {
        return static::make(array_merge([
            'format' => 'd/m/Y H:i',
            'timezone' => 'America/Sao_Paulo',
            'locale' => 'pt_BR',
        ], $config));
    }

    /**
     * Configuração rápida para formato americano
     */
    public static function american(array $config = []): static
    {
        return static::make(array_merge([
            'format' => 'm/d/Y H:i',
            'timezone' => 'America/New_York',
            'locale' => 'en_US',
        ], $config));
    }

    /**
     * Configuração rápida para ISO
     */
    public static function iso(array $config = []): static
    {
        return static::make(array_merge([
            'format' => 'Y-m-d H:i:s',
            'timezone' => 'UTC',
            'locale' => 'en',
        ], $config));
    }

    /**
     * Configuração rápida apenas data
     */
    public static function dateOnly(array $config = []): static
    {
        return static::make(array_merge([
            'format' => 'd/m/Y',
            'show_time' => false,
        ], $config));
    }

    /**
     * Configuração rápida com tempo relativo
     */
    public static function relative(array $config = []): static
    {
        return static::make(array_merge([
            'show_relative' => true,
            'relative_threshold' => 30,
        ], $config));
    }

    /**
     * Define o formato de saída
     */
    public function format(string $format): static
    {
        return $this->config('format', $format);
    }

    /**
     * Define o formato de entrada
     */
    public function inputFormat(string $format): static
    {
        return $this->config('input_format', $format);
    }

    /**
     * Define o timezone
     */
    public function timezone(string $timezone): static
    {
        return $this->config('timezone', $timezone);
    }

    /**
     * Define o locale
     */
    public function locale(string $locale): static
    {
        return $this->config('locale', $locale);
    }

    /**
     * Habilita tempo relativo
     */
    public function showRelative(bool $show = true, int $threshold = 7): static
    {
        return $this->config('show_relative', $show)
                   ->config('relative_threshold', $threshold);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCast(mixed $value, array $context): mixed
    {
        if ($value === null) {
            return $this->getConfig('null_display');
        }

        try {
            $carbon = $this->toCarbon($value);
            
            if (!$carbon) {
                return $this->getConfig('invalid_display');
            }

            // Aplicar timezone se configurado
            $timezone = $this->getConfig('timezone');
            if ($timezone) {
                $carbon = $carbon->setTimezone($timezone);
            }

            // Aplicar locale se configurado
            $locale = $this->getConfig('locale');
            if ($locale) {
                $carbon = $carbon->locale($locale);
            }

            // Preparar resultado
            $result = [
                'value' => $value,
                'carbon' => $carbon,
                'formatted' => $carbon->format($this->getConfig('format')),
                'iso' => $carbon->toISOString(),
                'timestamp' => $carbon->timestamp,
                'timezone' => $carbon->timezoneName,
                'type' => 'date',
            ];

            // Adicionar tempo relativo se configurado
            if ($this->getConfig('show_relative')) {
                $result['relative'] = $this->getRelativeTime($carbon);
                $result['show_relative'] = $this->shouldShowRelative($carbon);
            }

            // Adicionar formatações adicionais
            $result['human'] = $carbon->diffForHumans();
            $result['calendar'] = $carbon->calendar();

            return $result;

        } catch (\Exception $e) {
            return $this->getConfig('invalid_display');
        }
    }

    /**
     * Converte valor para Carbon
     */
    protected function toCarbon(mixed $value): ?Carbon
    {
        if ($value instanceof CarbonInterface) {
            return Carbon::instance($value);
        }

        if ($value instanceof \DateTime) {
            return Carbon::instance($value);
        }

        if (is_string($value)) {
            // Tentar com formato específico se configurado
            $inputFormat = $this->getConfig('input_format');
            if ($inputFormat) {
                try {
                    return Carbon::createFromFormat($inputFormat, $value);
                } catch (\Exception $e) {
                    // Continuar para tentar parse automático
                }
            }

            // Tentar parse automático
            try {
                return Carbon::parse($value);
            } catch (\Exception $e) {
                return null;
            }
        }

        if (is_numeric($value)) {
            try {
                return Carbon::createFromTimestamp($value);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Obtém tempo relativo formatado
     */
    protected function getRelativeTime(Carbon $carbon): string
    {
        $now = Carbon::now($carbon->timezone);
        $diffInDays = abs($carbon->diffInDays($now));

        // Diferentes formatos baseados na diferença
        if ($diffInDays === 0) {
            return 'Hoje';
        }

        if ($diffInDays === 1) {
            return $carbon->isPast() ? 'Ontem' : 'Amanhã';
        }

        if ($diffInDays <= 7) {
            return $carbon->diffForHumans($now, [
                'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW,
                'short' => false,
            ]);
        }

        if ($diffInDays <= 30) {
            return $carbon->diffForHumans($now, [
                'syntax' => CarbonInterface::DIFF_RELATIVE_TO_NOW,
                'short' => true,
            ]);
        }

        // Para datas mais antigas, usar formato normal
        return $carbon->format($this->getConfig('format'));
    }

    /**
     * Verifica se deve mostrar tempo relativo
     */
    protected function shouldShowRelative(Carbon $carbon): bool
    {
        $threshold = $this->getConfig('relative_threshold');
        $now = Carbon::now($carbon->timezone);
        
        return abs($carbon->diffInDays($now)) <= $threshold;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkCanCast(mixed $value, ?string $type = null): bool
    {
        // Verificar tipo explícito
        if ($type && $type !== 'date') {
            return false;
        }

        // Verificar instâncias de data
        if ($value instanceof CarbonInterface || $value instanceof \DateTime) {
            return true;
        }

        // Verificar strings de data
        if (is_string($value)) {
            // Verificar formato específico se configurado
            $inputFormat = $this->getConfig('input_format');
            if ($inputFormat) {
                try {
                    Carbon::createFromFormat($inputFormat, $value);
                    return true;
                } catch (\Exception $e) {
                    // Continuar para outras verificações
                }
            }

            // Verificar se é uma data válida
            if (strtotime($value) !== false) {
                return true;
            }

            // Verificar padrões comuns de data
            $datePatterns = [
                '/^\d{4}-\d{2}-\d{2}/', // YYYY-MM-DD
                '/^\d{2}\/\d{2}\/\d{4}/', // DD/MM/YYYY
                '/^\d{2}-\d{2}-\d{4}/', // DD-MM-YYYY
                '/^\d{4}\/\d{2}\/\d{2}/', // YYYY/MM/DD
            ];

            foreach ($datePatterns as $pattern) {
                if (preg_match($pattern, $value)) {
                    return true;
                }
            }
        }

        // Verificar timestamps
        if (is_numeric($value)) {
            $timestamp = (int) $value;
            // Verificar se é um timestamp válido (entre 1970 e 2100)
            return $timestamp > 0 && $timestamp < 4102444800;
        }

        return false;
    }
} 
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

use Carbon\Carbon;

class DateColumn extends Column
{
    protected string $format = 'd/m/Y';
    protected ?string $timezone = null;
    protected bool $since = false;
    protected ?string $placeholder = null;

    /**
     * Definir formato da data
     */
    public function dateFormat(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    /**
     * Definir timezone
     */
    public function timezone(string $timezone): static
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * Mostrar tempo relativo (há X dias)
     */
    public function since(bool $since = true): static
    {
        $this->since = $since;
        return $this;
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
     * Formatar valor como data
     */
    protected function format(mixed $value, $row): mixed
    {
        // Se o valor já foi processado por um cast e é um array, usar ele
        if (is_array($value) && isset($value['type']) && $value['type'] === 'date') {
            // Valor já processado por cast - apenas retornar
            return $value;
        }
        
        // Se é um array mas não é uma data, extrair o valor original
        $originalValue = is_array($value) && isset($value['value']) ? $value['value'] : $value;
        
        if (is_null($originalValue)) {
            return [
                'value' => null,
                'type' => 'date',
                'formatted' => $this->placeholder ?? '',
                'since' => null,
                'timestamp' => null,
                'iso' => null,
            ];
        }

        try {
            $date = Carbon::parse($originalValue);
            
            if ($this->timezone) {
                $date = $date->setTimezone($this->timezone);
            }

            $formatted = $date->format($this->format);
            $since = $this->since ? $date->diffForHumans() : null;

            return [
                'value' => $originalValue,
                'type' => 'date',
                'formatted' => $formatted,
                'since' => $since,
                'timestamp' => $date->timestamp,
                'iso' => $date->toISOString(),
            ];
        } catch (\Exception $e) {
            return [
                'value' => $originalValue,
                'type' => 'date',
                'formatted' => $this->placeholder ?? (string) $originalValue,
                'since' => null,
                'timestamp' => null,
                'iso' => null,
            ];
        }
    }

    /**
     * Obter tipo da coluna
     */
    public function getType(): string
    {
        return 'date';
    }

    /**
     * Converter para array incluindo configurações específicas
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'format' => $this->format,
            'timezone' => $this->timezone,
            'since' => $this->since,
            'placeholder' => $this->placeholder,
        ]);
    }
} 
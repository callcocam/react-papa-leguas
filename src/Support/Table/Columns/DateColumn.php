<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

use Carbon\Carbon;

/**
 * Coluna de data com formatação avançada
 */
class DateColumn extends Column
{
    protected string $type = 'date';

    /**
     * Formato de exibição da data
     */
    public function dateFormat(string $format): static
    {
        $this->formatConfig['format'] = $format;
        return $this;
    }

    /**
     * Mostrar apenas a data (sem hora)
     */
    public function dateOnly(): static
    {
        return $this->dateFormat('d/m/Y');
    }

    /**
     * Mostrar data e hora
     */
    public function dateTime(): static
    {
        return $this->dateFormat('d/m/Y H:i');
    }

    /**
     * Mostrar apenas a hora
     */
    public function timeOnly(): static
    {
        return $this->dateFormat('H:i');
    }

    /**
     * Mostrar data relativa (ex: "há 2 dias")
     */
    public function relative(bool $relative = true): static
    {
        $this->formatConfig['relative'] = $relative;
        return $this;
    }

    /**
     * Mostrar nome do dia da semana
     */
    public function dayName(bool $dayName = true): static
    {
        $this->formatConfig['dayName'] = $dayName;
        return $this;
    }

    /**
     * Mostrar tempo relativo (ex: "há 2 dias")
     */
    public function since(bool $since = true): static
    {
        $this->formatConfig['since'] = $since;
        return $this;
    }

    /**
     * Definir placeholder para data vazia
     */
    public function placeholder(string $placeholder): static
    {
        $this->formatConfig['placeholder'] = $placeholder;
        return $this;
    }

    /**
     * Aplicar formatação padrão
     */
    protected function applyDefaultFormatting($value, $record): mixed
    {
        if (is_null($value)) {
            return [
                'value' => null,
                'formatted' => $this->formatConfig['placeholder'] ?? '',
                'placeholder' => true,
            ];
        }

        try {
            $date = $value instanceof Carbon ? $value : Carbon::parse($value);
            
            $formatted = [
                'value' => $date->toISOString(),
                'formatted' => $date->format($this->formatConfig['format'] ?? 'd/m/Y H:i'),
                'timestamp' => $date->timestamp,
                'is_today' => $date->isToday(),
                'is_past' => $date->isPast(),
                'is_future' => $date->isFuture(),
            ];

            // Adicionar data relativa se solicitado
            if ($this->formatConfig['relative'] ?? false) {
                $formatted['relative'] = $date->diffForHumans();
            }

            // Adicionar since se solicitado
            if ($this->formatConfig['since'] ?? false) {
                $formatted['since'] = $date->diffForHumans();
            }

            // Adicionar nome do dia se solicitado
            if ($this->formatConfig['dayName'] ?? false) {
                $formatted['day_name'] = $date->dayName;
            }

            return $formatted;
            
        } catch (\Exception $e) {
            return [
                'value' => $value,
                'formatted' => 'Data inválida',
                'error' => true,
            ];
        }
    }
} 
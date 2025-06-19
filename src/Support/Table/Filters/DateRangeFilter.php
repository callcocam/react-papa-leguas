<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

use Carbon\Carbon;

/**
 * Filtro de range de datas
 */
class DateRangeFilter extends Filter
{
    protected ?string $startDate = null;
    protected ?string $endDate = null;
    protected string $format = 'Y-m-d';
    protected ?string $timezone = null;
    protected bool $includeTime = false;

    /**
     * Definir formato da data
     */
    public function format(string $format): static
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
     * Incluir tempo na comparação
     */
    public function includeTime(bool $include = true): static
    {
        $this->includeTime = $include;
        return $this;
    }

    /**
     * Configuração para data brasileira
     */
    public function brazilian(): static
    {
        return $this->format('d/m/Y')
                   ->timezone('America/Sao_Paulo');
    }

    /**
     * Configuração para apenas data (sem tempo)
     */
    public function dateOnly(): static
    {
        return $this->includeTime(false)
                   ->format('Y-m-d');
    }

    /**
     * Configuração para data e hora
     */
    public function dateTime(): static
    {
        return $this->includeTime(true)
                   ->format('Y-m-d H:i:s');
    }

    /**
     * Definir valor do filtro (array com start e end)
     */
    public function setValue(mixed $value): static
    {
        if (is_array($value)) {
            $this->startDate = $value['start'] ?? $value[0] ?? null;
            $this->endDate = $value['end'] ?? $value[1] ?? null;
        } elseif (is_string($value) && str_contains($value, ' - ')) {
            // Formato "2023-01-01 - 2023-12-31"
            [$start, $end] = explode(' - ', $value, 2);
            $this->startDate = trim($start);
            $this->endDate = trim($end);
        }

        return parent::setValue($value);
    }

    /**
     * Obter data de início
     */
    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    /**
     * Obter data de fim
     */
    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    /**
     * Verificar se filtro tem valor válido
     */
    public function hasValue(): bool
    {
        return !empty($this->startDate) || !empty($this->endDate);
    }

    /**
     * {@inheritdoc}
     */
    protected function apply($query, mixed $value): void
    {
        $startDate = $this->startDate;
        $endDate = $this->endDate;

        if (empty($startDate) && empty($endDate)) {
            return;
        }

        // Verificar se é busca em relacionamento
        if (str_contains($this->key, '.')) {
            $this->applyRelationshipFilter($query, $startDate, $endDate);
            return;
        }

        // Aplicar filtro de data
        $this->applyDateFilter($query, $this->key, $startDate, $endDate);
    }

    /**
     * Aplicar filtro de data em uma coluna
     */
    protected function applyDateFilter($query, string $column, ?string $startDate, ?string $endDate): void
    {
        if (!empty($startDate)) {
            $start = $this->parseDate($startDate, true);
            if ($start) {
                $query->where($column, '>=', $start);
            }
        }

        if (!empty($endDate)) {
            $end = $this->parseDate($endDate, false);
            if ($end) {
                $query->where($column, '<=', $end);
            }
        }
    }

    /**
     * Aplicar filtro em relacionamento
     */
    protected function applyRelationshipFilter($query, ?string $startDate, ?string $endDate): void
    {
        [$relation, $relationColumn] = explode('.', $this->key, 2);

        $query->whereHas($relation, function ($q) use ($relationColumn, $startDate, $endDate) {
            $this->applyDateFilter($q, $relationColumn, $startDate, $endDate);
        });
    }

    /**
     * Fazer parse da data
     */
    protected function parseDate(string $date, bool $isStart = true): ?string
    {
        try {
            $carbon = Carbon::createFromFormat($this->format, $date);

            if ($this->timezone) {
                $carbon->setTimezone($this->timezone);
            }

            // Se não incluir tempo, ajustar para início/fim do dia
            if (!$this->includeTime) {
                if ($isStart) {
                    $carbon->startOfDay();
                } else {
                    $carbon->endOfDay();
                }
            }

            return $carbon->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // Tentar parse automático
            try {
                $carbon = Carbon::parse($date);
                
                if ($this->timezone) {
                    $carbon->setTimezone($this->timezone);
                }

                if (!$this->includeTime) {
                    if ($isStart) {
                        $carbon->startOfDay();
                    } else {
                        $carbon->endOfDay();
                    }
                }

                return $carbon->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'date_range';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'format' => $this->format,
            'timezone' => $this->timezone,
            'include_time' => $this->includeTime,
        ]);
    }
} 
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;

/**
 * Filtro de data para React Frontend
 */
class DateFilter extends Filter
{
    /**
     * Tipo do filtro
     */
    protected string $type = 'date';

    /**
     * Se é um filtro de range
     */
    protected bool $isRange = false;

    /**
     * Formato da data
     */
    protected string $format = 'Y-m-d';

    /**
     * Formato de exibição
     */
    protected string $displayFormat = 'd/m/Y';

    /**
     * Operador de comparação
     */
    protected string $operator = '=';

    /**
     * Configurar como range
     */
    public function range(bool $isRange = true): static
    {
        $this->isRange = $isRange;
        $this->type = $isRange ? 'daterange' : 'date';
        $this->reactConfig['range'] = $isRange;
        return $this;
    }

    /**
     * Definir formato da data
     */
    public function format(string $format, string $displayFormat = null): static
    {
        $this->format = $format;
        if ($displayFormat) {
            $this->displayFormat = $displayFormat;
        }
        $this->reactConfig['format'] = $format;
        $this->reactConfig['displayFormat'] = $this->displayFormat;
        return $this;
    }

    /**
     * Definir operador
     */
    public function operator(string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * Filtro de data específica
     */
    public function exact(): static
    {
        $this->operator = '=';
        return $this;
    }

    /**
     * Filtro de data posterior
     */
    public function after(): static
    {
        $this->operator = '>';
        $this->placeholder('Após esta data...');
        return $this;
    }

    /**
     * Filtro de data anterior
     */
    public function before(): static
    {
        $this->operator = '<';
        $this->placeholder('Antes desta data...');
        return $this;
    }

    /**
     * Filtro de hoje
     */
    public static function today(): static
    {
        return static::make('date_today')
            ->label('Hoje')
            ->default(Carbon::today()->format('Y-m-d'))
            ->exact()
            ->reactConfig([
                'component' => 'TodayFilter',
                'preset' => 'today',
            ]);
    }

    /**
     * Filtro desta semana
     */
    public static function thisWeek(): static
    {
        return static::make('date_this_week')
            ->label('Esta Semana')
            ->range()
            ->default([
                Carbon::now()->startOfWeek()->format('Y-m-d'),
                Carbon::now()->endOfWeek()->format('Y-m-d'),
            ])
            ->reactConfig([
                'component' => 'WeekFilter',
                'preset' => 'thisWeek',
            ]);
    }

    /**
     * Filtro deste mês
     */
    public static function thisMonth(): static
    {
        return static::make('date_this_month')
            ->label('Este Mês')
            ->range()
            ->default([
                Carbon::now()->startOfMonth()->format('Y-m-d'),
                Carbon::now()->endOfMonth()->format('Y-m-d'),
            ])
            ->reactConfig([
                'component' => 'MonthFilter',
                'preset' => 'thisMonth',
            ]);
    }

    /**
     * Filtro deste ano
     */
    public static function thisYear(): static
    {
        return static::make('date_this_year')
            ->label('Este Ano')
            ->range()
            ->default([
                Carbon::now()->startOfYear()->format('Y-m-d'),
                Carbon::now()->endOfYear()->format('Y-m-d'),
            ])
            ->reactConfig([
                'component' => 'YearFilter',
                'preset' => 'thisYear',
            ]);
    }

    /**
     * Filtro de período personalizado
     */
    public static function period(string $field = 'created_at'): static
    {
        return static::make('period')
            ->label('Período')
            ->field($field)
            ->range()
            ->placeholder('Selecione o período...')
            ->reactConfig([
                'component' => 'PeriodFilter',
                'presets' => [
                    'today' => 'Hoje',
                    'yesterday' => 'Ontem',
                    'thisWeek' => 'Esta Semana',
                    'lastWeek' => 'Semana Passada',
                    'thisMonth' => 'Este Mês',
                    'lastMonth' => 'Mês Passado',
                    'thisYear' => 'Este Ano',
                    'lastYear' => 'Ano Passado',
                    'custom' => 'Período Personalizado',
                ],
                'showPresets' => true,
                'allowCustom' => true,
            ]);
    }

    /**
     * Filtro de data de criação
     */
    public static function createdAt(): static
    {
        return static::make('created_at')
            ->label('Data de Criação')
            ->range()
            ->reactConfig([
                'component' => 'CreatedAtFilter',
                'showRelative' => true,
                'relativeOptions' => [
                    'last_hour' => 'Última hora',
                    'last_day' => 'Último dia',
                    'last_week' => 'Última semana',
                    'last_month' => 'Último mês',
                ],
            ]);
    }

    /**
     * Filtro de data de atualização
     */
    public static function updatedAt(): static
    {
        return static::make('updated_at')
            ->label('Data de Atualização')
            ->range()
            ->reactConfig([
                'component' => 'UpdatedAtFilter',
                'showRelative' => true,
            ]);
    }

    /**
     * Filtro com presets
     */
    public function withPresets(array $presets): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'presets' => $presets,
            'showPresets' => true,
        ]);
        return $this;
    }

    /**
     * Filtro com data mínima
     */
    public function minDate(string $date): static
    {
        $this->reactConfig['minDate'] = $date;
        return $this;
    }

    /**
     * Filtro com data máxima
     */
    public function maxDate(string $date): static
    {
        $this->reactConfig['maxDate'] = $date;
        return $this;
    }

    /**
     * Aplicar filtro à query
     */
    protected function applyFilter(Builder $query, mixed $value): Builder
    {
        if (!$this->field) {
            return $query;
        }

        if ($this->isRange) {
            if (!is_array($value) || count($value) !== 2) {
                return $query;
            }

            [$start, $end] = $value;
            
            if ($start) {
                $query->whereDate($this->field, '>=', Carbon::parse($start));
            }
            
            if ($end) {
                $query->whereDate($this->field, '<=', Carbon::parse($end));
            }

            return $query;
        }

        $date = Carbon::parse($value);

        return match($this->operator) {
            '=' => $query->whereDate($this->field, '=', $date),
            '>' => $query->whereDate($this->field, '>', $date),
            '<' => $query->whereDate($this->field, '<', $date),
            '>=' => $query->whereDate($this->field, '>=', $date),
            '<=' => $query->whereDate($this->field, '<=', $date),
            default => $query->whereDate($this->field, $this->operator, $date),
        };
    }

    /**
     * Converter para array (com configurações React)
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        return array_merge($data, [
            'isRange' => $this->isRange,
            'format' => $this->format,
            'displayFormat' => $this->displayFormat,
            'operator' => $this->operator,
            'frontend' => [
                'component' => 'DateFilter',
                'type' => $this->type,
                'config' => array_merge($this->reactConfig, [
                    'isRange' => $this->isRange,
                    'format' => $this->format,
                    'displayFormat' => $this->displayFormat,
                    'operator' => $this->operator,
                ]),
            ],
        ]);
    }
} 
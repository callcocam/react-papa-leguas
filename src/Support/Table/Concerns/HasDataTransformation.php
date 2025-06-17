<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Closure;
use Carbon\Carbon;
use Illuminate\Support\Str;

/**
 * Trait para sistema de transformação de dados avançado da tabela
 */
trait HasDataTransformation
{
    /**
     * Configurações de transformação
     */
    protected array $transformationConfig = [
        'enabled' => true,
        'global_transformers' => [],
        'column_transformers' => [],
        'row_transformers' => [],
        'aggregators' => [],
        'validators' => [],
        'sanitizers' => [],
        'formatters' => [],
    ];

    /**
     * Cache de dados transformados
     */
    protected ?array $transformedDataCache = null;

    /**
     * Habilitar transformação de dados
     */
    public function dataTransformation(bool $enabled = true): static
    {
        $this->transformationConfig['enabled'] = $enabled;
        
        return $this;
    }

    /**
     * Adicionar transformador global
     */
    public function addGlobalTransformer(Closure $transformer): static
    {
        $this->transformationConfig['global_transformers'][] = $transformer;
        
        return $this;
    }

    /**
     * Adicionar transformador de coluna
     */
    public function addColumnTransformer(string $column, Closure $transformer): static
    {
        if (!isset($this->transformationConfig['column_transformers'][$column])) {
            $this->transformationConfig['column_transformers'][$column] = [];
        }
        
        $this->transformationConfig['column_transformers'][$column][] = $transformer;
        
        return $this;
    }

    /**
     * Adicionar transformador de linha
     */
    public function addRowTransformer(Closure $transformer): static
    {
        $this->transformationConfig['row_transformers'][] = $transformer;
        
        return $this;
    }

    /**
     * Adicionar agregador
     */
    public function addAggregator(string $name, Closure $aggregator): static
    {
        $this->transformationConfig['aggregators'][$name] = $aggregator;
        
        return $this;
    }

    /**
     * Adicionar formatador
     */
    public function addFormatter(string $type, Closure $formatter): static
    {
        $this->transformationConfig['formatters'][$type] = $formatter;
        
        return $this;
    }

    /**
     * Transformar dados principais
     */
    public function transformData($data, Request $request): array
    {
        if (!$this->transformationConfig['enabled']) {
            return $this->normalizeData($data);
        }

        // Cache para evitar reprocessamento
        $cacheKey = md5(serialize([$data, $request->all()]));
        if (isset($this->transformedDataCache[$cacheKey])) {
            return $this->transformedDataCache[$cacheKey];
        }

        $transformedData = $this->processDataTransformation($data, $request);
        
        // Cache do resultado
        $this->transformedDataCache[$cacheKey] = $transformedData;
        
        return $transformedData;
    }

    /**
     * Processar transformação dos dados
     */
    protected function processDataTransformation($data, Request $request): array
    {
        $normalizedData = $this->normalizeData($data);
        
        // Aplicar transformadores globais
        foreach ($this->transformationConfig['global_transformers'] as $transformer) {
            $normalizedData = $transformer($normalizedData, $request, $this);
        }

        // Transformar linhas individuais
        if (isset($normalizedData['data']) && is_array($normalizedData['data'])) {
            $normalizedData['data'] = array_map(function ($row) use ($request) {
                return $this->transformRow($row, $request);
            }, $normalizedData['data']);
        }

        // Aplicar agregações
        $normalizedData['aggregations'] = $this->applyAggregations($normalizedData['data'] ?? []);

        // Adicionar metadados de transformação
        $normalizedData['transformation_meta'] = $this->getTransformationMeta();

        return $normalizedData;
    }

    /**
     * Transformar linha individual
     */
    protected function transformRow(array $row, Request $request): array
    {
        // Aplicar transformadores de linha
        foreach ($this->transformationConfig['row_transformers'] as $transformer) {
            $row = $transformer($row, $request, $this);
        }

        // Aplicar transformadores de coluna
        foreach ($this->transformationConfig['column_transformers'] as $column => $transformers) {
            if (isset($row[$column])) {
                foreach ($transformers as $transformer) {
                    $row[$column] = $transformer($row[$column], $row, $request, $this);
                }
            }
        }

        // Aplicar formatadores automáticos
        $row = $this->applyAutoFormatters($row);

        return $row;
    }

    /**
     * Aplicar formatadores automáticos
     */
    protected function applyAutoFormatters(array $row): array
    {
        foreach ($row as $key => $value) {
            // Formatação de datas
            if ($this->isDateField($key, $value)) {
                $row[$key] = $this->formatDate($value);
            }
            
            // Formatação de números
            elseif ($this->isNumericField($key, $value)) {
                $row[$key] = $this->formatNumber($value);
            }
            
            // Formatação de moeda
            elseif ($this->isCurrencyField($key, $value)) {
                $row[$key] = $this->formatCurrency($value);
            }
            
            // Formatação de texto
            elseif ($this->isTextField($key, $value)) {
                $row[$key] = $this->formatText($value);
            }
        }

        return $row;
    }

    /**
     * Aplicar agregações
     */
    protected function applyAggregations(array $data): array
    {
        $aggregations = [];

        foreach ($this->transformationConfig['aggregators'] as $name => $aggregator) {
            $aggregations[$name] = $aggregator($data, $this);
        }

        return $aggregations;
    }

    /**
     * Normalizar dados de diferentes fontes
     */
    protected function normalizeData($data): array
    {
        // Se é uma Collection do Eloquent
        if ($data instanceof Collection) {
            return [
                'data' => $data->toArray(),
                'total' => $data->count(),
                'per_page' => $data->count(),
                'current_page' => 1,
                'last_page' => 1,
            ];
        }

        // Se é um LengthAwarePaginator
        if ($data instanceof LengthAwarePaginator) {
            return [
                'data' => $data->items(),
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ];
        }

        // Se é um array simples
        if (is_array($data)) {
            // Se já tem estrutura de paginação
            if (isset($data['data']) && is_array($data['data'])) {
                return $data;
            }
            
            // Se é array de dados simples
            return [
                'data' => $data,
                'total' => count($data),
                'per_page' => count($data),
                'current_page' => 1,
                'last_page' => 1,
            ];
        }

        // Fallback para outros tipos
        return [
            'data' => [],
            'total' => 0,
            'per_page' => 0,
            'current_page' => 1,
            'last_page' => 1,
        ];
    }

    /**
     * Verificar se é campo de data
     */
    protected function isDateField(string $key, $value): bool
    {
        $dateFields = ['created_at', 'updated_at', 'deleted_at', 'date', 'birth_date'];
        
        return in_array($key, $dateFields) || 
               (is_string($value) && $this->isValidDateString($value));
    }

    /**
     * Verificar se é campo numérico
     */
    protected function isNumericField(string $key, $value): bool
    {
        $numericFields = ['id', 'count', 'quantity', 'amount', 'total'];
        
        return in_array($key, $numericFields) || is_numeric($value);
    }

    /**
     * Verificar se é campo de moeda
     */
    protected function isCurrencyField(string $key, $value): bool
    {
        $currencyFields = ['price', 'cost', 'value', 'salary', 'balance'];
        
        return in_array($key, $currencyFields) && is_numeric($value);
    }

    /**
     * Verificar se é campo de texto
     */
    protected function isTextField(string $key, $value): bool
    {
        return is_string($value) && !$this->isDateField($key, $value);
    }

    /**
     * Verificar se string é uma data válida
     */
    protected function isValidDateString(string $value): bool
    {
        try {
            Carbon::parse($value);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Formatar data
     */
    protected function formatDate($value): array
    {
        if (!$value) {
            return ['raw' => null, 'formatted' => null, 'type' => 'date'];
        }

        try {
            $date = Carbon::parse($value);
            return [
                'raw' => $value,
                'formatted' => $date->format('d/m/Y H:i'),
                'human' => $date->diffForHumans(),
                'iso' => $date->toISOString(),
                'type' => 'date'
            ];
        } catch (\Exception $e) {
            return ['raw' => $value, 'formatted' => $value, 'type' => 'date'];
        }
    }

    /**
     * Formatar número
     */
    protected function formatNumber($value): array
    {
        if (!is_numeric($value)) {
            return ['raw' => $value, 'formatted' => $value, 'type' => 'number'];
        }

        return [
            'raw' => $value,
            'formatted' => number_format($value, 0, ',', '.'),
            'type' => 'number'
        ];
    }

    /**
     * Formatar moeda
     */
    protected function formatCurrency($value): array
    {
        if (!is_numeric($value)) {
            return ['raw' => $value, 'formatted' => $value, 'type' => 'currency'];
        }

        return [
            'raw' => $value,
            'formatted' => 'R$ ' . number_format($value, 2, ',', '.'),
            'type' => 'currency'
        ];
    }

    /**
     * Formatar texto
     */
    protected function formatText($value): array
    {
        if (!is_string($value)) {
            return ['raw' => $value, 'formatted' => $value, 'type' => 'text'];
        }

        return [
            'raw' => $value,
            'formatted' => trim($value),
            'excerpt' => Str::limit($value, 100),
            'type' => 'text'
        ];
    }

    /**
     * Obter metadados de transformação
     */
    protected function getTransformationMeta(): array
    {
        return [
            'enabled' => $this->transformationConfig['enabled'],
            'transformers_count' => [
                'global' => count($this->transformationConfig['global_transformers']),
                'column' => array_sum(array_map('count', $this->transformationConfig['column_transformers'])),
                'row' => count($this->transformationConfig['row_transformers']),
            ],
            'aggregators_count' => count($this->transformationConfig['aggregators']),
            'formatters_count' => count($this->transformationConfig['formatters']),
            'processed_at' => now()->toISOString(),
        ];
    }

    /**
     * Transformadores pré-definidos de conveniência
     */
    public function transformCurrency(string $column): static
    {
        return $this->addColumnTransformer($column, function ($value) {
            return $this->formatCurrency($value);
        });
    }

    public function transformDate(string $column, string $format = 'd/m/Y'): static
    {
        return $this->addColumnTransformer($column, function ($value) use ($format) {
            if (!$value) return null;
            
            try {
                return Carbon::parse($value)->format($format);
            } catch (\Exception $e) {
                return $value;
            }
        });
    }

    public function transformBoolean(string $column): static
    {
        return $this->addColumnTransformer($column, function ($value) {
            return $value ? 'Sim' : 'Não';
        });
    }

    public function transformEnum(string $column, array $mapping): static
    {
        return $this->addColumnTransformer($column, function ($value) use ($mapping) {
            return $mapping[$value] ?? $value;
        });
    }

    /**
     * Agregadores pré-definidos
     */
    public function addCountAggregator(): static
    {
        return $this->addAggregator('count', function ($data) {
            return count($data);
        });
    }

    public function addSumAggregator(string $column): static
    {
        return $this->addAggregator("sum_{$column}", function ($data) use ($column) {
            return array_sum(array_column($data, $column));
        });
    }

    public function addAvgAggregator(string $column): static
    {
        return $this->addAggregator("avg_{$column}", function ($data) use ($column) {
            $values = array_column($data, $column);
            return count($values) > 0 ? array_sum($values) / count($values) : 0;
        });
    }

    /**
     * Limpar cache de transformação
     */
    public function clearTransformationCache(): static
    {
        $this->transformedDataCache = null;
        
        return $this;
    }
} 
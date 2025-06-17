<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filtro de texto para React Frontend
 */
class TextFilter extends Filter
{
    /**
     * Tipo do filtro
     */
    protected string $type = 'text';

    /**
     * Operador de busca
     */
    protected string $operator = 'like';

    /**
     * Se a busca é case sensitive
     */
    protected bool $caseSensitive = false;

    /**
     * Configurar operador de busca
     */
    public function operator(string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }

    /**
     * Configurar busca case sensitive
     */
    public function caseSensitive(bool $caseSensitive = true): static
    {
        $this->caseSensitive = $caseSensitive;
        return $this;
    }

    /**
     * Busca que contém o texto
     */
    public function contains(): static
    {
        $this->operator = 'like';
        $this->reactConfig['placeholder'] = $this->reactConfig['placeholder'] ?: "Buscar {$this->label}...";
        return $this;
    }

    /**
     * Busca que inicia com o texto
     */
    public function startsWith(): static
    {
        $this->operator = 'starts_with';
        $this->reactConfig['placeholder'] = $this->reactConfig['placeholder'] ?: "Inicia com...";
        return $this;
    }

    /**
     * Busca que termina com o texto
     */
    public function endsWith(): static
    {
        $this->operator = 'ends_with';
        $this->reactConfig['placeholder'] = $this->reactConfig['placeholder'] ?: "Termina com...";
        return $this;
    }

    /**
     * Busca exata
     */
    public function exact(): static
    {
        $this->operator = '=';
        $this->reactConfig['placeholder'] = $this->reactConfig['placeholder'] ?: "Valor exato...";
        return $this;
    }

    /**
     * Busca com regex
     */
    public function regex(): static
    {
        $this->operator = 'regex';
        $this->reactConfig['placeholder'] = $this->reactConfig['placeholder'] ?: "Expressão regular...";
        $this->reactConfig['helpText'] = 'Use expressões regulares para busca avançada';
        return $this;
    }

    /**
     * Filtro de busca global (múltiplos campos)
     */
    public static function globalSearch(array $fields = []): static
    {
        return static::make('global_search')
            ->label('Busca Global')
            ->field('global_search')
            ->placeholder('Buscar em todos os campos...')
            ->icon('Search')
            ->clearable()
            ->metadata(['searchFields' => $fields])
            ->reactConfig([
                'component' => 'GlobalSearchFilter',
                'searchFields' => $fields,
                'debounce' => 300,
                'minLength' => 2,
            ])
            ->applyUsing(function (Builder $query, $value) use ($fields) {
                if (empty($fields)) {
                    return $query;
                }

                return $query->where(function (Builder $q) use ($fields, $value) {
                    foreach ($fields as $field) {
                        $q->orWhere($field, 'like', "%{$value}%");
                    }
                });
            });
    }

    /**
     * Filtro de busca em relacionamento
     */
    public static function relationSearch(string $relation, string $field = 'name'): static
    {
        return static::make("{$relation}_search")
            ->label("Buscar " . ucfirst($relation))
            ->field("{$relation}.{$field}")
            ->placeholder("Buscar por {$field}...")
            ->icon('Search')
            ->reactConfig([
                'component' => 'RelationSearchFilter',
                'relation' => $relation,
                'searchField' => $field,
                'debounce' => 300,
            ])
            ->applyUsing(function (Builder $query, $value) use ($relation, $field) {
                return $query->whereHas($relation, function (Builder $q) use ($field, $value) {
                    $q->where($field, 'like', "%{$value}%");
                });
            });
    }

    /**
     * Filtro de busca com autocompletar
     */
    public function autocomplete(array $options = []): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'component' => 'AutocompleteFilter',
            'autocomplete' => [
                'enabled' => true,
                'options' => $options,
                'minLength' => 2,
                'maxResults' => 10,
                'debounce' => 300,
                'remote' => empty($options),
            ],
        ]);
        return $this;
    }

    /**
     * Filtro com sugestões
     */
    public function withSuggestions(array $suggestions): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'suggestions' => [
                'enabled' => true,
                'items' => $suggestions,
                'showOnFocus' => true,
                'maxVisible' => 5,
            ],
        ]);
        return $this;
    }

    /**
     * Filtro com debounce
     */
    public function debounce(int $milliseconds = 300): static
    {
        $this->reactConfig['debounce'] = $milliseconds;
        return $this;
    }

    /**
     * Filtro com comprimento mínimo
     */
    public function minLength(int $length): static
    {
        $this->reactConfig['minLength'] = $length;
        $this->reactConfig['validation']['min'] = $length;
        return $this;
    }

    /**
     * Filtro com comprimento máximo
     */
    public function maxLength(int $length): static
    {
        $this->reactConfig['maxLength'] = $length;
        $this->reactConfig['validation']['max'] = $length;
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

        $value = trim($value);
        if (empty($value)) {
            return $query;
        }

        return match($this->operator) {
            'like' => $this->caseSensitive 
                ? $query->where($this->field, 'like', "%{$value}%")
                : $query->whereRaw("LOWER({$this->field}) LIKE ?", ['%' . strtolower($value) . '%']),
            
            'starts_with' => $this->caseSensitive
                ? $query->where($this->field, 'like', "{$value}%")
                : $query->whereRaw("LOWER({$this->field}) LIKE ?", [strtolower($value) . '%']),
            
            'ends_with' => $this->caseSensitive
                ? $query->where($this->field, 'like', "%{$value}")
                : $query->whereRaw("LOWER({$this->field}) LIKE ?", ['%' . strtolower($value)]),
            
            '=' => $this->caseSensitive
                ? $query->where($this->field, '=', $value)
                : $query->whereRaw("LOWER({$this->field}) = ?", [strtolower($value)]),
            
            'regex' => $query->whereRaw("{$this->field} REGEXP ?", [$value]),
            
            default => $query->where($this->field, $this->operator, $value),
        };
    }

    /**
     * Converter para array (com configurações React)
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        return array_merge($data, [
            'operator' => $this->operator,
            'caseSensitive' => $this->caseSensitive,
            'frontend' => [
                'component' => 'TextFilter',
                'type' => $this->type,
                'config' => array_merge($this->reactConfig, [
                    'operator' => $this->operator,
                    'caseSensitive' => $this->caseSensitive,
                ]),
            ],
        ]);
    }
} 
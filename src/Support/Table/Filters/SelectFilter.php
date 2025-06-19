<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

use Closure;

/**
 * Filtro de seleção com opções predefinidas
 */
class SelectFilter extends Filter
{
    protected array $options = [];
    protected bool $allowEmpty = true;
    protected ?string $emptyLabel = null;
    protected ?string $emptyValue = null;
    protected ?Closure $optionsCallback = null;

    /**
     * Definir opções do select
     */
    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Definir opções via callback
     */
    public function optionsUsing(Closure $callback): static
    {
        $this->optionsCallback = $callback;
        return $this;
    }

    /**
     * Permitir opção vazia
     */
    public function allowEmpty(bool $allow = true, ?string $label = null, ?string $value = null): static
    {
        $this->allowEmpty = $allow;
        $this->emptyLabel = $label ?? 'Todos';
        $this->emptyValue = $value ?? '';
        return $this;
    }

    /**
     * Não permitir opção vazia
     */
    public function disallowEmpty(): static
    {
        $this->allowEmpty = false;
        return $this;
    }

    /**
     * Configuração rápida para status
     */
    public function statusOptions(): static
    {
        return $this->options([
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'pending' => 'Pendente',
            'archived' => 'Arquivado',
        ])->allowEmpty(true, 'Todos os status');
    }

    /**
     * Configuração rápida para booleano
     */
    public function booleanOptions(string $trueLabel = 'Sim', string $falseLabel = 'Não'): static
    {
        return $this->options([
            '1' => $trueLabel,
            '0' => $falseLabel,
        ])->allowEmpty(true, 'Todos');
    }

    /**
     * Configuração para relacionamentos
     */
    public function relationship(string $model, string $labelColumn = 'name', string $valueColumn = 'id'): static
    {
        $this->optionsCallback = function () use ($model, $labelColumn, $valueColumn) {
            if (!class_exists($model)) {
                return [];
            }

            return $model::query()
                ->orderBy($labelColumn)
                ->pluck($labelColumn, $valueColumn)
                ->toArray();
        };

        return $this;
    }

    /**
     * Obter opções do filtro
     */
    public function getOptions(): array
    {
        $options = [];

        // Adicionar opção vazia se permitido
        // if ($this->allowEmpty) {
        //     $options[$this->emptyValue] = $this->emptyLabel;
        // }

        // Obter opções via callback se definido
        if ($this->optionsCallback) {
            $dynamicOptions = $this->evaluate($this->optionsCallback, [
                'filter' => $this,
            ]);
            
            if (is_array($dynamicOptions)) {
                $options = array_merge($options, $dynamicOptions);
            }
        } else {
            // Usar opções estáticas
            $options = array_merge($options, $this->options);
        }

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function apply($query, mixed $value): void
    {
        // Não aplicar filtro se valor é vazio e permitido
        if ($this->allowEmpty && ($value === $this->emptyValue || $value === '' || is_null($value))) {
            return;
        }

        // Verificar se é busca em relacionamento
        if (str_contains($this->key, '.')) {
            $this->applyRelationshipFilter($query, $value);
            return;
        }

        // Aplicar filtro na coluna
        if ($this->multiple) {
            // Múltiplos valores
            $values = is_array($value) ? $value : [$value];
            $query->whereIn($this->key, $values);
        } else {
            // Valor único
            $query->where($this->key, $value);
        }
    }

    /**
     * Aplicar filtro em relacionamento
     */
    protected function applyRelationshipFilter($query, mixed $value): void
    {
        [$relation, $relationColumn] = explode('.', $this->key, 2);

        $query->whereHas($relation, function ($q) use ($relationColumn, $value) {
            if ($this->multiple) {
                $values = is_array($value) ? $value : [$value];
                $q->whereIn($relationColumn, $values);
            } else {
                $q->where($relationColumn, $value);
            }
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'select';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->getOptions(),
            'allow_empty' => $this->allowEmpty,
            'empty_label' => $this->emptyLabel,
            'empty_value' => $this->emptyValue,
        ]);
    }
} 
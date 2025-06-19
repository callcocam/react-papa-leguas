<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

/**
 * Filtro booleano para valores verdadeiro/falso
 */
class BooleanFilter extends Filter
{
    protected string $trueLabel = 'Sim';
    protected string $falseLabel = 'Não';
    protected string $allLabel = 'Todos';
    protected mixed $trueValue = 1;
    protected mixed $falseValue = 0;
    protected bool $allowAll = true;

    /**
     * Definir labels para os valores
     */
    public function labels(string $trueLabel, string $falseLabel, string $allLabel = 'Todos'): static
    {
        $this->trueLabel = $trueLabel;
        $this->falseLabel = $falseLabel;
        $this->allLabel = $allLabel;
        return $this;
    }

    /**
     * Definir valores para verdadeiro e falso
     */
    public function values(mixed $trueValue, mixed $falseValue): static
    {
        $this->trueValue = $trueValue;
        $this->falseValue = $falseValue;
        return $this;
    }

    /**
     * Permitir opção "Todos"
     */
    public function allowAll(bool $allow = true): static
    {
        $this->allowAll = $allow;
        return $this;
    }

    /**
     * Configuração para campos ativos/inativos
     */
    public function activeInactive(): static
    {
        return $this->labels('Ativo', 'Inativo', 'Todos')
                   ->values(1, 0);
    }

    /**
     * Configuração para campos habilitado/desabilitado
     */
    public function enabledDisabled(): static
    {
        return $this->labels('Habilitado', 'Desabilitado', 'Todos')
                   ->values(true, false);
    }

    /**
     * Configuração para campos verificado/não verificado
     */
    public function verifiedUnverified(): static
    {
        return $this->labels('Verificado', 'Não verificado', 'Todos')
                   ->values(1, 0);
    }

    /**
     * Configuração para campos publicado/não publicado
     */
    public function publishedUnpublished(): static
    {
        return $this->labels('Publicado', 'Não publicado', 'Todos')
                   ->values(1, 0);
    }

    /**
     * Obter opções do filtro
     */
    public function getOptions(): array
    {
        $options = [];

        if ($this->allowAll) {
            $options[''] = $this->allLabel;
        }

        $options[(string) $this->trueValue] = $this->trueLabel;
        $options[(string) $this->falseValue] = $this->falseLabel;

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    protected function apply($query, mixed $value): void
    {
        // Não aplicar filtro se valor é vazio e "Todos" é permitido
        if ($this->allowAll && ($value === '' || is_null($value))) {
            return;
        }

        // Converter valor para o tipo apropriado
        $filterValue = $this->normalizeValue($value);

        // Verificar se é busca em relacionamento
        if (str_contains($this->key, '.')) {
            $this->applyRelationshipFilter($query, $filterValue);
            return;
        }

        // Aplicar filtro na coluna
        $query->where($this->key, $filterValue);
    }

    /**
     * Normalizar valor do filtro
     */
    protected function normalizeValue(mixed $value): mixed
    {
        // Se o valor é string, converter para o tipo apropriado
        if (is_string($value)) {
            if ($value === (string) $this->trueValue) {
                return $this->trueValue;
            }
            if ($value === (string) $this->falseValue) {
                return $this->falseValue;
            }
        }

        return $value;
    }

    /**
     * Aplicar filtro em relacionamento
     */
    protected function applyRelationshipFilter($query, mixed $value): void
    {
        [$relation, $relationColumn] = explode('.', $this->key, 2);

        $query->whereHas($relation, function ($q) use ($relationColumn, $value) {
            $q->where($relationColumn, $value);
        });
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'boolean';
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'options' => $this->getOptions(),
            'true_label' => $this->trueLabel,
            'false_label' => $this->falseLabel,
            'all_label' => $this->allLabel,
            'true_value' => $this->trueValue,
            'false_value' => $this->falseValue,
            'allow_all' => $this->allowAll,
        ]);
    }
} 
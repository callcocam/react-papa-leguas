<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Filters;

class NumberFilter extends Filter
{
    protected string $operator = '=';
    protected ?float $min = null;
    protected ?float $max = null;
    protected ?float $step = null;

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'number';
    }

    public function operator(string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }

    public function min(float $min): static
    {
        $this->min = $min;
        return $this;
    }

    public function max(float $max): static
    {
        $this->max = $max;
        return $this;
    }

    public function step(float $step): static
    {
        $this->step = $step;
        return $this;
    }

    public function range(float $min, float $max): static
    {
        return $this->min($min)->max($max);
    }

    // Operator shortcuts
    public function equals(): static
    {
        return $this->operator('=');
    }

    public function greaterThan(): static
    {
        return $this->operator('>');
    }

    public function lessThan(): static
    {
        return $this->operator('<');
    }

    public function greaterThanOrEqual(): static
    {
        return $this->operator('>=');
    }

    public function lessThanOrEqual(): static
    {
        return $this->operator('<=');
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getMin(): ?float
    {
        return $this->min;
    }

    public function getMax(): ?float
    {
        return $this->max;
    }

    public function getStep(): ?float
    {
        return $this->step;
    }

    protected function defaultApply($query, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $query->where($this->getKey(), $this->operator, $value);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'operator' => $this->getOperator(),
            'min' => $this->getMin(),
            'max' => $this->getMax(),
            'step' => $this->getStep(),
        ]);
    }
}

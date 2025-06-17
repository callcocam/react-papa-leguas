<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Filters;

class DateFilter extends Filter
{
    protected string $operator = '=';
    protected string $format = 'Y-m-d';
    protected bool $includeTime = false;

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'date';
    }

    public function operator(string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }

    public function format(string $format): static
    {
        $this->format = $format;
        return $this;
    }

    public function includeTime(bool $includeTime = true): static
    {
        $this->includeTime = $includeTime;
        if ($includeTime) {
            $this->type = 'datetime';
            $this->format = 'Y-m-d H:i:s';
        }
        return $this;
    }

    // Operator shortcuts
    public function equals(): static
    {
        return $this->operator('=');
    }

    public function before(): static
    {
        return $this->operator('<');
    }

    public function after(): static
    {
        return $this->operator('>');
    }

    public function beforeOrEqual(): static
    {
        return $this->operator('<=');
    }

    public function afterOrEqual(): static
    {
        return $this->operator('>=');
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function shouldIncludeTime(): bool
    {
        return $this->includeTime;
    }

    protected function defaultApply($query, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if ($this->includeTime) {
            $query->where($this->getKey(), $this->operator, $value);
        } else {
            $query->whereDate($this->getKey(), $this->operator, $value);
        }
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'operator' => $this->getOperator(),
            'format' => $this->getFormat(),
            'includeTime' => $this->shouldIncludeTime(),
        ]);
    }
}

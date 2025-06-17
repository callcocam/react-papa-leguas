<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Filters;

class TextFilter extends Filter
{
    protected string $operator = 'like';

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'text';
    }

    public function operator(string $operator): static
    {
        $this->operator = $operator;
        return $this;
    }

    public function exact(): static
    {
        return $this->operator('=');
    }

    public function contains(): static
    {
        return $this->operator('like');
    }

    public function startsWith(): static
    {
        return $this->operator('starts_with');
    }

    public function endsWith(): static
    {
        return $this->operator('ends_with');
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    protected function defaultApply($query, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        switch ($this->operator) {
            case 'like':
                $query->where($this->getKey(), 'like', "%{$value}%");
                break;
            case 'starts_with':
                $query->where($this->getKey(), 'like', "{$value}%");
                break;
            case 'ends_with':
                $query->where($this->getKey(), 'like', "%{$value}");
                break;
            default:
                $query->where($this->getKey(), $this->operator, $value);
                break;
        }
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'operator' => $this->getOperator(),
        ]);
    }
}

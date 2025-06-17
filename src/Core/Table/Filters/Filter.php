<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Filters;

use Callcocam\ReactPapaLeguas\Core;
use Closure;

class Filter
{
    use Core\Concerns\EvaluatesClosures;
    use Core\Concerns\BelongsToName;
    use Core\Concerns\BelongsToLabel;
    use Core\Concerns\BelongsToPlaceholder;

    protected string $key;
    protected string $type = 'text';
    protected array $options = [];
    protected bool $multiple = false;
    protected bool $searchable = false;
    protected $defaultValue = null;
    protected ?Closure $query = null;

    public function __construct(string $key, ?string $label = null)
    {
        $this->key = $key;
        $this->name = $key;
        $this->label = $label ?? ucfirst(str_replace(['_', '-'], ' ', $key));
    }

    public static function make(string $key, ?string $label = null): static
    {
        return new static($key, $label);
    }

    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function defaultValue($value): static
    {
        $this->defaultValue = $value;
        return $this;
    }

    public function query(Closure $callback): static
    {
        $this->query = $callback;
        return $this;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    public function getQuery(): ?Closure
    {
        return $this->query;
    }

    public function apply($query, $value): void
    {
        if ($this->query) {
            $this->evaluate($this->query, ['query' => $query, 'value' => $value]);
        } else {
            $this->defaultApply($query, $value);
        }
    }

    protected function defaultApply($query, $value): void
    {
        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            return;
        }

        if ($this->isMultiple() && is_array($value)) {
            $query->whereIn($this->getKey(), $value);
        } else {
            $query->where($this->getKey(), $value);
        }
    }

    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'placeholder' => $this->getPlaceholder(),
            'options' => $this->getOptions(),
            'multiple' => $this->isMultiple(),
            'searchable' => $this->isSearchable(),
            'defaultValue' => $this->getDefaultValue(),
        ];
    }
}

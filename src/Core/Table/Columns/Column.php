<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Columns;

use Callcocam\ReactPapaLeguas\Core;
use Closure;

class Column
{
    use Core\Concerns\EvaluatesClosures;
    use Core\Concerns\BelongsToName;
    use Core\Concerns\BelongsToLabel;
    use Core\Concerns\BelongsToSearchable;
    use Core\Concerns\BelongsToSortable;
    use Core\Concerns\BelongsToHidden;

    protected string $key;
    protected string $type = 'text';
    protected string $align = 'left';
    protected ?string $width = null;
    protected bool $resizable = true;
    protected bool $sticky = false;
    protected ?Closure $formatValue = null;
    protected array $attributes = [];

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

    public function align(string $align): static
    {
        $this->align = $align;
        return $this;
    }

    public function width(string $width): static
    {
        $this->width = $width;
        return $this;
    }

    public function resizable(bool $resizable = true): static
    {
        $this->resizable = $resizable;
        return $this;
    }

    public function sticky(bool $sticky = true): static
    {
        $this->sticky = $sticky;
        return $this;
    }

    public function formatValue(Closure $callback): static
    {
        $this->formatValue = $callback;
        return $this;
    }

    public function attributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
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

    public function getAlign(): string
    {
        return $this->align;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function isResizable(): bool
    {
        return $this->resizable;
    }

    public function isSticky(): bool
    {
        return $this->sticky;
    }

    public function getFormattedValue($value, $record = null)
    {
        if ($this->formatValue) {
            return $this->evaluate($this->formatValue, ['value' => $value, 'record' => $record]);
        }

        return $value;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->getKey(),
            'name' => $this->getName(),
            'label' => $this->getLabel(),
            'type' => $this->getType(),
            'align' => $this->getAlign(),
            'width' => $this->getWidth(),
            'searchable' => $this->isSearchable(),
            'sortable' => $this->isSortable(),
            'hidden' => $this->isHidden(),
            'resizable' => $this->isResizable(),
            'sticky' => $this->isSticky(),
            'attributes' => $this->getAttributes(),
        ];
    }
}
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Columns;

class TextColumn extends Column
{
    protected bool $copyable = false;
    protected ?string $placeholder = null;
    protected ?int $limit = null;
    protected bool $wrap = true;

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'text';
    }

    public function copyable(bool $copyable = true): static
    {
        $this->copyable = $copyable;
        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    public function wrap(bool $wrap = true): static
    {
        $this->wrap = $wrap;
        return $this;
    }

    public function isCopyable(): bool
    {
        return $this->copyable;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function shouldWrap(): bool
    {
        return $this->wrap;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'copyable' => $this->isCopyable(),
            'placeholder' => $this->getPlaceholder(),
            'limit' => $this->getLimit(),
            'wrap' => $this->shouldWrap(),
        ]);
    }
}

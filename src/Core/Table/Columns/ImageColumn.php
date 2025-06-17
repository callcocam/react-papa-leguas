<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Columns;

class ImageColumn extends Column
{
    protected string $size = 'sm'; // xs, sm, md, lg, xl
    protected string $shape = 'rounded'; // rounded, circle, square
    protected ?string $defaultImage = null;
    protected bool $zoomable = false;
    protected ?string $alt = null;
    protected array $transformations = [];

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'image';
        $this->align = 'center';
        $this->sortable = false;
    }

    public function size(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    public function shape(string $shape): static
    {
        $this->shape = $shape;
        return $this;
    }

    public function defaultImage(string $url): static
    {
        $this->defaultImage = $url;
        return $this;
    }

    public function zoomable(bool $zoomable = true): static
    {
        $this->zoomable = $zoomable;
        return $this;
    }

    public function alt(string $alt): static
    {
        $this->alt = $alt;
        return $this;
    }

    public function transformations(array $transformations): static
    {
        $this->transformations = $transformations;
        return $this;
    }

    // Size shortcuts
    public function extraSmall(): static
    {
        return $this->size('xs');
    }

    public function small(): static
    {
        return $this->size('sm');
    }

    public function medium(): static
    {
        return $this->size('md');
    }

    public function large(): static
    {
        return $this->size('lg');
    }

    public function extraLarge(): static
    {
        return $this->size('xl');
    }

    // Shape shortcuts
    public function circle(): static
    {
        return $this->shape('circle');
    }

    public function square(): static
    {
        return $this->shape('square');
    }

    public function rounded(): static
    {
        return $this->shape('rounded');
    }

    // Common presets
    public function avatar(): static
    {
        return $this->size('sm')
            ->shape('circle')
            ->zoomable(true);
    }

    public function thumbnail(): static
    {
        return $this->size('md')
            ->shape('rounded')
            ->zoomable(true);
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function getShape(): string
    {
        return $this->shape;
    }

    public function getDefaultImage(): ?string
    {
        return $this->defaultImage;
    }

    public function isZoomable(): bool
    {
        return $this->zoomable;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function getTransformations(): array
    {
        return $this->transformations;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'size' => $this->getSize(),
            'shape' => $this->getShape(),
            'defaultImage' => $this->getDefaultImage(),
            'zoomable' => $this->isZoomable(),
            'alt' => $this->getAlt(),
            'transformations' => $this->getTransformations(),
        ]);
    }
}

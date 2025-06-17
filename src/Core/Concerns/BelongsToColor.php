<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToColor
{
    /**
     * The color for the component.
     *
     * @var string|null
     */
    protected Closure|string|null $color = null;

    /**
     * The variant for the component.
     *
     * @var string
     */
    protected string $variant = 'default';

    /**
     * The size for the component.
     *
     * @var string
     */
    protected string $size = 'md';

    /**
     * Set the color for the component.
     *
     * @param Closure|string $color
     * @return $this
     */
    public function color(Closure|string $color): static
    {
        $this->color = $color;

        return $this;
    }

    /**
     * Set the variant for the component.
     *
     * @param string $variant
     * @return $this
     */
    public function variant(string $variant): static
    {
        $this->variant = $variant;

        return $this;
    }

    /**
     * Set the size for the component.
     *
     * @param string $size
     * @return $this
     */
    public function size(string $size): static
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get the color for the component.
     *
     * @return string|null
     */
    public function getColor(): ?string
    {
        return $this->evaluate($this->color);
    }

    /**
     * Get the variant for the component.
     *
     * @return string
     */
    public function getVariant(): string
    {
        return $this->variant;
    }

    /**
     * Get the size for the component.
     *
     * @return string
     */
    public function getSize(): string
    {
        return $this->size;
    }
}

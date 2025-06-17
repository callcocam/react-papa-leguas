<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToColumn
{
    /**
     * The column width.
     *
     * @var string|null
     */
    protected ?string $width = null;

    /**
     * The column alignment.
     *
     * @var string
     */
    protected string $alignment = 'left';

    /**
     * Whether the column is fixed.
     *
     * @var bool
     */
    protected bool $fixed = false;

    /**
     * Set the column width.
     *
     * @param string $width
     * @return $this
     */
    public function width(string $width): static
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Set the column alignment.
     *
     * @param string $alignment
     * @return $this
     */
    public function alignment(string $alignment): static
    {
        $this->alignment = $alignment;

        return $this;
    }

    /**
     * Set the column as fixed.
     *
     * @param bool $fixed
     * @return $this
     */
    public function fixed(bool $fixed = true): static
    {
        $this->fixed = $fixed;

        return $this;
    }

    /**
     * Get the column width.
     *
     * @return string|null
     */
    public function getWidth(): ?string
    {
        return $this->width;
    }

    /**
     * Get the column alignment.
     *
     * @return string
     */
    public function getAlignment(): string
    {
        return $this->alignment;
    }

    /**
     * Check if the column is fixed.
     *
     * @return bool
     */
    public function isFixed(): bool
    {
        return $this->fixed;
    }
}

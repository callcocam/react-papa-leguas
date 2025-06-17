<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToIcon
{
    /**
     * The icon for the component.
     *
     * @var string|null
     */
    protected Closure|string|null $icon = null;

    /**
     * The icon position for the component.
     *
     * @var string
     */
    protected string $iconPosition = 'before';

    /**
     * Set the icon for the component.
     *
     * @param Closure|string $icon
     * @param string $position
     * @return $this
     */
    public function icon(Closure|string $icon, string $position = 'before'): static
    {
        $this->icon = $icon;
        $this->iconPosition = $position;

        return $this;
    }

    /**
     * Get the icon for the component.
     *
     * @return Closure|string|null
     */
    public function getIcon(): Closure|string|null
    {
        return $this->evaluate($this->icon);
    }

    /**
     * Get the icon position for the component.
     *
     * @return string
     */
    public function getIconPosition(): string
    {
        return $this->iconPosition;
    }
}

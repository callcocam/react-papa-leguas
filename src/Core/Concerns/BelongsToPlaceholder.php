<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToPlaceholder
{
    /**
     * The placeholder for the component.
     *
     * @var string|null
     */
    protected Closure|string|null $placeholder = null;

    /**
     * Set the placeholder for the component.
     *
     * @param Closure|string $placeholder
     * @return $this
     */
    public function placeholder(Closure|string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    /**
     * Get the placeholder for the component.
     *
     * @return Closure|string|null
     */
    public function getPlaceholder(): Closure|string|null
    {
        return $this->evaluate($this->placeholder);
    }
}

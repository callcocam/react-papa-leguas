<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToDescription
{
    /**
     * The description for the component.
     *
     * @var string|null
     */
    protected Closure|string|null $description = null;

    /**
     * Set the description for the component.
     *
     * @param Closure|string $description
     * @return $this
     */
    public function description(Closure|string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the description for the component.
     *
     * @return Closure|string|null
     */
    public function getDescription(): Closure|string|null
    {
        return $this->evaluate($this->description);
    }
}

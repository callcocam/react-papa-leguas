<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToId
{
    /**
     * The id for the component.
     *
     * @var string|null
     */
    protected Closure|string|null $id = null;

    /**
     * Set the id for the component.
     *
     * @param Closure|string $id
     * @return $this
     */
    public function id(Closure|string $id): static
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the id for the component.
     *
     * @return Closure|string|null
     */
    public function getId(): Closure|string|null
    {
        return $this->evaluate($this->id);
    }
}

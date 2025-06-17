<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToRequired
{
    /**
     * Whether the component is required.
     *
     * @var bool
     */
    protected Closure|bool $required = false;

    /**
     * Set the component as required.
     *
     * @param Closure|bool $condition
     * @return $this
     */
    public function required(Closure|bool $condition = true): static
    {
        $this->required = $condition;

        return $this;
    }

    /**
     * Check if the component is required.
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->evaluate($this->required);
    }
}

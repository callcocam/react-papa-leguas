<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToDisabled
{
    /**
     * Whether the component is disabled.
     *
     * @var bool
     */
    protected Closure|bool $disabled = false;

    /**
     * Set the component as disabled.
     *
     * @param Closure|bool $condition
     * @return $this
     */
    public function disabled(Closure|bool $condition = true): static
    {
        $this->disabled = $condition;

        return $this;
    }

    /**
     * Check if the component is disabled.
     *
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->evaluate($this->disabled);
    }
}

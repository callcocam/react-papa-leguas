<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToHidden
{
    /**
     * Whether the component is hidden.
     *
     * @var bool
     */
    protected Closure|bool $hidden = false;

    /**
     * Set the component as hidden.
     *
     * @param Closure|bool $condition
     * @return $this
     */
    public function hidden(Closure|bool $condition = true): static
    {
        $this->hidden = $condition;

        return $this;
    }

    /**
     * Check if the component is hidden.
     *
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->evaluate($this->hidden);
    }

    /**
     * Check if the component is visible.
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return ! $this->isHidden();
    }
}

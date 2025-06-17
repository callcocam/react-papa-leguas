<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToName
{
    /**
     * The name for the component.
     *
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * Set the name for the component.
     *
     * @param string $name
     * @return $this
     */
    public function name(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name for the component.
     *
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }
}

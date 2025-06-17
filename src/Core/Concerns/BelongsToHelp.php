<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToHelp
{
    /**
     * The help text for the component.
     *
     * @var string|null
     */
    protected Closure|string|null $helpText = null;

    /**
     * The help position for the component.
     *
     * @var string
     */
    protected string $helpPosition = 'below';

    /**
     * Set the help text for the component.
     *
     * @param Closure|string $helpText
     * @param string $position
     * @return $this
     */
    public function help(Closure|string $helpText, string $position = 'below'): static
    {
        $this->helpText = $helpText;
        $this->helpPosition = $position;

        return $this;
    }

    /**
     * Get the help text for the component.
     *
     * @return Closure|string|null
     */
    public function getHelpText(): Closure|string|null
    {
        return $this->evaluate($this->helpText);
    }

    /**
     * Get the help position for the component.
     *
     * @return string
     */
    public function getHelpPosition(): string
    {
        return $this->helpPosition;
    }
}

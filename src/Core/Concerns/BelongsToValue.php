<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToValue
{
    /**
     * The default value for the component.
     *
     * @var mixed
     */
    protected mixed $defaultValue = null;

    /**
     * The current value for the component.
     *
     * @var mixed
     */
    protected mixed $value = null;

    /**
     * Set the default value for the component.
     *
     * @param mixed $value
     * @return $this
     */
    public function default(mixed $value): static
    {
        $this->defaultValue = $value;

        return $this;
    }

    /**
     * Set the value for the component.
     *
     * @param mixed $value
     * @return $this
     */
    public function value(mixed $value): static
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the default value for the component.
     *
     * @return mixed
     */
    public function getDefaultValue(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Get the value for the component.
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value ?? $this->getDefaultValue();
    }
}

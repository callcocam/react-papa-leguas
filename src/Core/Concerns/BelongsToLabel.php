<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToLabel
{
    /**
     * The label for the model.
     *
     * @var string|null
     */
    protected Closure|string|null $label = null;

    /**
     * The hidden label
     *
     * @var bool|null
     */
    protected ?bool $hiddenLabel = null;

    /**
     * Set the label for the model.
     *
     * @param Closure|string $label
     * @return $this
     */
    public function label(Closure|string $label): static
    {
        $this->label = $label;

        return $this;
    }
    /**
     * Get the label for the model.
     *
     * If no label is set, it will return the name of the model.
     *
     * @return Closure|string|null
     */
    public function getLabel(): Closure|string|null
    {
        return $this->evaluate($this->label);
    }

    /**
     * Set whether the label should be hidden.
     *
     * @param bool $hidden
     * @return $this
     */
    public function hiddenLabel(bool $hidden = true): static
    {
        $this->hiddenLabel = $hidden;

        return $this;
    }
    /**
     * Check if the label is hidden.
     *
     * @return bool
     */
    public function isLabelHidden(): bool
    {
        if(empty($this->label)) {
            return true;
        }
        return $this->hiddenLabel === true;
    }

    /**
     * Check if the label is hidden (alias).
     *
     * @return bool
     */
    public function isHiddenLabel(): bool
    {
        return $this->isLabelHidden();
    }
}

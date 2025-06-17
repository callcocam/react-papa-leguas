<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToSearchable
{
    /**
     * Whether the component is searchable.
     *
     * @var bool
     */
    protected Closure|bool $searchable = false;

    /**
     * The search placeholder text.
     *
     * @var string|null
     */
    protected Closure|string|null $searchPlaceholder = null;

    /**
     * Set the component as searchable.
     *
     * @param Closure|bool $condition
     * @param Closure|string|null $placeholder
     * @return $this
     */
    public function searchable(Closure|bool $condition = true, Closure|string|null $placeholder = null): static
    {
        $this->searchable = $condition;
        $this->searchPlaceholder = $placeholder;

        return $this;
    }

    /**
     * Check if the component is searchable.
     *
     * @return bool
     */
    public function isSearchable(): bool
    {
        return $this->evaluate($this->searchable);
    }

    /**
     * Get the search placeholder.
     *
     * @return string|null
     */
    public function getSearchPlaceholder(): ?string
    {
        return $this->evaluate($this->searchPlaceholder) ?? 'Pesquisar...';
    }
}

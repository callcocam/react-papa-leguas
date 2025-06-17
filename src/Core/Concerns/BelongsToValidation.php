<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;

trait BelongsToValidation
{
    /**
     * The validation rules for the component.
     *
     * @var array
     */
    protected array $rules = [];

    /**
     * The validation messages for the component.
     *
     * @var array
     */
    protected array $validationMessages = [];

    /**
     * Set validation rules for the component.
     *
     * @param array|string $rules
     * @return $this
     */
    public function rules(array|string $rules): static
    {
        $this->rules = is_string($rules) ? [$rules] : $rules;

        return $this;
    }

    /**
     * Set validation messages for the component.
     *
     * @param array $messages
     * @return $this
     */
    public function validationMessages(array $messages): static
    {
        $this->validationMessages = $messages;

        return $this;
    }

    /**
     * Get validation rules for the component.
     *
     * @return array
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Get validation messages for the component.
     *
     * @return array
     */
    public function getValidationMessages(): array
    {
        return $this->validationMessages;
    }
}

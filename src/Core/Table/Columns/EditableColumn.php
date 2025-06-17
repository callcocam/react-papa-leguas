<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Columns;

use Callcocam\ReactPapaLeguas\Core;

class EditableColumn extends Column
{
    use Core\Concerns\BelongsToValidation;

    protected string $editType = 'text';
    protected string $updateRoute = '';
    protected array $updateRouteParameters = [];
    protected bool $requiresConfirmation = false;
    protected string $confirmationTitle = 'Confirmar alteração';
    protected string $confirmationDescription = 'Deseja salvar a alteração?';
    protected ?string $placeholder = null;
    protected array $options = [];
    protected bool $autosave = true;
    protected int $debounceMs = 500;

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'editable';
    }

    public function editType(string $editType): static
    {
        $this->editType = $editType;
        return $this;
    }

    public function updateRoute(string $route, array $parameters = []): static
    {
        $this->updateRoute = $route;
        $this->updateRouteParameters = $parameters;
        return $this;
    }

    public function requiresConfirmation(bool $requiresConfirmation = true): static
    {
        $this->requiresConfirmation = $requiresConfirmation;
        return $this;
    }

    public function confirmationTitle(string $title): static
    {
        $this->confirmationTitle = $title;
        return $this;
    }

    public function confirmationDescription(string $description): static
    {
        $this->confirmationDescription = $description;
        return $this;
    }

    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function autosave(bool $autosave = true): static
    {
        $this->autosave = $autosave;
        return $this;
    }

    public function debounce(int $milliseconds): static
    {
        $this->debounceMs = $milliseconds;
        return $this;
    }

    // Edit types shortcuts
    public function asText(): static
    {
        return $this->editType('text');
    }

    public function asTextarea(): static
    {
        return $this->editType('textarea');
    }

    public function asNumber(): static
    {
        return $this->editType('number');
    }

    public function asSelect(): static
    {
        return $this->editType('select');
    }

    public function asBoolean(): static
    {
        return $this->editType('boolean');
    }

    public function asDate(): static
    {
        return $this->editType('date');
    }

    public function getEditType(): string
    {
        return $this->editType;
    }

    public function getUpdateRoute(): string
    {
        return $this->updateRoute;
    }

    public function getUpdateRouteParameters(): array
    {
        return $this->updateRouteParameters;
    }

    public function getRequiresConfirmation(): bool
    {
        return $this->requiresConfirmation;
    }

    public function getConfirmationTitle(): string
    {
        return $this->confirmationTitle;
    }

    public function getConfirmationDescription(): string
    {
        return $this->confirmationDescription;
    }

    public function getPlaceholder(): ?string
    {
        return $this->placeholder;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function isAutosave(): bool
    {
        return $this->autosave;
    }

    public function getDebounceMs(): int
    {
        return $this->debounceMs;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'editType' => $this->getEditType(),
            'updateRoute' => $this->getUpdateRoute(),
            'updateRouteParameters' => $this->getUpdateRouteParameters(),
            'requiresConfirmation' => $this->getRequiresConfirmation(),
            'confirmationTitle' => $this->getConfirmationTitle(),
            'confirmationDescription' => $this->getConfirmationDescription(),
            'placeholder' => $this->getPlaceholder(),
            'options' => $this->getOptions(),
            'autosave' => $this->isAutosave(),
            'debounceMs' => $this->getDebounceMs(),
            'validation' => $this->getValidation(),
        ]);
    }
}

<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows;

use Callcocam\ReactPapaLeguas\Core\Table\Actions\Action;
use Callcocam\ReactPapaLeguas\Core;

abstract class RowAction extends Action
{
    use Core\Concerns\BelongsToIcon;
    use Core\Concerns\BelongsToColor;
    use Core\Concerns\BelongsToDescription;

    protected string $route = '';
    protected array $routeParameters = [];
    protected string $target = '_self';
    protected bool $requiresConfirmation = false;
    protected string $confirmationTitle = 'Confirmar ação';
    protected string $confirmationDescription = 'Tem certeza que deseja executar esta ação?';

    public function route(string $route, array $parameters = []): static
    {
        $this->route = $route;
        $this->routeParameters = $parameters;
        return $this;
    }

    public function target(string $target): static
    {
        $this->target = $target;
        return $this;
    }

    public function openInNewTab(): static
    {
        $this->target = '_blank';
        return $this;
    }

    public function requiresConfirmation(bool $condition = true): static
    {
        $this->requiresConfirmation = $condition;
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

    public function getRoute(): string
    {
        return $this->route;
    }

    public function getRouteParameters(): array
    {
        return $this->routeParameters;
    }

    public function getTarget(): string
    {
        return $this->target;
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

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'type' => 'row',
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'description' => $this->getDescription(),
            'route' => $this->getRoute(),
            'routeParameters' => $this->getRouteParameters(),
            'target' => $this->getTarget(),
            'requiresConfirmation' => $this->getRequiresConfirmation(),
            'confirmationTitle' => $this->getConfirmationTitle(),
            'confirmationDescription' => $this->getConfirmationDescription(),
        ]);
    }
}

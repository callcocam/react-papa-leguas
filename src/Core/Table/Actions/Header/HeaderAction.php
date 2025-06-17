<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Header;

use Callcocam\ReactPapaLeguas\Core\Table\Actions\Action;
use Callcocam\ReactPapaLeguas\Core;

abstract class HeaderAction extends Action
{
    use Core\Concerns\BelongsToIcon;
    use Core\Concerns\BelongsToColor;
    use Core\Concerns\BelongsToDescription;

    protected string $route = '';
    protected array $routeParameters = [];
    protected string $target = '_self';

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

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'type' => 'header',
            'icon' => $this->getIcon(),
            'color' => $this->getColor(),
            'description' => $this->getDescription(),
            'route' => $this->getRoute(),
            'routeParameters' => $this->getRouteParameters(),
            'target' => $this->getTarget(),
        ]);
    }
}

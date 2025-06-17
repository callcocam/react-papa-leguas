<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

use Closure;

/**
 * Classe base para ações da tabela
 */
abstract class Action
{
    /**
     * ID único da ação
     */
    protected string $id;

    /**
     * Label da ação
     */
    protected string $label;

    /**
     * Ícone da ação (Lucide Vue)
     */
    protected ?string $icon = null;

    /**
     * Cor da ação
     */
    protected string $color = 'primary';

    /**
     * Variante do botão
     */
    protected string $variant = 'default';

    /**
     * Tamanho do botão
     */
    protected string $size = 'sm';

    /**
     * Se a ação está visível
     */
    protected bool $visible = true;

    /**
     * Se a ação está habilitada
     */
    protected bool $enabled = true;

    /**
     * Rota da ação
     */
    protected ?string $route = null;

    /**
     * Parâmetros da rota
     */
    protected array $routeParameters = [];

    /**
     * URL da ação
     */
    protected ?string $url = null;

    /**
     * Método HTTP
     */
    protected string $method = 'GET';

    /**
     * Se requer confirmação
     */
    protected bool $requiresConfirmation = false;

    /**
     * Título da confirmação
     */
    protected string $confirmationTitle = 'Confirmar ação';

    /**
     * Descrição da confirmação
     */
    protected string $confirmationDescription = 'Deseja executar esta ação?';

    /**
     * Callback de visibilidade
     */
    protected ?Closure $visibilityCallback = null;

    /**
     * Callback de habilitação
     */
    protected ?Closure $enabledCallback = null;

    /**
     * Permissões necessárias
     */
    protected array $permissions = [];

    /**
     * Dados extras
     */
    protected array $data = [];

    /**
     * Atributos HTML extras
     */
    protected array $attributes = [];

    /**
     * Classes CSS extras
     */
    protected array $classes = [];

    /**
     * Se abre em nova aba
     */
    protected bool $openInNewTab = false;

    /**
     * Tooltip
     */
    protected ?string $tooltip = null;

    public function __construct(string $id = null)
    {
        $this->id = $id ?? 'action-' . uniqid();
    }

    /**
     * Criar nova instância
     */
    public static function make(string $id = null): static
    {
        return new static($id);
    }

    /**
     * Definir label
     */
    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Definir ícone
     */
    public function icon(string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Definir cor
     */
    public function color(string $color): static
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Variante do botão
     */
    public function variant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    /**
     * Tamanho do botão
     */
    public function size(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    /**
     * Definir rota
     */
    public function route(string $route, array $parameters = []): static
    {
        $this->route = $route;
        $this->routeParameters = $parameters;
        return $this;
    }

    /**
     * Definir URL
     */
    public function url(string $url): static
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Definir método HTTP
     */
    public function method(string $method): static
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Método GET
     */
    public function get(): static
    {
        return $this->method('GET');
    }

    /**
     * Método POST
     */
    public function post(): static
    {
        return $this->method('POST');
    }

    /**
     * Método PUT
     */
    public function put(): static
    {
        return $this->method('PUT');
    }

    /**
     * Método DELETE
     */
    public function delete(): static
    {
        return $this->method('DELETE');
    }

    /**
     * Requer confirmação
     */
    public function requiresConfirmation(bool $requiresConfirmation = true): static
    {
        $this->requiresConfirmation = $requiresConfirmation;
        return $this;
    }

    /**
     * Título da confirmação
     */
    public function confirmationTitle(string $title): static
    {
        $this->confirmationTitle = $title;
        return $this;
    }

    /**
     * Descrição da confirmação
     */
    public function confirmationDescription(string $description): static
    {
        $this->confirmationDescription = $description;
        return $this;
    }

    /**
     * Definir visibilidade condicional
     */
    public function visible(bool|Closure $visible): static
    {
        if ($visible instanceof Closure) {
            $this->visibilityCallback = $visible;
        } else {
            $this->visible = $visible;
        }
        return $this;
    }

    /**
     * Ocultar ação
     */
    public function hidden(): static
    {
        return $this->visible(false);
    }

    /**
     * Definir habilitação condicional
     */
    public function enabled(bool|Closure $enabled): static
    {
        if ($enabled instanceof Closure) {
            $this->enabledCallback = $enabled;
        } else {
            $this->enabled = $enabled;
        }
        return $this;
    }

    /**
     * Desabilitar ação
     */
    public function disabled(): static
    {
        return $this->enabled(false);
    }

    /**
     * Definir permissões
     */
    public function permissions(array $permissions): static
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * Adicionar dados extras
     */
    public function data(array $data): static
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * Adicionar atributos HTML
     */
    public function attributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Adicionar classes CSS
     */
    public function classes(array $classes): static
    {
        $this->classes = array_merge($this->classes, $classes);
        return $this;
    }

    /**
     * Abrir em nova aba
     */
    public function openInNewTab(bool $openInNewTab = true): static
    {
        $this->openInNewTab = $openInNewTab;
        return $this;
    }

    /**
     * Definir tooltip
     */
    public function tooltip(string $tooltip): static
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    /**
     * Verificar se a ação é visível
     */
    public function isVisible($record = null): bool
    {
        if ($this->visibilityCallback) {
            return call_user_func($this->visibilityCallback, $record);
        }
        return $this->visible;
    }

    /**
     * Verificar se a ação está habilitada
     */
    public function isEnabled($record = null): bool
    {
        if ($this->enabledCallback) {
            return call_user_func($this->enabledCallback, $record);
        }
        return $this->enabled;
    }

    /**
     * Obter configuração para o frontend
     */
    public function toArray($record = null): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'icon' => $this->icon,
            'color' => $this->color,
            'variant' => $this->variant,
            'size' => $this->size,
            'visible' => $this->isVisible($record),
            'enabled' => $this->isEnabled($record),
            'route' => $this->route,
            'routeParameters' => $this->routeParameters,
            'url' => $this->url,
            'method' => $this->method,
            'requiresConfirmation' => $this->requiresConfirmation,
            'confirmationTitle' => $this->confirmationTitle,
            'confirmationDescription' => $this->confirmationDescription,
            'permissions' => $this->permissions,
            'data' => $this->data,
            'attributes' => $this->attributes,
            'classes' => $this->classes,
            'openInNewTab' => $this->openInNewTab,
            'tooltip' => $this->tooltip,
        ];
    }

    // Getters
    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }
} 
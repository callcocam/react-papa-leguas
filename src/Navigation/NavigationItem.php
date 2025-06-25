<?php

namespace Callcocam\ReactPapaLeguas\Navigation;

use Illuminate\Support\Facades\Auth;

/**
 * NavigationItem - Representa um item individual de navegação
 * 
 * Encapsula todas as propriedades e comportamentos
 * de um item de menu ou submenu.
 */
class NavigationItem
{
    protected string $key;
    protected ?string $label = null;
    protected ?string $route = null;
    protected array $routeParams = [];
    protected ?string $url = null;
    protected ?string $icon = null;
    protected ?string $permission = null;
    protected array $permissions = [];
    protected ?string $badge = null;
    protected string $badgeVariant = 'default';
    protected bool $active = false;
    protected int $order = 999;
    protected array $subitems = [];
    protected ?string $target = null;
    protected ?string $className = null;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * Definir label
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Definir rota
     */
    public function setRoute(string $route, array $params = []): self
    {
        $this->route = $route;
        $this->routeParams = $params;
        return $this;
    }

    /**
     * Definir URL
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Definir ícone
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Definir permissão
     */
    public function setPermission(string $permission): self
    {
        $this->permission = $permission;
        return $this;
    }

    /**
     * Definir múltiplas permissões
     */
    public function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * Definir badge
     */
    public function setBadge(string $badge, string $variant = 'default'): self
    {
        $this->badge = $badge;
        $this->badgeVariant = $variant;
        return $this;
    }

    /**
     * Definir como ativo
     */
    public function setActive(bool $active): self
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Definir ordem
     */
    public function setOrder(int $order): self
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Definir target
     */
    public function setTarget(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * Definir classe CSS
     */
    public function setClassName(string $className): self
    {
        $this->className = $className;
        return $this;
    }

    /**
     * Adicionar subitem
     */
    public function addSubitem(NavigationItem $subitem): self
    {
        $this->subitems[] = $subitem;
        return $this;
    }

    /**
     * Verificar se tem permissão para exibir item
     */
    public function hasPermission(): bool
    {
        // Se não tem permissão definida, sempre permitir
        if (!$this->permission && empty($this->permissions)) {
            return true;
        }

        $user = Auth::user();
        
        if (!$user) {
            return false;
        }

        // Verificar permissão única
        if ($this->permission) {
            return $this->checkUserPermission($this->permission);
        }

        // Verificar múltiplas permissões (OR)
        if (!empty($this->permissions)) {
            foreach ($this->permissions as $permission) {
                if ($this->checkUserPermission($permission)) {
                    return true;
                }
            }
            return false;
        }

        return true;
    }

    /**
     * Verificar se usuário tem permissão específica
     */
    protected function checkUserPermission(string $permission): bool
    {
        $user = Auth::user();
        
        // Verificar se user tem método hasPermissionTo (Shinobi)
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo($permission);
        }
        
        // Fallback para gates/policies do Laravel
        return $user->can($permission);
    }

    /**
     * Converter para array
     */
    public function toArray(): array
    {
        $data = [
            'key' => $this->key,
            'title' => $this->label ?: ucfirst(str_replace(['-', '_'], ' ', $this->key)),
            'icon' => $this->icon,
            'href' => $this->getHref(),
            'permission' => $this->permission,
            'permissions' => $this->permissions,
            'active' => $this->active,
            'order' => $this->order,
            'target' => $this->target,
            'className' => $this->className,
        ];

        // Adicionar badge se definido
        if ($this->badge) {
            $data['badge'] = [
                'text' => $this->badge,
                'variant' => $this->badgeVariant
            ];
        }

        // Adicionar subitems se existirem
        if (!empty($this->subitems)) {
            $data['subitems'] = array_map(
                fn(NavigationItem $item) => $item->toArray(),
                $this->subitems
            );
        }

        return array_filter($data, fn($value) => $value !== null);
    }

    /**
     * Obter href (URL ou rota)
     */
    protected function getHref(): ?string
    {
        if ($this->url) {
            return $this->url;
        }

        if ($this->route) {
            try {
                return route($this->route, $this->routeParams);
            } catch (\Exception $e) {
                // Se rota não existe, retornar null
                return null;
            }
        }

        return null;
    }

    /**
     * Getters
     */
    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getPermission(): ?string
    {
        return $this->permission;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getOrder(): int
    {
        return $this->order;
    }

    public function getSubitems(): array
    {
        return $this->subitems;
    }
} 
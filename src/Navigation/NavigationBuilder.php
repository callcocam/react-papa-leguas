<?php

namespace Callcocam\ReactPapaLeguas\Navigation;

use Illuminate\Support\Facades\Auth;

/**
 * NavigationBuilder - Construtor fluente de navegação
 * 
 * Permite criar estruturas de navegação dinâmicas com 
 * permissões, sub menus e configurações flexíveis.
 */
class NavigationBuilder
{
    protected array $items = [];
    protected ?NavigationItem $currentItem = null;
    protected ?NavigationItem $currentSubmenu = null;

    /**
     * Criar nova instância do builder
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * Adicionar item de navegação
     */
    public function item(string $key): self
    {
        $this->currentItem = new NavigationItem($key);
        $this->items[$key] = $this->currentItem;
        $this->currentSubmenu = null; // Reset submenu context
        
        return $this;
    }

    /**
     * Definir label do item atual
     */
    public function label(string $label): self
    {
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setLabel($label);
        } elseif ($this->currentItem) {
            $this->currentItem->setLabel($label);
        }
        
        return $this;
    }

    /**
     * Definir rota do item atual
     */
    public function route(string $route, array $params = []): self
    {
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setRoute($route, $params);
        } elseif ($this->currentItem) {
            $this->currentItem->setRoute($route, $params);
        }
        
        return $this;
    }

    /**
     * Definir URL do item atual
     */
    public function url(string $url): self
    {
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setUrl($url);
        } elseif ($this->currentItem) {
            $this->currentItem->setUrl($url);
        }
        
        return $this;
    }

    /**
     * Definir ícone do item atual
     */
    public function icon(string $icon): self
    {
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setIcon($icon);
        } elseif ($this->currentItem) {
            $this->currentItem->setIcon($icon);
        }
        
        return $this;
    }

    /**
     * Definir permissão necessária
     */
    public function permission(string $permission): self
    {
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setPermission($permission);
        } elseif ($this->currentItem) {
            $this->currentItem->setPermission($permission);
        }
        
        return $this;
    }

    /**
     * Definir múltiplas permissões (OR)
     */
    public function permissions(array $permissions): self
    {
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setPermissions($permissions);
        } elseif ($this->currentItem) {
            $this->currentItem->setPermissions($permissions);
        }
        
        return $this;
    }

    /**
     * Definir badge/contador
     */
    public function badge(string $badge, string $variant = 'default'): self
    {  
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setBadge($badge, $variant);
        } elseif ($this->currentItem) {
            $this->currentItem->setBadge($badge, $variant);
        }
        
        return $this;
    }

    /**
     * Marcar como ativo/inativo
     */
    public function active(bool $active = true): self
    {
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setActive($active);
        } elseif ($this->currentItem) {
            $this->currentItem->setActive($active);
        }
        
        return $this;
    }

    /**
     * Definir ordem de exibição
     */
    public function order(int $order): self
    {
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setOrder($order);
        } elseif ($this->currentItem) {
            $this->currentItem->setOrder($order);
        }
        
        return $this;
    }

    /**
     * Adicionar submenu ao item atual
     */
    public function submenu(\Closure $callback): self
    {
        if ($this->currentItem) {
            $callback($this);
        }
        
        return $this;
    }

    /**
     * Adicionar item de submenu
     */
    public function subitem(string $key): self
    {
        if ($this->currentItem) {
            $this->currentSubmenu = new NavigationItem($key);
            $this->currentItem->addSubitem($this->currentSubmenu);
        }
        
        return $this;
    }

    /**
     * Definir target (_blank, _self, etc.)
     */
    public function target(string $target): self
    {
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setTarget($target);
        } elseif ($this->currentItem) {
            $this->currentItem->setTarget($target);
        }
        
        return $this;
    }

    /**
     * Adicionar classes CSS customizadas
     */
    public function className(string $className): self
    {
        if ($this->currentSubmenu) {
            $this->currentSubmenu->setClassName($className);
        } elseif ($this->currentItem) {
            $this->currentItem->setClassName($className);
        }
        
        return $this;
    }

    /**
     * Construir e retornar navegação filtrada por permissões
     */
    public function build(): array
    {
        $navigation = [];
        
        foreach ($this->items as $key => $item) {
            // Verificar permissões do item principal
            if (!$item->hasPermission()) {
                continue;
            }

            $itemData = $item->toArray();
            
            // Filtrar subitems por permissões
            if (!empty($itemData['subitems'])) {
                $itemData['subitems'] = array_filter(
                    $itemData['subitems'], 
                    fn($subitem) => $this->hasPermission($subitem['permission'] ?? null)
                );
                
                // Se não há subitems após filtro e item pai não tem rota própria, remover
                if (empty($itemData['subitems']) && empty($itemData['href'])) {
                    continue;
                }
            }

            $navigation[] = $itemData;
        }

        // Ordenar por ordem definida
        usort($navigation, fn($a, $b) => ($a['order'] ?? 999) <=> ($b['order'] ?? 999));

        return $navigation;
    }

    /**
     * Verificar se usuário tem permissão
     */
    protected function hasPermission(?string $permission): bool
    {
        if (!$permission) {
            return true;
        }

        $user = Auth::user();
        
        if (!$user) {
            return false;
        }
        
        // Verificar se user tem método hasPermission (Shinobi)
        if (method_exists($user, 'hasPermission')) {
            return $user->hasPermission($permission);
        }
        
        // Fallback para gates/policies do Laravel
        return Auth::user()->can($permission);
    }
} 
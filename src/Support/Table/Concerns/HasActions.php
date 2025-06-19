<?php

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\Actions\Action;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\RouteAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\UrlAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\CallbackAction;

/**
 * Trait para gerenciar ações de tabela
 */
trait HasActions
{
    /**
     * Array de ações da tabela
     */
    protected array $actions = [];

    /**
     * Valores aplicados para ações
     */
    protected array $actionContext = [];

    /**
     * Boot do trait HasActions
     */
    protected function bootHasActions(): void
    {
        $this->loadActions();
    }

    /**
     * Carrega as ações definidas no método actions()
     */
    protected function loadActions(): void
    {
        if (method_exists($this, 'actions')) {
            $actions = $this->actions();
            
            if (is_array($actions)) {
                foreach ($actions as $action) {
                    if ($action instanceof Action) {
                        $this->actions[$action->getKey()] = $action;
                    }
                }
            }
        }
    }

    /**
     * Obtém todas as ações
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * Obtém uma ação específica
     */
    public function getAction(string $key): ?Action
    {
        return $this->actions[$key] ?? null;
    }

    /**
     * Verifica se tem uma ação específica
     */
    public function hasAction(string $key): bool
    {
        return isset($this->actions[$key]);
    }

    /**
     * Obtém ações visíveis
     */
    public function getVisibleActions($item = null, array $context = []): array
    {
        $visibleActions = [];
        
        foreach ($this->actions as $action) {
            if ($action->isVisible($item, $context)) {
                $visibleActions[$action->getKey()] = $action;
            }
        }
        
        return $visibleActions;
    }

    /**
     * Obtém ações habilitadas
     */
    public function getEnabledActions($item = null, array $context = []): array
    {
        $enabledActions = [];
        
        foreach ($this->getVisibleActions($item, $context) as $action) {
            if ($action->isEnabled($item, $context)) {
                $enabledActions[$action->getKey()] = $action;
            }
        }
        
        return $enabledActions;
    }

    /**
     * Obtém ações por posição
     */
    public function getActionsByPosition(string $position, $item = null, array $context = []): array
    {
        $actions = [];
        
        foreach ($this->getVisibleActions($item, $context) as $action) {
            if ($action->getPosition() === $position) {
                $actions[$action->getKey()] = $action;
            }
        }
        
        // Ordenar por ordem
        uasort($actions, fn($a, $b) => $a->getOrder() <=> $b->getOrder());
        
        return $actions;
    }

    /**
     * Obtém ações por grupo
     */
    public function getActionsByGroup(?string $group, $item = null, array $context = []): array
    {
        $actions = [];
        
        foreach ($this->getVisibleActions($item, $context) as $action) {
            if ($action->getGroup() === $group) {
                $actions[$action->getKey()] = $action;
            }
        }
        
        // Ordenar por ordem
        uasort($actions, fn($a, $b) => $a->getOrder() <=> $b->getOrder());
        
        return $actions;
    }

    /**
     * Define contexto para ações
     */
    public function setActionContext(array $context): static
    {
        $this->actionContext = array_merge($this->actionContext, $context);
        return $this;
    }

    /**
     * Obtém contexto das ações
     */
    public function getActionContext(): array
    {
        return $this->actionContext;
    }

    /**
     * Limpa contexto das ações
     */
    public function clearActionContext(): static
    {
        $this->actionContext = [];
        return $this;
    }

    /**
     * Executa uma ação (para CallbackAction)
     */
    public function executeAction(string $key, $item = null, array $context = []): mixed
    {
        $action = $this->getAction($key);
        
        if (!$action) {
            return null;
        }
        
        if (!$action instanceof CallbackAction) {
            return null;
        }
        
        $mergedContext = array_merge($this->actionContext, $context);
        
        return $action->execute($item, $mergedContext);
    }

    /**
     * Obtém configuração das ações para serialização
     */
    public function getActionsConfig($item = null, array $context = []): array
    {
        $actions = [];
        $mergedContext = array_merge($this->actionContext, $context);
        
        foreach ($this->getVisibleActions($item, $mergedContext) as $action) {
            $actionArray = $action->toArray($item, $mergedContext);
            if (!empty($actionArray)) {
                $actions[] = $actionArray;
            }
        }
        
        return $actions;
    }

    /**
     * Verifica se a tabela tem ações
     */
    public function hasActions(): bool
    {
        return !empty($this->actions);
    }

    /**
     * Conta total de ações
     */
    public function getActionsCount(): int
    {
        return count($this->actions);
    }

    /**
     * Conta ações visíveis
     */
    public function getVisibleActionsCount($item = null, array $context = []): int
    {
        return count($this->getVisibleActions($item, $context));
    }

    /**
     * Obtém resumo das ações
     */
    public function getActionsSummary($item = null, array $context = []): array
    {
        $mergedContext = array_merge($this->actionContext, $context);
        
        return [
            'total' => $this->getActionsCount(),
            'visible' => $this->getVisibleActionsCount($item, $mergedContext),
            'enabled' => count($this->getEnabledActions($item, $mergedContext)),
            'by_position' => [
                'start' => count($this->getActionsByPosition('start', $item, $mergedContext)),
                'end' => count($this->getActionsByPosition('end', $item, $mergedContext)),
            ],
            'by_type' => $this->getActionsByType($item, $mergedContext),
        ];
    }

    /**
     * Obtém ações agrupadas por tipo
     */
    protected function getActionsByType($item = null, array $context = []): array
    {
        $types = [];
        
        foreach ($this->getVisibleActions($item, $context) as $action) {
            $type = $action->getType();
            $types[$type] = ($types[$type] ?? 0) + 1;
        }
        
        return $types;
    }

    /**
     * Métodos de conveniência para criar ações
     */
    
    /**
     * Cria uma ação de rota
     */
    protected function routeAction(string $key): RouteAction
    {
        return RouteAction::make($key);
    }

    /**
     * Cria uma ação de URL
     */
    protected function urlAction(string $key): UrlAction
    {
        return UrlAction::make($key);
    }

    /**
     * Cria uma ação de callback
     */
    protected function callbackAction(string $key): CallbackAction
    {
        return CallbackAction::make($key);
    }

    /**
     * Configurações rápidas para ações comuns
     */
    
    /**
     * Ação de edição padrão
     */
    protected function editAction(string $route = null): RouteAction
    {
        $route = $route ?? $this->getActionRoutePrefix() . '.edit';
        
        return $this->routeAction('edit')
            ->label('Editar')
            ->route($route)
            ->edit();
    }

    /**
     * Ação de visualização padrão
     */
    protected function viewAction(string $route = null): RouteAction
    {
        $route = $route ?? $this->getActionRoutePrefix() . '.show';
        
        return $this->routeAction('view')
            ->label('Visualizar')
            ->route($route)
            ->view();
    }

    /**
     * Ação de exclusão padrão
     */
    protected function deleteAction(string $route = null): RouteAction
    {
        $route = $route ?? $this->getActionRoutePrefix() . '.destroy';
        
        return $this->routeAction('delete')
            ->label('Excluir')
            ->route($route)
            ->deleteMethod()
            ->delete();
    }

    /**
     * Ação de duplicação padrão
     */
    protected function duplicateAction(string $route = null): RouteAction
    {
        $route = $route ?? $this->getActionRoutePrefix() . '.duplicate';
        
        return $this->routeAction('duplicate')
            ->label('Duplicar')
            ->route($route)
            ->post()
            ->duplicate();
    }

    /**
     * Obtém prefixo de rotas (delegado para BelongsToRoutes)
     */
    protected function getActionRoutePrefix(): string
    {
        // Verificar se o trait BelongsToRoutes está sendo usado
        if (method_exists($this, 'getRoutePrefix')) {
            return $this->getRoutePrefix();
        }
        
        // Fallback: usar propriedade routePrefix se existir
        return property_exists($this, 'routePrefix') ? $this->routePrefix : '';
    }
} 
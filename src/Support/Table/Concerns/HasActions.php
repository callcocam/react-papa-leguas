<?php

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Illuminate\Database\Eloquent\Collection;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\Action;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\RouteAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\UrlAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\CallbackAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\ModalAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\BulkAction;

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
     * Executa uma ação em lote (para BulkAction)
     */
    public function executeBulkAction(string $key, Collection $items, array $context = []): mixed
    {
        $action = $this->getAction($key);
        
        if (!$action || !$action instanceof BulkAction) {
            return null;
        }
        
        $mergedContext = array_merge($this->actionContext, $context);
        
        // O callback de BulkAction espera uma coleção
        $callback = $action->getCallback();
        if (is_callable($callback)) {
            return call_user_func($callback, $items, $mergedContext);
        }

        return null;
    }

    /**
     * Obtém a configuração das ações de LINHA para um item específico.
     */
    public function getRowActions(object $item, array $context = []): array
    {
        $rowActions = [];
        $mergedContext = array_merge($this->actionContext, $context);

        foreach ($this->getVisibleActions($item, $mergedContext) as $action) {
            // Ignorar explicitamente ações em lote ao obter ações de linha
            if ($action instanceof BulkAction) {
                continue;
            }

            $actionArray = $action->toArray($item, $mergedContext);
            if (!empty($actionArray)) {
                $rowActions[] = $actionArray;
            }
        }

        return $rowActions;
    }

    /**
     * Obtém a configuração de TODAS as ações em LOTE.
     */
    public function getBulkActionsConfig(array $context = []): array
    {
        $bulkActions = [];
        $mergedContext = array_merge($this->actionContext, $context);

        // getVisibleBulkActions não depende de um item
        foreach ($this->getVisibleBulkActions($mergedContext) as $action) {
            // O item é nulo aqui, pois a ação em lote não pertence a uma linha
            $actionArray = $action->toArray(null, $mergedContext);
            if (!empty($actionArray)) {
                $bulkActions[] = $actionArray;
            }
        }

        return $bulkActions;
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
     * Métodos auxiliares para criar ações
     */

    /**
     * Cria uma ação de rota
     */
    protected function routeAction(string $key): RouteAction
    {
        return new RouteAction($key);
    }

    /**
     * Cria uma ação de URL
     */
    protected function urlAction(string $key): UrlAction
    {
        return new UrlAction($key);
    }

    /**
     * Cria uma ação de callback
     */
    protected function callbackAction(string $key): CallbackAction
    {
        return new CallbackAction($key);
    }

    /**
     * Cria uma ação de modal
     */
    protected function modalAction(string $key): ModalAction
    {
        return new ModalAction($key);
    }

    /**
     * Cria uma ação em lote
     */
    protected function bulkAction(string $key): BulkAction
    {
        return new BulkAction($key);
    }

    /**
     * Ação de Edição Padrão
     */
    protected function editAction(?string $route = null): RouteAction
    {
        $action = $this->routeAction('edit');
        $action->label('Editar')
            ->icon('Pencil')
            ->tooltip('Editar este registro')
            ->group('group-1');

        if ($route) {
            $action->route($route);
        }

        return $action;
    }

    /**
     * Ação de Visualização Padrão
     */
    protected function viewAction(?string $route = null): RouteAction
    {
        $action = $this->routeAction('view');
        $action->label('Visualizar')
            ->icon('Eye')
            ->tooltip('Visualizar este registro')
            ->group('group-1');

        if ($route) {
            $action->route($route);
        }

        return $action;
    }

    /**
     * Ação de Exclusão Padrão
     */
    protected function deleteAction(?string $route = null): RouteAction
    {
        $action = $this->routeAction('delete');
        $action->label('Excluir')
            ->icon('Trash2')
            ->variant('destructive')
            ->tooltip('Excluir este registro')
            ->group('group-2')
            ->requiresConfirmation(
                'Tem certeza que deseja excluir este registro?',
                'Confirmar Exclusão'
            );
        if ($route) {
            $action->route($route);
        }

        return $action;
    }

    /**
     * Ação de Duplicação Padrão
     */
    protected function duplicateAction(?string $route = null): RouteAction
    {
        $action = $this->routeAction('duplicate');
        $action->label('Duplicar')
            ->icon('Copy')
            ->tooltip('Duplicar este registro')
            ->group('group-2');

        if ($route) {
            $action->route($route);
        }
        return $action;
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

    /**
     * Obtém todas as ações em lote.
     */
    public function getBulkActions(): array
    {
        return array_filter($this->actions, fn (Action $action) => $action instanceof BulkAction);
    }

    /**
     * Obtém as ações em lote visíveis.
     * Ações em lote geralmente não dependem de um item de linha específico.
     */
    public function getVisibleBulkActions(array $context = []): array
    {
        $visibleActions = [];
        $mergedContext = array_merge($this->actionContext, $context);

        foreach ($this->getBulkActions() as $action) {
            if ($action->isVisible(null, $mergedContext)) {
                $visibleActions[$action->getKey()] = $action;
            }
        }
        
        return $visibleActions;
    }
} 
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\Actions\RelationBulkAction;

/**
 * Trait para gerenciar ações em massa de relacionamentos (React Frontend)
 */
trait HasRelationBulkActions
{
    /**
     * Ações em massa específicas para relacionamentos
     */
    protected array $relationBulkActions = [];

    /**
     * Configurações de interface React para bulk actions
     */
    protected array $relationBulkConfig = [
        'emptyState' => [
            'title' => 'Nenhum registro selecionado',
            'description' => 'Selecione registros para ver as ações disponíveis',
            'icon' => 'CheckSquare',
        ],
        'selectionInfo' => [
            'showCount' => true,
            'showSelectAll' => true,
            'showClearSelection' => true,
        ],
        'notifications' => [
            'position' => 'top-right',
            'duration' => 5000,
            'showProgress' => true,
        ],
    ];

    /**
     * Adicionar ação em massa de relacionamento
     */
    public function relationBulkAction(RelationBulkAction $action): static
    {
        $this->relationBulkActions[$action->getId()] = $action;
        return $this;
    }

    /**
     * Adicionar múltiplas ações em massa de relacionamento
     */
    public function relationBulkActions(array $actions): static
    {
        foreach ($actions as $action) {
            if ($action instanceof RelationBulkAction) {
                $this->relationBulkAction($action);
            }
        }
        return $this;
    }

    /**
     * Configurar interface React para bulk actions
     */
    public function relationBulkConfig(array $config): static
    {
        $this->relationBulkConfig = array_merge_recursive($this->relationBulkConfig, $config);
        return $this;
    }

    /**
     * Configurar estado vazio React
     */
    public function emptyState(string $title, string $description, string $icon = 'CheckSquare'): static
    {
        $this->relationBulkConfig['emptyState'] = [
            'title' => $title,
            'description' => $description,
            'icon' => $icon,
        ];
        return $this;
    }

    /**
     * Configurar informações de seleção React
     */
    public function selectionInfo(bool $showCount = true, bool $showSelectAll = true, bool $showClearSelection = true): static
    {
        $this->relationBulkConfig['selectionInfo'] = [
            'showCount' => $showCount,
            'showSelectAll' => $showSelectAll,
            'showClearSelection' => $showClearSelection,
        ];
        return $this;
    }

    /**
     * Configurar notificações React
     */
    public function notifications(string $position = 'top-right', int $duration = 5000, bool $showProgress = true): static
    {
        $this->relationBulkConfig['notifications'] = [
            'position' => $position,
            'duration' => $duration,
            'showProgress' => $showProgress,
        ];
        return $this;
    }

    /**
     * Obter ações em massa de relacionamento
     */
    public function getRelationBulkActions(): array
    {
        return $this->relationBulkActions;
    }

    /**
     * Obter ações em massa de relacionamento formatadas para React
     */
    public function getRelationBulkActionsForFrontend(): array
    {
        $actions = [];
        
        foreach ($this->relationBulkActions as $action) {
            if ($action->isVisible()) {
                $actions[] = $action->toArray();
            }
        }
        
        return $actions;
    }

    /**
     * Verificar se tem ações em massa de relacionamento
     */
    public function hasRelationBulkActions(): bool
    {
        return !empty($this->relationBulkActions);
    }

    /**
     * Remover ação em massa de relacionamento
     */
    public function removeRelationBulkAction(string $id): static
    {
        unset($this->relationBulkActions[$id]);
        return $this;
    }

    /**
     * Limpar todas as ações em massa de relacionamento
     */
    public function clearRelationBulkActions(): static
    {
        $this->relationBulkActions = [];
        return $this;
    }

    /**
     * Configurar ações em massa para relacionamento BelongsToMany
     */
    public function withBelongsToManyBulkActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        $this->relationBulkActions([
            RelationBulkAction::detachSelected($prefix ? "{$prefix}.bulk-detach" : null),
            RelationBulkAction::syncSelected($prefix ? "{$prefix}.bulk-sync" : null),
            RelationBulkAction::exportSelectedRelations($prefix ? "{$prefix}.bulk-export" : null),
        ]);
        
        $this->emptyState(
            'Nenhum relacionamento selecionado',
            'Selecione relacionamentos para ver as ações de sincronização e desanexação',
            'Link'
        );
        
        return $this;
    }

    /**
     * Configurar ações em massa para relacionamento HasMany
     */
    public function withHasManyBulkActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        $this->relationBulkActions([
            RelationBulkAction::duplicateSelectedRelations($prefix ? "{$prefix}.bulk-duplicate" : null),
            RelationBulkAction::exportSelectedRelations($prefix ? "{$prefix}.bulk-export" : null),
        ]);
        
        $this->emptyState(
            'Nenhum registro selecionado',
            'Selecione registros para ver as ações de duplicação e exportação',
            'Copy'
        );
        
        return $this;
    }

    /**
     * Configurar ações em massa para relacionamento MorphMany
     */
    public function withMorphManyBulkActions(string $routePrefix = null): static
    {
        return $this->withHasManyBulkActions($routePrefix);
    }

    /**
     * Configurar ações em massa avançadas
     */
    public function withAdvancedRelationBulkActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        $this->relationBulkActions([
            RelationBulkAction::moveSelectedToRelation($prefix ? "{$prefix}.bulk-move" : null),
            RelationBulkAction::reorderSelectedRelations($prefix ? "{$prefix}.bulk-reorder" : null),
            RelationBulkAction::attachMultiple($prefix ? "{$prefix}.bulk-attach" : null),
        ]);
        
        return $this;
    }

    /**
     * Configurar ações para campos pivot
     */
    public function withPivotFieldBulkActions(array $fields, string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        foreach ($fields as $field) {
            $this->relationBulkAction(
                RelationBulkAction::updatePivotField($field, $prefix ? "{$prefix}.bulk-update-pivot-{$field}" : null)
            );
        }
        
        return $this;
    }

    /**
     * Obter configuração completa para React
     */
    public function getRelationBulkConfigForFrontend(): array
    {
        if (!$this->hasRelationBulkActions()) {
            return [];
        }

        return [
            'enabled' => true,
            'actions' => $this->getRelationBulkActionsForFrontend(),
            'config' => $this->relationBulkConfig,
            'frontend' => [
                'component' => 'RelationBulkActionBar',
                'emptyState' => $this->relationBulkConfig['emptyState'],
                'selectionInfo' => $this->relationBulkConfig['selectionInfo'],
                'notifications' => $this->relationBulkConfig['notifications'],
            ],
        ];
    }

    /**
     * Estado vazio contextual baseado no tipo de relacionamento
     */
    public function setupContextualEmptyState(): static
    {
        if (!$this->isRelationManager()) {
            return $this;
        }

        $relationshipType = $this->getRelationshipType();
        $relationshipName = $this->getRelationshipName();
        $parentName = $this->getParentDisplayName();

        $emptyStates = [
            'hasMany' => [
                'title' => "Nenhum {$relationshipName} selecionado",
                'description' => "Selecione {$relationshipName} de {$parentName} para ver as ações disponíveis",
                'icon' => 'List',
            ],
            'belongsToMany' => [
                'title' => 'Nenhum relacionamento selecionado',
                'description' => "Selecione relacionamentos para sincronizar ou desanexar de {$parentName}",
                'icon' => 'Link',
            ],
            'morphMany' => [
                'title' => "Nenhum {$relationshipName} selecionado",
                'description' => "Selecione {$relationshipName} relacionados a {$parentName}",
                'icon' => 'Layers',
            ],
        ];

        if (isset($emptyStates[$relationshipType])) {
            $this->emptyState(
                $emptyStates[$relationshipType]['title'],
                $emptyStates[$relationshipType]['description'],
                $emptyStates[$relationshipType]['icon']
            );
        }

        return $this;
    }
} 
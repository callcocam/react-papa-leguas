<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\Actions\Action;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\HeaderAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\RowAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\BulkAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\RelationAction;

/**
 * Trait para gerenciar ações da tabela
 */
trait HasActions
{
    /**
     * Ações do cabeçalho
     */
    protected array $headerActions = [];

    /**
     * Ações das linhas
     */
    protected array $rowActions = [];

    /**
     * Adicionar ação do cabeçalho
     */
    public function headerAction(HeaderAction $action): static
    {
        $this->headerActions[$action->getId()] = $action;
        return $this;
    }

    /**
     * Adicionar múltiplas ações do cabeçalho
     */
    public function headerActions(array $actions): static
    {
        foreach ($actions as $action) {
            if ($action instanceof HeaderAction || $action instanceof RelationAction) {
                $this->headerActions[$action->getId()] = $action;
            }
        }
        return $this;
    }

    /**
     * Adicionar ação de linha
     */
    public function rowAction(RowAction $action): static
    {
        $this->rowActions[$action->getId()] = $action;
        return $this;
    }

    /**
     * Adicionar múltiplas ações de linha
     */
    public function rowActions(array $actions): static
    {
        foreach ($actions as $action) {
            if ($action instanceof RowAction) {
                $this->rowAction($action);
            }
        }
        return $this;
    }

    /**
     * Obter ações do cabeçalho
     */
    public function getHeaderActions(): array
    {
        return $this->headerActions;
    }

    /**
     * Obter ações das linhas
     */
    public function getRowActions(): array
    {
        return $this->rowActions;
    }

    /**
     * Obter ações do cabeçalho formatadas para o frontend
     */
    public function getHeaderActionsForFrontend(): array
    {
        $actions = [];
        
        foreach ($this->headerActions as $action) {
            if ($action->isVisible()) {
                $actions[] = $action->toArray();
            }
        }
        
        return $actions;
    }

    /**
     * Obter ações das linhas formatadas para o frontend
     */
    public function getRowActionsForFrontend(): array
    {
        $actions = [];
        
        foreach ($this->rowActions as $action) {
            $actions[] = $action->toArray();
        }
        
        // Ordenar por prioridade
        usort($actions, fn($a, $b) => $a['priority'] <=> $b['priority']);
        
        return $actions;
    }

    /**
     * Obter todas as ações formatadas para o frontend
     */
    public function getActionsForFrontend(): array
    {
        return [
            'header' => $this->getHeaderActionsForFrontend(),
            'row' => $this->getRowActionsForFrontend(),
        ];
    }

    /**
     * Verificar se tem ações do cabeçalho
     */
    public function hasHeaderActions(): bool
    {
        return !empty($this->headerActions);
    }

    /**
     * Verificar se tem ações de linha
     */
    public function hasRowActions(): bool
    {
        return !empty($this->rowActions);
    }

    /**
     * Remover ação do cabeçalho
     */
    public function removeHeaderAction(string $id): static
    {
        unset($this->headerActions[$id]);
        return $this;
    }

    /**
     * Remover ação de linha
     */
    public function removeRowAction(string $id): static
    {
        unset($this->rowActions[$id]);
        return $this;
    }

    /**
     * Limpar todas as ações do cabeçalho
     */
    public function clearHeaderActions(): static
    {
        $this->headerActions = [];
        return $this;
    }

    /**
     * Limpar todas as ações de linha
     */
    public function clearRowActions(): static
    {
        $this->rowActions = [];
        return $this;
    }

    /**
     * Configurar ações padrão de CRUD
     */
    public function withCrudActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        // Ações do cabeçalho
        $this->headerActions([
            HeaderAction::create($prefix ? "{$prefix}.create" : null),
            HeaderAction::export($prefix ? "{$prefix}.export" : null),
        ]);
        
        // Ações de linha
        $this->rowActions([
            RowAction::view($prefix ? "{$prefix}.show" : null),
            RowAction::edit($prefix ? "{$prefix}.edit" : null),
            RowAction::deleteAction($prefix ? "{$prefix}.destroy" : null),
        ]);
        
        return $this;
    }

    /**
     * Configurar ações de status
     */
    public function withStatusActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        $this->rowActions([
            RowAction::activate($prefix ? "{$prefix}.activate" : null),
            RowAction::deactivate($prefix ? "{$prefix}.deactivate" : null),
        ]);
        
        return $this;
    }

    /**
     * Configurar ações de arquivo
     */
    public function withArchiveActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        $this->rowActions([
            RowAction::archive($prefix ? "{$prefix}.archive" : null),
            RowAction::restore($prefix ? "{$prefix}.restore" : null),
        ]);
        
        return $this;
    }

    /**
     * Configurar ações para RelationManager
     */
    public function withRelationActions(string $routePrefix = null, array $permissions = []): static
    {
        $prefix = $routePrefix ?? '';
        
        // Actions do cabeçalho para RelationManager - convertemos para HeaderAction
        if ($permissions['canCreate'] ?? true) {
            $this->headerAction(
                HeaderAction::make('create-related')
                    ->label('Criar Novo')
                    ->icon('Plus')
                    ->color('primary')
                    ->route($prefix ? "{$prefix}.create-related" : 'create-related')
            );
        }
        
        if ($permissions['canAttach'] ?? true) {
            $this->headerAction(
                HeaderAction::make('attach-existing')
                    ->label('Anexar Existente')
                    ->icon('Link')
                    ->color('secondary')
                    ->route($prefix ? "{$prefix}.attach-existing" : 'attach-existing')
            );
        }
        
        // Actions de linha para RelationManager - convertemos para RowAction
        $this->rowAction(
            RowAction::make('view-related')
                ->label('Ver')
                ->icon('Eye')
                ->color('secondary')
                ->variant('ghost')
                ->tooltip('Visualizar registro')
                ->route($prefix ? "{$prefix}.view-related" : 'view-related')
        );
        
        $this->rowAction(
            RowAction::make('edit-related')
                ->label('Editar')
                ->icon('Edit')
                ->color('primary')
                ->variant('ghost')
                ->tooltip('Editar registro')
                ->route($prefix ? "{$prefix}.edit-related" : 'edit-related')
        );
        
        if ($permissions['canDetach'] ?? true) {
            $this->rowAction(
                RowAction::make('detach-record')
                    ->label('Desanexar')
                    ->icon('Unlink')
                    ->color('warning')
                    ->variant('ghost')
                    ->tooltip('Desanexar este registro')
                    ->route($prefix ? "{$prefix}.detach-record" : 'detach-record')
                    ->requiresConfirmation()
                    ->confirmationTitle('Confirmar desanexação')
                    ->confirmationDescription('Deseja desanexar este registro? O registro não será excluído, apenas a relação será removida.')
            );
        }
        
        return $this;
    }

    /**
     * Configurar ações avançadas para RelationManager
     */
    public function withAdvancedRelationActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        // Header actions convertidas
        $this->headerAction(
            HeaderAction::make('sync-relation')
                ->label('Sincronizar')
                ->icon('RefreshCw')
                ->color('primary')
                ->route($prefix ? "{$prefix}.sync-relation" : 'sync-relation')
        );
        
        $this->headerAction(
            HeaderAction::make('export-related')
                ->label('Exportar')
                ->icon('Download')
                ->color('secondary')
                ->route($prefix ? "{$prefix}.export-related" : 'export-related')
                ->openInNewTab()
        );
        
        // Row actions convertidas
        $this->rowAction(
            RowAction::make('duplicate-related')
                ->label('Duplicar')
                ->icon('Copy')
                ->color('secondary')
                ->variant('ghost')
                ->tooltip('Duplicar registro')
                ->route($prefix ? "{$prefix}.duplicate-related" : 'duplicate-related')
                ->post()
        );
        
        return $this;
    }

    /**
     * Configurar ações para relacionamento BelongsToMany
     */
    public function withBelongsToManyActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        return $this->withRelationActions($prefix, [
                'canCreate' => true,
                'canAttach' => true,
                'canDetach' => true,
            ])
            ->headerAction(
                HeaderAction::make('sync')
                    ->label('Sincronizar')
                    ->icon('RefreshCw')
                    ->color('primary')
                    ->route($prefix ? "{$prefix}.sync" : 'sync')
            );
    }

    /**
     * Configurar ações para relacionamento HasMany
     */
    public function withHasManyActions(string $routePrefix = null): static
    {
        return $this->withRelationActions($routePrefix, [
            'canCreate' => true,
            'canAttach' => false, // HasMany não permite anexar
            'canDetach' => false, // HasMany não permite desanexar
        ]);
    }

    /**
     * Configurar ações para relacionamento MorphMany
     */
    public function withMorphManyActions(string $routePrefix = null): static
    {
        return $this->withHasManyActions($routePrefix);
    }
} 
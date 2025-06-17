<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Callcocam\ReactPapaLeguas\Support\Table\Actions\BulkAction;

/**
 * Trait para gerenciar ações em massa da tabela
 */
trait HasBulkActions
{
    /**
     * Ações em massa
     */
    protected array $bulkActions = [];

    /**
     * Se seleção em massa está habilitada
     */
    protected bool $bulkSelectionEnabled = true;

    /**
     * Se permite selecionar todos os registros
     */
    protected bool $selectAllEnabled = true;

    /**
     * Limite máximo de seleções
     */
    protected ?int $maxBulkSelections = null;

    /**
     * Adicionar ação em massa
     */
    public function bulkAction(BulkAction $action): static
    {
        $this->bulkActions[$action->getId()] = $action;
        return $this;
    }

    /**
     * Adicionar múltiplas ações em massa
     */
    public function bulkActions(array $actions): static
    {
        foreach ($actions as $action) {
            if ($action instanceof BulkAction) {
                $this->bulkAction($action);
            }
        }
        return $this;
    }

    /**
     * Habilitar/desabilitar seleção em massa
     */
    public function bulkSelection(bool $enabled = true): static
    {
        $this->bulkSelectionEnabled = $enabled;
        return $this;
    }

    /**
     * Desabilitar seleção em massa
     */
    public function disableBulkSelection(): static
    {
        return $this->bulkSelection(false);
    }

    /**
     * Habilitar/desabilitar selecionar todos
     */
    public function selectAll(bool $enabled = true): static
    {
        $this->selectAllEnabled = $enabled;
        return $this;
    }

    /**
     * Desabilitar selecionar todos
     */
    public function disableSelectAll(): static
    {
        return $this->selectAll(false);
    }

    /**
     * Definir limite máximo de seleções
     */
    public function maxBulkSelections(int $max): static
    {
        $this->maxBulkSelections = $max;
        return $this;
    }

    /**
     * Obter ações em massa
     */
    public function getBulkActions(): array
    {
        return $this->bulkActions;
    }

    /**
     * Obter ações em massa formatadas para o frontend
     */
    public function getBulkActionsForFrontend(): array
    {
        $actions = [];
        
        foreach ($this->bulkActions as $action) {
            if ($action->isVisible()) {
                $actions[] = $action->toArray();
            }
        }
        
        return $actions;
    }

    /**
     * Verificar se tem ações em massa
     */
    public function hasBulkActions(): bool
    {
        return !empty($this->bulkActions) && $this->bulkSelectionEnabled;
    }

    /**
     * Verificar se seleção em massa está habilitada
     */
    public function isBulkSelectionEnabled(): bool
    {
        return $this->bulkSelectionEnabled;
    }

    /**
     * Verificar se selecionar todos está habilitado
     */
    public function isSelectAllEnabled(): bool
    {
        return $this->selectAllEnabled;
    }

    /**
     * Obter limite máximo de seleções
     */
    public function getMaxBulkSelections(): ?int
    {
        return $this->maxBulkSelections;
    }

    /**
     * Remover ação em massa
     */
    public function removeBulkAction(string $id): static
    {
        unset($this->bulkActions[$id]);
        return $this;
    }

    /**
     * Limpar todas as ações em massa
     */
    public function clearBulkActions(): static
    {
        $this->bulkActions = [];
        return $this;
    }

    /**
     * Configurar ações em massa padrão
     */
    public function withDefaultBulkActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        $this->bulkActions([
            BulkAction::deleteSelected($prefix ? "{$prefix}.bulk-delete" : null),
            BulkAction::exportSelected($prefix ? "{$prefix}.bulk-export" : null),
        ]);
        
        return $this;
    }

    /**
     * Configurar ações em massa de status
     */
    public function withBulkStatusActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        $this->bulkActions([
            BulkAction::activateSelected($prefix ? "{$prefix}.bulk-activate" : null),
            BulkAction::deactivateSelected($prefix ? "{$prefix}.bulk-deactivate" : null),
        ]);
        
        return $this;
    }

    /**
     * Configurar ações em massa de arquivo
     */
    public function withBulkArchiveActions(string $routePrefix = null): static
    {
        $prefix = $routePrefix ?? '';
        
        $this->bulkActions([
            BulkAction::archiveSelected($prefix ? "{$prefix}.bulk-archive" : null),
        ]);
        
        return $this;
    }

    /**
     * Obter configuração de seleção em massa para o frontend
     */
    public function getBulkSelectionConfig(): array
    {
        return [
            'enabled' => $this->bulkSelectionEnabled,
            'selectAllEnabled' => $this->selectAllEnabled,
            'maxSelections' => $this->maxBulkSelections,
            'actions' => $this->getBulkActionsForFrontend(),
        ];
    }
} 
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

/**
 * Ações específicas para RelationManager
 */
class RelationAction extends Action
{
    /**
     * Tipo da ação de relacionamento
     */
    protected string $relationType = 'default';

    /**
     * Se abre modal para seleção
     */
    protected bool $opensModal = false;

    /**
     * Configurações do modal
     */
    protected array $modalConfig = [];

    /**
     * Definir tipo da relação
     */
    public function relationType(string $type): static
    {
        $this->relationType = $type;
        return $this;
    }

    /**
     * Configurar para abrir modal
     */
    public function opensModal(bool $opensModal = true, array $config = []): static
    {
        $this->opensModal = $opensModal;
        $this->modalConfig = $config;
        return $this;
    }

    /**
     * Ação para criar novo registro relacionado
     */
    public static function createRelated(string $route = null): static
    {
        return static::make('create-related')
            ->label('Criar Novo')
            ->icon('Plus')
            ->color('primary')
            ->route($route ?? 'create-related')
            ->relationType('create')
            ->opensModal(true, [
                'title' => 'Criar Novo Registro',
                'size' => 'lg',
            ]);
    }

    /**
     * Ação para anexar registros existentes
     */
    public static function attachExisting(string $route = null): static
    {
        return static::make('attach-existing')
            ->label('Anexar Existente')
            ->icon('Link')
            ->color('secondary')
            ->route($route ?? 'attach-existing')
            ->relationType('attach')
            ->opensModal(true, [
                'title' => 'Selecionar Registros para Anexar',
                'size' => 'xl',
                'searchable' => true,
                'selectable' => 'multiple',
            ]);
    }

    /**
     * Ação para desanexar registro
     */
    public static function detachRecord(string $route = null): static
    {
        return static::make('detach-record')
            ->label('Desanexar')
            ->icon('Unlink')
            ->color('warning')
            ->variant('ghost')
            ->tooltip('Desanexar este registro')
            ->route($route ?? 'detach-record')
            ->relationType('detach')
            ->requiresConfirmation()
            ->confirmationTitle('Confirmar desanexação')
            ->confirmationDescription('Deseja desanexar este registro? O registro não será excluído, apenas a relação será removida.');
    }

    /**
     * Ação para editar registro relacionado
     */
    public static function editRelated(string $route = null): static
    {
        return static::make('edit-related')
            ->label('Editar')
            ->icon('Edit')
            ->color('primary')
            ->variant('ghost')
            ->tooltip('Editar registro')
            ->route($route ?? 'edit-related')
            ->relationType('edit')
            ->opensModal(true, [
                'title' => 'Editar Registro',
                'size' => 'lg',
            ]);
    }

    /**
     * Ação para visualizar registro relacionado
     */
    public static function viewRelated(string $route = null): static
    {
        return static::make('view-related')
            ->label('Ver')
            ->icon('Eye')
            ->color('secondary')
            ->variant('ghost')
            ->tooltip('Visualizar registro')
            ->route($route ?? 'view-related')
            ->relationType('view')
            ->opensModal(true, [
                'title' => 'Visualizar Registro',
                'size' => 'lg',
                'readonly' => true,
            ]);
    }

    /**
     * Ação para duplicar registro relacionado
     */
    public static function duplicateRelated(string $route = null): static
    {
        return static::make('duplicate-related')
            ->label('Duplicar')
            ->icon('Copy')
            ->color('secondary')
            ->variant('ghost')
            ->tooltip('Duplicar registro')
            ->route($route ?? 'duplicate-related')
            ->relationType('duplicate')
            ->post()
            ->requiresConfirmation()
            ->confirmationTitle('Duplicar registro')
            ->confirmationDescription('Deseja criar uma cópia deste registro?');
    }

    /**
     * Ação para mover registro para outra relação
     */
    public static function moveToRelation(string $route = null): static
    {
        return static::make('move-to-relation')
            ->label('Mover')
            ->icon('Move')
            ->color('secondary')
            ->variant('ghost')
            ->tooltip('Mover para outra relação')
            ->route($route ?? 'move-to-relation')
            ->relationType('move')
            ->opensModal(true, [
                'title' => 'Mover Registro',
                'size' => 'md',
                'searchable' => true,
            ])
            ->requiresConfirmation()
            ->confirmationTitle('Mover registro')
            ->confirmationDescription('Deseja mover este registro para outra relação?');
    }

    /**
     * Ação para sincronizar relacionamentos (belongsToMany)
     */
    public static function syncRelation(string $route = null): static
    {
        return static::make('sync-relation')
            ->label('Sincronizar')
            ->icon('RefreshCw')
            ->color('primary')
            ->route($route ?? 'sync-relation')
            ->relationType('sync')
            ->opensModal(true, [
                'title' => 'Sincronizar Relacionamentos',
                'size' => 'xl',
                'searchable' => true,
                'selectable' => 'multiple',
            ])
            ->requiresConfirmation()
            ->confirmationTitle('Sincronizar relacionamentos')
            ->confirmationDescription('Esta ação irá substituir todos os relacionamentos atuais pelos selecionados.');
    }

    /**
     * Ação para ordenar registros relacionados
     */
    public static function reorderRelated(string $route = null): static
    {
        return static::make('reorder-related')
            ->label('Reordenar')
            ->icon('ArrowUpDown')
            ->color('secondary')
            ->route($route ?? 'reorder-related')
            ->relationType('reorder')
            ->opensModal(true, [
                'title' => 'Reordenar Registros',
                'size' => 'lg',
                'draggable' => true,
            ]);
    }

    /**
     * Ação para exportar relacionamentos
     */
    public static function exportRelated(string $route = null): static
    {
        return static::make('export-related')
            ->label('Exportar')
            ->icon('Download')
            ->color('secondary')
            ->route($route ?? 'export-related')
            ->relationType('export')
            ->openInNewTab();
    }

    /**
     * Ação para importar relacionamentos
     */
    public static function importRelated(string $route = null): static
    {
        return static::make('import-related')
            ->label('Importar')
            ->icon('Upload')
            ->color('secondary')
            ->route($route ?? 'import-related')
            ->relationType('import')
            ->opensModal(true, [
                'title' => 'Importar Relacionamentos',
                'size' => 'md',
                'uploadable' => true,
            ]);
    }

    /**
     * Ação para limpar todos os relacionamentos
     */
    public static function clearAllRelations(string $route = null): static
    {
        return static::make('clear-all-relations')
            ->label('Limpar Todos')
            ->icon('Trash2')
            ->color('destructive')
            ->route($route ?? 'clear-all-relations')
            ->relationType('clear')
            ->method('DELETE')
            ->requiresConfirmation()
            ->confirmationTitle('Limpar todos os relacionamentos')
            ->confirmationDescription('Esta ação irá remover TODOS os relacionamentos. Esta ação não pode ser desfeita.');
    }

    /**
     * Ação personalizada para relacionamento
     */
    public static function customRelation(string $id, string $label, string $relationType = 'custom'): static
    {
        return static::make($id)
            ->label($label)
            ->icon('Settings')
            ->color('secondary')
            ->variant('ghost')
            ->relationType($relationType);
    }

    /**
     * Converter para array
     */
    public function toArray($record = null): array
    {
        $data = parent::toArray($record);
        
        return array_merge($data, [
            'relationType' => $this->relationType,
            'opensModal' => $this->opensModal,
            'modalConfig' => $this->modalConfig,
        ]);
    }
} 
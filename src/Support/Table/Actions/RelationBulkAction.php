<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

/**
 * Ações em massa específicas para RelationManager (React Frontend)
 */
class RelationBulkAction extends BulkAction
{
    /**
     * Tipo da ação de relacionamento em massa
     */
    protected string $relationType = 'bulk';

    /**
     * Configurações específicas para React
     */
    protected array $reactConfig = [
        'modal' => [
            'size' => 'lg',
            'title' => '',
            'description' => '',
            'confirmButton' => 'Confirmar',
            'cancelButton' => 'Cancelar',
        ],
        'toast' => [
            'success' => '',
            'error' => '',
        ],
        'loading' => [
            'text' => 'Processando...',
            'spinner' => true,
        ],
    ];

    /**
     * Definir tipo da relação
     */
    public function relationType(string $type): static
    {
        $this->relationType = $type;
        return $this;
    }

    /**
     * Configurar interface React
     */
    public function reactConfig(array $config): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, $config);
        return $this;
    }

    /**
     * Configurar modal React
     */
    public function modal(array $config): static
    {
        $this->reactConfig['modal'] = array_merge($this->reactConfig['modal'], $config);
        return $this;
    }

    /**
     * Configurar toast notifications React
     */
    public function toast(string $success, string $error = null): static
    {
        $this->reactConfig['toast']['success'] = $success;
        if ($error) {
            $this->reactConfig['toast']['error'] = $error;
        }
        return $this;
    }

    /**
     * Configurar loading state React
     */
    public function loading(string $text, bool $spinner = true): static
    {
        $this->reactConfig['loading'] = [
            'text' => $text,
            'spinner' => $spinner,
        ];
        return $this;
    }

    /**
     * Desanexar registros selecionados
     */
    public static function detachSelected(string $route = null): static
    {
        return static::make('bulk-detach')
            ->label('Desanexar Selecionados')
            ->icon('Unlink')
            ->color('warning')
            ->route($route ?? 'bulk-detach')
            ->method('DELETE')
            ->relationType('detach')
            ->requiresConfirmation()
            ->confirmationTitle('Desanexar Relacionamentos')
            ->confirmationMessageTemplate('Deseja desanexar {count} registro(s) selecionado(s)? Os registros não serão excluídos, apenas as relações serão removidas.')
            ->modal([
                'title' => 'Confirmar Desanexação em Massa',
                'description' => 'Esta ação irá remover as relações selecionadas.',
                'confirmButton' => 'Desanexar',
                'cancelButton' => 'Cancelar',
            ])
            ->toast(
                'Relacionamentos desanexados com sucesso!',
                'Erro ao desanexar relacionamentos.'
            )
            ->loading('Desanexando relacionamentos...');
    }

    /**
     * Sincronizar relacionamentos selecionados (BelongsToMany)
     */
    public static function syncSelected(string $route = null): static
    {
        return static::make('bulk-sync')
            ->label('Sincronizar Selecionados')
            ->icon('RefreshCw')
            ->color('primary')
            ->route($route ?? 'bulk-sync')
            ->method('PUT')
            ->relationType('sync')
            ->requiresConfirmation()
            ->confirmationTitle('Sincronizar Relacionamentos')
            ->confirmationMessageTemplate('Deseja sincronizar mantendo apenas os {count} registro(s) selecionado(s)? Todos os outros relacionamentos serão removidos.')
            ->modal([
                'title' => 'Sincronizar Relacionamentos',
                'description' => 'Esta ação irá manter apenas os relacionamentos selecionados.',
                'confirmButton' => 'Sincronizar',
                'cancelButton' => 'Cancelar',
            ])
            ->toast(
                'Relacionamentos sincronizados com sucesso!',
                'Erro ao sincronizar relacionamentos.'
            )
            ->loading('Sincronizando relacionamentos...');
    }

    /**
     * Mover registros selecionados para outro relacionamento
     */
    public static function moveSelectedToRelation(string $route = null): static
    {
        return static::make('bulk-move-relation')
            ->label('Mover para Outro Relacionamento')
            ->icon('Move')
            ->color('secondary')
            ->route($route ?? 'bulk-move-relation')
            ->method('PUT')
            ->relationType('move')
            ->requiresConfirmation()
            ->confirmationTitle('Mover Relacionamentos')
            ->confirmationMessageTemplate('Deseja mover {count} registro(s) selecionado(s) para outro relacionamento?')
            ->modal([
                'title' => 'Mover para Outro Relacionamento',
                'description' => 'Selecione o destino para mover os relacionamentos.',
                'confirmButton' => 'Mover',
                'cancelButton' => 'Cancelar',
                'size' => 'xl',
                'searchable' => true,
            ])
            ->toast(
                'Relacionamentos movidos com sucesso!',
                'Erro ao mover relacionamentos.'
            )
            ->loading('Movendo relacionamentos...');
    }

    /**
     * Duplicar relacionamentos selecionados
     */
    public static function duplicateSelectedRelations(string $route = null): static
    {
        return static::make('bulk-duplicate-relations')
            ->label('Duplicar Relacionamentos')
            ->icon('Copy')
            ->color('secondary')
            ->route($route ?? 'bulk-duplicate-relations')
            ->method('POST')
            ->relationType('duplicate')
            ->requiresConfirmation()
            ->confirmationTitle('Duplicar Relacionamentos')
            ->confirmationMessageTemplate('Deseja duplicar {count} relacionamento(s) selecionado(s)?')
            ->modal([
                'title' => 'Duplicar Relacionamentos',
                'description' => 'Esta ação irá criar cópias dos relacionamentos selecionados.',
                'confirmButton' => 'Duplicar',
                'cancelButton' => 'Cancelar',
            ])
            ->toast(
                'Relacionamentos duplicados com sucesso!',
                'Erro ao duplicar relacionamentos.'
            )
            ->loading('Duplicando relacionamentos...');
    }

    /**
     * Atualizar campo pivot em massa (BelongsToMany)
     */
    public static function updatePivotField(string $field, string $route = null): static
    {
        return static::make("bulk-update-pivot-{$field}")
            ->label("Atualizar {$field} (Pivot)")
            ->icon('Edit')
            ->color('primary')
            ->route($route ?? "bulk-update-pivot-{$field}")
            ->method('PUT')
            ->relationType('updatePivot')
            ->requiresConfirmation()
            ->confirmationTitle("Atualizar Campo Pivot: {$field}")
            ->confirmationMessageTemplate("Deseja atualizar o campo {$field} de {count} relacionamento(s) selecionado(s)?")
            ->modal([
                'title' => "Atualizar Campo Pivot: {$field}",
                'description' => 'Digite o novo valor para o campo pivot.',
                'confirmButton' => 'Atualizar',
                'cancelButton' => 'Cancelar',
                'hasInput' => true,
                'inputLabel' => "Novo valor para {$field}",
                'inputType' => 'text',
            ])
            ->toast(
                "Campo {$field} atualizado com sucesso!",
                "Erro ao atualizar campo {$field}."
            )
            ->loading("Atualizando campo {$field}...");
    }

    /**
     * Exportar relacionamentos selecionados
     */
    public static function exportSelectedRelations(string $route = null): static
    {
        return static::make('bulk-export-relations')
            ->label('Exportar Relacionamentos')
            ->icon('Download')
            ->color('secondary')
            ->route($route ?? 'bulk-export-relations')
            ->method('POST')
            ->relationType('export')
            ->openInNewTab()
            ->toast(
                'Exportação iniciada! O download começará em breve.',
                'Erro ao iniciar exportação.'
            )
            ->loading('Preparando exportação...');
    }

    /**
     * Anexar novos registros em massa
     */
    public static function attachMultiple(string $route = null): static
    {
        return static::make('bulk-attach-multiple')
            ->label('Anexar Múltiplos')
            ->icon('Link')
            ->color('primary')
            ->route($route ?? 'bulk-attach-multiple')
            ->method('POST')
            ->relationType('attach')
            ->modal([
                'title' => 'Anexar Múltiplos Registros',
                'description' => 'Selecione os registros que deseja anexar.',
                'confirmButton' => 'Anexar',
                'cancelButton' => 'Cancelar',
                'size' => 'xl',
                'searchable' => true,
                'selectable' => 'multiple',
                'pagination' => true,
            ])
            ->toast(
                'Registros anexados com sucesso!',
                'Erro ao anexar registros.'
            )
            ->loading('Anexando registros...');
    }

    /**
     * Reordenar relacionamentos selecionados
     */
    public static function reorderSelectedRelations(string $route = null): static
    {
        return static::make('bulk-reorder-relations')
            ->label('Reordenar Selecionados')
            ->icon('ArrowUpDown')
            ->color('secondary')
            ->route($route ?? 'bulk-reorder-relations')
            ->method('PUT')
            ->relationType('reorder')
            ->modal([
                'title' => 'Reordenar Relacionamentos',
                'description' => 'Arraste os itens para reordená-los.',
                'confirmButton' => 'Salvar Ordem',
                'cancelButton' => 'Cancelar',
                'size' => 'lg',
                'draggable' => true,
                'sortable' => true,
            ])
            ->toast(
                'Ordem dos relacionamentos atualizada!',
                'Erro ao reordenar relacionamentos.'
            )
            ->loading('Salvando nova ordem...');
    }

    /**
     * Ação personalizada em massa para relacionamentos
     */
    public static function customRelationBulk(string $id, string $label, string $relationType = 'custom'): static
    {
        return static::make($id)
            ->label($label)
            ->icon('Settings')
            ->color('secondary')
            ->relationType($relationType)
            ->modal([
                'title' => $label,
                'description' => 'Ação personalizada para relacionamentos.',
                'confirmButton' => 'Executar',
                'cancelButton' => 'Cancelar',
            ])
            ->loading('Executando ação...');
    }

    /**
     * Converter para array (com configurações React)
     */
    public function toArray($record = null): array
    {
        $data = parent::toArray($record);
        
        return array_merge($data, [
            'relationType' => $this->relationType,
            'reactConfig' => $this->reactConfig,
            'frontend' => [
                'component' => 'RelationBulkAction',
                'modal' => $this->reactConfig['modal'],
                'toast' => $this->reactConfig['toast'],
                'loading' => $this->reactConfig['loading'],
            ],
        ]);
    }
} 
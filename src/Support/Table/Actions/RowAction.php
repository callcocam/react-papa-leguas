<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

/**
 * Ação de linha da tabela
 */
class RowAction extends Action
{
    /**
     * Se a ação aparece sempre ou apenas no hover
     */
    protected bool $alwaysVisible = false;

    /**
     * Se a ação aparece no menu dropdown
     */
    protected bool $inDropdown = false;

    /**
     * Prioridade da ação (para ordenação)
     */
    protected int $priority = 0;

    /**
     * Definir se sempre visível
     */
    public function alwaysVisible(bool $alwaysVisible = true): static
    {
        $this->alwaysVisible = $alwaysVisible;
        return $this;
    }

    /**
     * Colocar no dropdown
     */
    public function inDropdown(bool $inDropdown = true): static
    {
        $this->inDropdown = $inDropdown;
        return $this;
    }

    /**
     * Definir prioridade
     */
    public function priority(int $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Ação de visualizar
     */
    public static function view(string $route = null): static
    {
        return static::make('view')
            ->label('Ver')
            ->icon('Eye')
            ->color('secondary')
            ->variant('ghost')
            ->tooltip('Visualizar registro')
            ->route($route ?? 'show')
            ->priority(1);
    }

    /**
     * Ação de editar
     */
    public static function edit(string $route = null): static
    {
        return static::make('edit')
            ->label('Editar')
            ->icon('Edit')
            ->color('primary')
            ->variant('ghost')
            ->tooltip('Editar registro')
            ->route($route ?? 'edit')
            ->priority(2);
    }

    /**
     * Ação de excluir
     */
    public static function deleteAction(string $route = null): static
    {
        return static::make('delete')
            ->label('Excluir')
            ->icon('Trash2')
            ->color('destructive')
            ->variant('ghost')
            ->tooltip('Excluir registro')
            ->route($route ?? 'destroy')
            ->method('DELETE')
            ->requiresConfirmation()
            ->confirmationTitle('Confirmar exclusão')
            ->confirmationDescription('Esta ação não pode ser desfeita. O registro será excluído permanentemente.')
            ->priority(10);
    }

    /**
     * Ação de duplicar
     */
    public static function duplicate(string $route = null): static
    {
        return static::make('duplicate')
            ->label('Duplicar')
            ->icon('Copy')
            ->color('secondary')
            ->variant('ghost')
            ->tooltip('Duplicar registro')
            ->route($route ?? 'duplicate')
            ->post()
            ->priority(3);
    }

    /**
     * Ação de ativar
     */
    public static function activate(string $route = null): static
    {
        return static::make('activate')
            ->label('Ativar')
            ->icon('CheckCircle')
            ->color('success')
            ->variant('ghost')
            ->tooltip('Ativar registro')
            ->route($route ?? 'activate')
            ->put()
            ->visible(fn($record) => !$record->active ?? true)
            ->priority(4);
    }

    /**
     * Ação de desativar
     */
    public static function deactivate(string $route = null): static
    {
        return static::make('deactivate')
            ->label('Desativar')
            ->icon('XCircle')
            ->color('warning')
            ->variant('ghost')
            ->tooltip('Desativar registro')
            ->route($route ?? 'deactivate')
            ->put()
            ->visible(fn($record) => $record->active ?? false)
            ->priority(5);
    }

    /**
     * Ação de arquivar
     */
    public static function archive(string $route = null): static
    {
        return static::make('archive')
            ->label('Arquivar')
            ->icon('Archive')
            ->color('secondary')
            ->variant('ghost')
            ->tooltip('Arquivar registro')
            ->route($route ?? 'archive')
            ->put()
            ->requiresConfirmation()
            ->confirmationTitle('Arquivar registro')
            ->confirmationDescription('O registro será movido para o arquivo.')
            ->priority(6);
    }

    /**
     * Ação de restaurar
     */
    public static function restore(string $route = null): static
    {
        return static::make('restore')
            ->label('Restaurar')
            ->icon('RotateCcw')
            ->color('success')
            ->variant('ghost')
            ->tooltip('Restaurar registro')
            ->route($route ?? 'restore')
            ->put()
            ->visible(fn($record) => $record->trashed() ?? false)
            ->priority(7);
    }

    /**
     * Ação de download
     */
    public static function download(string $route = null): static
    {
        return static::make('download')
            ->label('Download')
            ->icon('Download')
            ->color('secondary')
            ->variant('ghost')
            ->tooltip('Fazer download')
            ->route($route ?? 'download')
            ->openInNewTab()
            ->priority(8);
    }

    /**
     * Ação personalizada
     */
    public static function custom(string $id, string $label, string $icon = null): static
    {
        return static::make($id)
            ->label($label)
            ->icon($icon ?? 'Settings')
            ->color('secondary')
            ->variant('ghost')
            ->priority(9);
    }

    /**
     * Converter para array
     */
    public function toArray($record = null): array
    {
        $data = parent::toArray($record);
        
        return array_merge($data, [
            'type' => 'row',
            'alwaysVisible' => $this->alwaysVisible,
            'inDropdown' => $this->inDropdown,
            'priority' => $this->priority,
        ]);
    }
} 
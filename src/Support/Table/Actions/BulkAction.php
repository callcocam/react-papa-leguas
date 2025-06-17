<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

/**
 * Ação em massa da tabela
 */
class BulkAction extends Action
{
    /**
     * Campo que será enviado com os IDs selecionados
     */
    protected string $idsField = 'ids';

    /**
     * Limite mínimo de seleções
     */
    protected int $minSelections = 1;

    /**
     * Limite máximo de seleções
     */
    protected ?int $maxSelections = null;

    /**
     * Se requer seleção de todos os registros
     */
    protected bool $requiresAllSelected = false;

    /**
     * Mensagem de confirmação personalizada baseada na quantidade
     */
    protected ?string $confirmationMessageTemplate = null;

    /**
     * Definir campo dos IDs
     */
    public function idsField(string $field): static
    {
        $this->idsField = $field;
        return $this;
    }

    /**
     * Definir limite mínimo de seleções
     */
    public function minSelections(int $min): static
    {
        $this->minSelections = $min;
        return $this;
    }

    /**
     * Definir limite máximo de seleções
     */
    public function maxSelections(int $max): static
    {
        $this->maxSelections = $max;
        return $this;
    }

    /**
     * Requer que todos estejam selecionados
     */
    public function requiresAllSelected(bool $requiresAllSelected = true): static
    {
        $this->requiresAllSelected = $requiresAllSelected;
        return $this;
    }

    /**
     * Template de mensagem de confirmação
     */
    public function confirmationMessageTemplate(string $template): static
    {
        $this->confirmationMessageTemplate = $template;
        return $this;
    }

    /**
     * Ação de excluir em massa
     */
    public static function deleteSelected(string $route = null): static
    {
        return static::make('bulk-delete')
            ->label('Excluir Selecionados')
            ->icon('Trash2')
            ->color('destructive')
            ->route($route ?? 'bulk-delete')
            ->method('DELETE')
            ->requiresConfirmation()
            ->confirmationTitle('Confirmar exclusão em massa')
            ->confirmationMessageTemplate('Deseja excluir {count} registro(s) selecionado(s)? Esta ação não pode ser desfeita.');
    }

    /**
     * Ação de ativar em massa
     */
    public static function activateSelected(string $route = null): static
    {
        return static::make('bulk-activate')
            ->label('Ativar Selecionados')
            ->icon('CheckCircle')
            ->color('success')
            ->route($route ?? 'bulk-activate')
            ->method('PUT')
            ->requiresConfirmation()
            ->confirmationTitle('Ativar registros')
            ->confirmationMessageTemplate('Deseja ativar {count} registro(s) selecionado(s)?');
    }

    /**
     * Ação de desativar em massa
     */
    public static function deactivateSelected(string $route = null): static
    {
        return static::make('bulk-deactivate')
            ->label('Desativar Selecionados')
            ->icon('XCircle')
            ->color('warning')
            ->route($route ?? 'bulk-deactivate')
            ->method('PUT')
            ->requiresConfirmation()
            ->confirmationTitle('Desativar registros')
            ->confirmationMessageTemplate('Deseja desativar {count} registro(s) selecionado(s)?');
    }

    /**
     * Ação de arquivar em massa
     */
    public static function archiveSelected(string $route = null): static
    {
        return static::make('bulk-archive')
            ->label('Arquivar Selecionados')
            ->icon('Archive')
            ->color('secondary')
            ->route($route ?? 'bulk-archive')
            ->method('PUT')
            ->requiresConfirmation()
            ->confirmationTitle('Arquivar registros')
            ->confirmationMessageTemplate('Deseja arquivar {count} registro(s) selecionado(s)?');
    }

    /**
     * Ação de exportar selecionados
     */
    public static function exportSelected(string $route = null): static
    {
        return static::make('bulk-export')
            ->label('Exportar Selecionados')
            ->icon('Download')
            ->color('secondary')
            ->route($route ?? 'bulk-export')
            ->method('POST')
            ->openInNewTab();
    }

    /**
     * Ação de atualizar campo em massa
     */
    public static function updateField(string $field, string $route = null): static
    {
        return static::make("bulk-update-{$field}")
            ->label("Atualizar {$field}")
            ->icon('Edit')
            ->color('primary')
            ->route($route ?? "bulk-update-{$field}")
            ->method('PUT')
            ->requiresConfirmation()
            ->confirmationTitle("Atualizar {$field}")
            ->confirmationMessageTemplate("Deseja atualizar o campo {$field} de {count} registro(s) selecionado(s)?");
    }

    /**
     * Ação de mover para categoria
     */
    public static function moveToCategory(string $route = null): static
    {
        return static::make('bulk-move-category')
            ->label('Mover para Categoria')
            ->icon('FolderOpen')
            ->color('secondary')
            ->route($route ?? 'bulk-move-category')
            ->method('PUT')
            ->requiresConfirmation()
            ->confirmationTitle('Mover para categoria')
            ->confirmationMessageTemplate('Deseja mover {count} registro(s) selecionado(s) para a nova categoria?');
    }

    /**
     * Ação de duplicar selecionados
     */
    public static function duplicateSelected(string $route = null): static
    {
        return static::make('bulk-duplicate')
            ->label('Duplicar Selecionados')
            ->icon('Copy')
            ->color('secondary')
            ->route($route ?? 'bulk-duplicate')
            ->method('POST')
            ->requiresConfirmation()
            ->confirmationTitle('Duplicar registros')
            ->confirmationMessageTemplate('Deseja duplicar {count} registro(s) selecionado(s)?');
    }

    /**
     * Ação personalizada em massa
     */
    public static function custom(string $id, string $label, string $route = null): static
    {
        return static::make($id)
            ->label($label)
            ->icon('Settings')
            ->color('secondary')
            ->route($route ?? $id)
            ->method('POST');
    }

    /**
     * Converter para array
     */
    public function toArray($record = null): array
    {
        $data = parent::toArray($record);
        
        return array_merge($data, [
            'type' => 'bulk',
            'idsField' => $this->idsField,
            'minSelections' => $this->minSelections,
            'maxSelections' => $this->maxSelections,
            'requiresAllSelected' => $this->requiresAllSelected,
            'confirmationMessageTemplate' => $this->confirmationMessageTemplate,
        ]);
    }
} 
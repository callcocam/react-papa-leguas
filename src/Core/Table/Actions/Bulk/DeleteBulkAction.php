<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Bulk;

class DeleteBulkAction extends BulkAction
{
    public function __construct()
    {
        parent::__construct('bulk-delete');
        
        $this->label('Excluir selecionados')
            ->icon('trash-2')
            ->color('destructive')
            ->description('Remove permanentemente os registros selecionados')
            ->confirmationTitle('Confirmar exclusão')
            ->confirmationDescription('Esta ação não pode ser desfeita. Os registros selecionados serão excluídos permanentemente.');
    }

    public static function make(string $name = 'bulk-delete'): static
    {
        return new static();
    }
}

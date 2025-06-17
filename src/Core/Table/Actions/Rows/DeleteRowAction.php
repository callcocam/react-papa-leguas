<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows;

class DeleteRowAction extends RowAction
{
    public function __construct()
    {
        parent::__construct('delete');
        
        $this->label('Excluir')
            ->icon('trash-2')
            ->color('destructive')
            ->description('Excluir este registro')
            ->requiresConfirmation()
            ->confirmationTitle('Confirmar exclusão')
            ->confirmationDescription('Esta ação não pode ser desfeita. O registro será excluído permanentemente.');
    }

    public static function make(string $name = 'delete'): static
    {
        return new static();
    }
}

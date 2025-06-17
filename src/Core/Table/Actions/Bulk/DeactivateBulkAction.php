<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Bulk;

class DeactivateBulkAction extends BulkAction
{
    public function __construct()
    {
        parent::__construct('bulk-deactivate');
        
        $this->label('Desativar selecionados')
            ->icon('x-circle')
            ->color('warning')
            ->description('Desativa todos os registros selecionados')
            ->confirmationTitle('Confirmar desativação')
            ->confirmationDescription('Os registros selecionados serão desativados.');
    }

    public static function make(string $name = 'bulk-deactivate'): static
    {
        return new static();
    }
}

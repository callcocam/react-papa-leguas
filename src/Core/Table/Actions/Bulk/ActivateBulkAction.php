<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Bulk;

class ActivateBulkAction extends BulkAction
{
    public function __construct()
    {
        parent::__construct('bulk-activate');
        
        $this->label('Ativar selecionados')
            ->icon('check-circle')
            ->color('success')
            ->description('Ativa todos os registros selecionados')
            ->confirmationTitle('Confirmar ativação')
            ->confirmationDescription('Os registros selecionados serão ativados.');
    }

    public static function make(string $name = 'bulk-activate'): static
    {
        return new static();
    }
}

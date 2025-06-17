<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows;

class ViewRowAction extends RowAction
{
    public function __construct()
    {
        parent::__construct('view');
        
        $this->label('Visualizar')
            ->icon('eye')
            ->color('secondary')
            ->description('Visualizar detalhes do registro');
    }

    public static function make(string $name = 'view'): static
    {
        return new static();
    }
}

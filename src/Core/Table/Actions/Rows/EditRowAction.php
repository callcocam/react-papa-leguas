<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows;

class EditRowAction extends RowAction
{
    public function __construct()
    {
        parent::__construct('edit');
        
        $this->label('Editar')
            ->icon('edit')
            ->color('primary')
            ->description('Editar este registro');
    }

    public static function make(string $name = 'edit'): static
    {
        return new static();
    }
}

<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Rows;

class CloneRowAction extends RowAction
{
    public function __construct()
    {
        parent::__construct('clone');
        
        $this->label('Duplicar')
            ->icon('copy')
            ->color('secondary')
            ->description('Criar uma cópia deste registro');
    }

    public static function make(string $name = 'clone'): static
    {
        return new static();
    }
}

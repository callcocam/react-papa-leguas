<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Header;

class RefreshHeaderAction extends HeaderAction
{
    public function __construct()
    {
        parent::__construct('refresh');
        
        $this->label('Atualizar')
            ->icon('refresh-cw')
            ->color('secondary')
            ->description('Recarregar dados da tabela');
    }

    public static function make(string $name = 'refresh'): static
    {
        return new static();
    }
}

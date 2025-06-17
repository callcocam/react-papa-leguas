<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Header;

class CreateHeaderAction extends HeaderAction
{
    public function __construct()
    {
        parent::__construct('create');
        
        $this->label('Criar novo')
            ->icon('plus')
            ->color('primary')
            ->description('Adicionar um novo registro');
    }

    public static function make(string $name = 'create'): static
    {
        return new static();
    }
}

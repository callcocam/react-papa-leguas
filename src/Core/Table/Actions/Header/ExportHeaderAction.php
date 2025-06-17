<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Core\Table\Actions\Header;

class ExportHeaderAction extends HeaderAction
{
    public function __construct()
    {
        parent::__construct('export');
        
        $this->label('Exportar')
            ->icon('download')
            ->color('secondary')
            ->description('Exportar dados da tabela');
    }

    public static function make(string $name = 'export'): static
    {
        return new static();
    }
}

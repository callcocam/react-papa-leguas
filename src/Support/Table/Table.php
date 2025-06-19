<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table;

use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasColumns;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasFilters;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasActions;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\InteractsWithTable;

/**
 * Classe Table moderna com suporte completo a formatação avançada,
 * relacionamentos, badges dinâmicos, filtros avançados, ações dinâmicas e componentes React customizados.
 */
abstract class Table 
{
    use HasColumns, HasFilters, HasActions, InteractsWithTable;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Chamar setUp se existir
        if (method_exists($this, 'setUp')) {
            $this->setUp();
        }
        
        $this->boot();
    }

    /**
     * Método setUp para configuração da tabela
     * Deve ser implementado pelas classes filhas
     */
    protected function setUp(): void
    {
        // Implementação padrão vazia
        // Classes filhas devem sobrescrever este método
    }

    /**
     * Define as ações da tabela
     * Pode ser sobrescrito pelas classes filhas
     */
    protected function actions(): array
    {
        return [];
    }
} 
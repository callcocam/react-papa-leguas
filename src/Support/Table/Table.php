<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table;

use Callcocam\ReactPapaLeguas\Support\Table\Concerns\HasColumns;
use Callcocam\ReactPapaLeguas\Support\Table\Concerns\InteractsWithTable;

/**
 * Classe Table moderna com suporte completo a formatação avançada,
 * relacionamentos, badges dinâmicos e componentes React customizados.
 */
abstract class Table 
{
    use HasColumns, InteractsWithTable;

    protected $filters = [];
    protected $actions = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->boot();
    }

    /**
     * Define os filtros da tabela
     * Pode ser sobrescrito pelas classes filhas
     */
    protected function filters(): array
    {
        return [];
    }

    /**
     * Define as ações da tabela
     * Pode ser sobrescrito pelas classes filhas
     */
    protected function actions(): array
    {
        return [];
    }

    /**
     * Boot method específico da Table
     */
    protected function bootTable()
    {
        $this->filters = $this->filters();
        $this->actions = $this->actions();
    }
} 
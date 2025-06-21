<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Admin;

use Callcocam\ReactPapaLeguas\Models\Workflowable;
use Callcocam\ReactPapaLeguas\Tables\WorkflowableTable;

/**
 * Controller para gestão de Workflowables
 * 
 * Monitora todos os itens que estão passando por workflows,
 * independente do tipo (tickets, leads, vendas, etc).
 */
class WorkflowableController extends AdminController
{
    /**
     * Especifica o modelo usado por este controller
     */
    public function getModelClass(): ?string
    {
        return Workflowable::class;
    }

    /**
     * Especifica a tabela usada por este controller
     */
    public function getTableClass(): ?string
    {
        return WorkflowableTable::class;
    }
} 
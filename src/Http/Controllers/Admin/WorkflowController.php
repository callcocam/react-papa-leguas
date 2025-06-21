<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Admin;

use Callcocam\ReactPapaLeguas\Models\Workflow;
use Callcocam\ReactPapaLeguas\Tables\WorkflowTable;

/**
 * Controller para gestão de Workflows
 * 
 * Gerencia os processos de negócio que definem fluxos Kanban
 * para diferentes entidades (tickets, leads, vendas, etc).
 */
class WorkflowController extends AdminController
{
    /**
     * Especifica o modelo usado por este controller
     */
    public function getModelClass(): ?string
    {
        return Workflow::class;
    }

    /**
     * Especifica a tabela usada por este controller
     */
    public function getTableClass(): ?string
    {
        return WorkflowTable::class;
    }
} 
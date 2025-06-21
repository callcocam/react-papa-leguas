<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Admin;

use Callcocam\ReactPapaLeguas\Models\WorkflowTemplate;
use Callcocam\ReactPapaLeguas\Tables\WorkflowTemplateTable;

/**
 * Controller para gestão de Templates de Workflow
 * 
 * Gerencia as etapas/colunas individuais de cada workflow,
 * definindo as regras e configurações das colunas Kanban.
 */
class WorkflowTemplateController extends AdminController
{
    /**
     * Especifica o modelo usado por este controller
     */
    public function getModelClass(): ?string
    {
        return WorkflowTemplate::class;
    }

    /**
     * Especifica a tabela usada por este controller
     */
    public function getTableClass(): ?string
    {
        return WorkflowTemplateTable::class;
    }
} 
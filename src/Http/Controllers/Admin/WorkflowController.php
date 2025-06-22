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
 * Controller para gestão de Workflows - Sistema Simplificado
 * 
 * Gerencia os processos de negócio simplificados que definem fluxos Kanban
 * para diferentes entidades (tickets, leads, vendas, etc).
 * 
 * Configurações visuais agora estão nos WorkflowTemplates.
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

    public function getViewCreate(): string
    {
        return 'crud/workflows/create';
    }

    public function getViewEdit(): string
    {
        return 'crud/workflows/edit';
    }

    public function getViewShow(): string
    {
        return 'crud/workflows/show';
    }

    /**
     * Configura relacionamentos específicos do WorkflowController com eager loading contextual
     * 
     * Relacionamentos do modelo Workflow (simplificado):
     * - BelongsTo: user (criador), tenant (proprietário)
     * - HasMany: templates (etapas), activeTemplates, workflowables (entidades usando o workflow)
     * 
     * Campos simplificados do Workflow: name, slug, description, status
     * Configurações visuais estão nos templates: color, icon, category, etc.
     * 
     * @return void
     */
    protected function configureControllerSpecificRelations(): void
    {
        // INDEX: Relacionamentos básicos para listagem
        // Carrega apenas user e tenant para mostrar informações essenciais na tabela
        $this->addEagerLoadToContext('index', 'user:id,name,email');   // Criador do workflow
        $this->addEagerLoadToContext('index', 'tenant:id,name');       // Tenant proprietário

        // SHOW: Visualização completa com todos os relacionamentos necessários
        // Inclui todos os templates e workflowables para análise completa
        $this->addEagerLoadToContext('show', 'user:id,name,email');    // Criador completo
        $this->addEagerLoadToContext('show', 'tenant:id,name');        // Tenant proprietário
        $this->addEagerLoadToContext('show', 'templates:id,workflow_id,name,slug,description,color,icon,sort_order,status'); // Templates/etapas com configurações visuais
        $this->addEagerLoadToContext('show', 'activeTemplates:id,workflow_id,name,slug,description,color,icon,sort_order'); // Templates publicados
        $this->addEagerLoadToContext('show', 'workflowables:id,workflow_id,workflowable_type,workflowable_id,current_template_id,status,started_at,completed_at'); // Entidades

        // EDIT: Relacionamentos necessários para formulários de edição
        // Inclui user e tenant para contexto, templates para configuração
        $this->addEagerLoadToContext('edit', 'user:id,name,email');    // Criador
        $this->addEagerLoadToContext('edit', 'tenant:id,name');        // Tenant proprietário  
        $this->addEagerLoadToContext('edit', 'templates:id,workflow_id,name,slug,sort_order,status'); // Templates para configuração

        // CREATE: Relacionamentos para formulários de criação
        // Apenas relacionamentos básicos necessários para o formulário
        $this->addEagerLoadToContext('create', 'user:id,name,email');  // Para contexto do usuário
        $this->addEagerLoadToContext('create', 'tenant:id,name');      // Para contexto do tenant
    }
} 
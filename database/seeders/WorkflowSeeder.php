<?php

namespace Callcocam\ReactPapaLeguas\Database\Seeders;

use Callcocam\ReactPapaLeguas\Models\Workflow;
use Callcocam\ReactPapaLeguas\Models\WorkflowTemplate;
use Callcocam\ReactPapaLeguas\Enums\BaseStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Seeder para Workflows - Sistema de Processos Genérico
 * 
 * Cria workflows para diferentes categorias de negócio com
 * templates configurados para uso em sistemas Kanban.
 */
class WorkflowSeeder extends Seeder
{
    /**
     * Executar o seeder
     */
    public function run(): void
    {
        $this->createSupportWorkflows();
        $this->createSalesWorkflows();
        $this->createHRWorkflows();
        $this->createDevelopmentWorkflows();
        $this->createFinanceWorkflows();
    }

    /**
     * Criar workflows de suporte técnico
     */
    protected function createSupportWorkflows(): void
    {
        // Workflow: Suporte Técnico Padrão
        $supportWorkflow = Workflow::create([
            'name' => 'Suporte Técnico',
            'slug' => 'suporte-tecnico',
            'description' => 'Fluxo padrão para atendimento de tickets de suporte técnico com SLA controlado',
            'category' => 'support',
            'tags' => ['suporte', 'tecnico', 'tickets', 'sla'],
            'color' => '#3b82f6',
            'icon' => 'Headphones',
            'estimated_duration_days' => 3,
            'is_required_by_default' => true,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
            'status' => BaseStatus::Published->value,
            'user_id' => $this->getFirstUserId(),
            'tenant_id' => $this->getFirstTenantId(),
        ]);

        // Templates do Suporte Técnico
        $this->createTemplatesForWorkflow($supportWorkflow, [
            [
                'name' => 'Novo',
                'slug' => 'novo',
                'description' => 'Ticket recém criado, aguardando primeira análise',
                'instructions' => 'Analise o ticket e classifique a prioridade. Atribua a um técnico se necessário.',
                'category' => 'initial',
                'color' => '#6b7280',
                'icon' => 'Plus',
                'max_items' => null,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Em Análise',
                'slug' => 'em-analise',
                'description' => 'Ticket sendo analisado pela equipe técnica',
                'instructions' => 'Reproduza o problema e documente os passos. Estime o tempo de resolução.',
                'category' => 'progress',
                'color' => '#f59e0b',
                'icon' => 'Search',
                'max_items' => 10,
                'auto_assign' => true,
                'requires_approval' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Em Desenvolvimento',
                'slug' => 'em-desenvolvimento',
                'description' => 'Solução sendo desenvolvida ou implementada',
                'instructions' => 'Desenvolva a correção ou solução. Teste em ambiente de desenvolvimento.',
                'category' => 'progress',
                'color' => '#8b5cf6',
                'icon' => 'Code',
                'max_items' => 5,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Aguardando Cliente',
                'slug' => 'aguardando-cliente',
                'description' => 'Aguardando informações ou ação do cliente',
                'instructions' => 'Mantenha contato com o cliente. Escale se não houver resposta em 48h.',
                'category' => 'blocked',
                'color' => '#ef4444',
                'icon' => 'Clock',
                'max_items' => null,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Resolvido',
                'slug' => 'resolvido',
                'description' => 'Problema resolvido, aguardando confirmação do cliente',
                'instructions' => 'Solicite feedback do cliente. Feche o ticket se confirmado.',
                'category' => 'final',
                'color' => '#10b981',
                'icon' => 'CheckCircle',
                'max_items' => null,
                'auto_assign' => false,
                'requires_approval' => true,
                'sort_order' => 5,
            ],
        ]);

        // Workflow: Suporte Crítico
        $criticalWorkflow = Workflow::create([
            'name' => 'Suporte Crítico',
            'slug' => 'suporte-critico',
            'description' => 'Fluxo acelerado para problemas críticos que afetam produção',
            'category' => 'support',
            'tags' => ['critico', 'emergencia', 'producao'],
            'color' => '#dc2626',
            'icon' => 'AlertTriangle',
            'estimated_duration_days' => 1,
            'is_required_by_default' => false,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 2,
            'status' => BaseStatus::Published->value,
            'user_id' => $this->getFirstUserId(),
            'tenant_id' => $this->getFirstTenantId(),
        ]);

        $this->createTemplatesForWorkflow($criticalWorkflow, [
            [
                'name' => 'Emergência',
                'slug' => 'emergencia',
                'description' => 'Problema crítico reportado',
                'category' => 'initial',
                'color' => '#dc2626',
                'icon' => 'AlertTriangle',
                'max_items' => 3,
                'auto_assign' => true,
                'requires_approval' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Investigação',
                'slug' => 'investigacao',
                'description' => 'Investigação em andamento',
                'category' => 'progress',
                'color' => '#f59e0b',
                'icon' => 'Search',
                'max_items' => 2,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Correção',
                'slug' => 'correcao',
                'description' => 'Aplicando correção',
                'category' => 'progress',
                'color' => '#8b5cf6',
                'icon' => 'Tool',
                'max_items' => 1,
                'auto_assign' => false,
                'requires_approval' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Resolvido',
                'slug' => 'resolvido',
                'description' => 'Problema resolvido',
                'category' => 'final',
                'color' => '#10b981',
                'icon' => 'CheckCircle',
                'max_items' => null,
                'auto_assign' => false,
                'requires_approval' => true,
                'sort_order' => 4,
            ],
        ]);
    }

    /**
     * Criar workflows de vendas
     */
    protected function createSalesWorkflows(): void
    {
        $salesWorkflow = Workflow::create([
            'name' => 'Pipeline de Vendas',
            'slug' => 'pipeline-vendas',
            'description' => 'Processo completo de vendas desde lead até fechamento',
            'category' => 'sales',
            'tags' => ['vendas', 'leads', 'pipeline', 'crm'],
            'color' => '#10b981',
            'icon' => 'TrendingUp',
            'estimated_duration_days' => 30,
            'is_required_by_default' => true,
            'is_active' => true,
            'is_featured' => true,
            'sort_order' => 1,
            'status' => BaseStatus::Published->value,
            'user_id' => $this->getFirstUserId(),
            'tenant_id' => $this->getFirstTenantId(),
        ]);

        $this->createTemplatesForWorkflow($salesWorkflow, [
            [
                'name' => 'Lead',
                'slug' => 'lead',
                'description' => 'Novo lead identificado',
                'category' => 'initial',
                'color' => '#6b7280',
                'icon' => 'User',
                'max_items' => null,
                'auto_assign' => true,
                'requires_approval' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Qualificação',
                'slug' => 'qualificacao',
                'description' => 'Qualificando o lead',
                'category' => 'progress',
                'color' => '#f59e0b',
                'icon' => 'UserCheck',
                'max_items' => 20,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Proposta',
                'slug' => 'proposta',
                'description' => 'Elaborando proposta comercial',
                'category' => 'progress',
                'color' => '#8b5cf6',
                'icon' => 'FileText',
                'max_items' => 10,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Negociação',
                'slug' => 'negociacao',
                'description' => 'Negociando condições',
                'category' => 'progress',
                'color' => '#f97316',
                'icon' => 'MessageSquare',
                'max_items' => 5,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Fechamento',
                'slug' => 'fechamento',
                'description' => 'Venda fechada com sucesso',
                'category' => 'final',
                'color' => '#10b981',
                'icon' => 'CheckCircle',
                'max_items' => null,
                'auto_assign' => false,
                'requires_approval' => true,
                'sort_order' => 5,
            ],
        ]);
    }

    /**
     * Criar workflows de RH
     */
    protected function createHRWorkflows(): void
    {
        $hrWorkflow = Workflow::create([
            'name' => 'Processo Seletivo',
            'slug' => 'processo-seletivo',
            'description' => 'Fluxo completo de recrutamento e seleção de candidatos',
            'category' => 'hr',
            'tags' => ['rh', 'recrutamento', 'selecao', 'candidatos'],
            'color' => '#8b5cf6',
            'icon' => 'Users',
            'estimated_duration_days' => 21,
            'is_required_by_default' => true,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
            'status' => BaseStatus::Published->value,
            'user_id' => $this->getFirstUserId(),
            'tenant_id' => $this->getFirstTenantId(),
        ]);

        $this->createTemplatesForWorkflow($hrWorkflow, [
            [
                'name' => 'Triagem',
                'slug' => 'triagem',
                'description' => 'Análise inicial do currículo',
                'category' => 'initial',
                'color' => '#6b7280',
                'icon' => 'Filter',
                'max_items' => null,
                'auto_assign' => true,
                'requires_approval' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Entrevista RH',
                'slug' => 'entrevista-rh',
                'description' => 'Entrevista com RH',
                'category' => 'progress',
                'color' => '#f59e0b',
                'icon' => 'MessageCircle',
                'max_items' => 15,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Teste Técnico',
                'slug' => 'teste-tecnico',
                'description' => 'Avaliação técnica',
                'category' => 'progress',
                'color' => '#8b5cf6',
                'icon' => 'Code',
                'max_items' => 10,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Entrevista Final',
                'slug' => 'entrevista-final',
                'description' => 'Entrevista com gestor',
                'category' => 'approval',
                'color' => '#f97316',
                'icon' => 'UserCheck',
                'max_items' => 5,
                'auto_assign' => false,
                'requires_approval' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Contratado',
                'slug' => 'contratado',
                'description' => 'Candidato aprovado',
                'category' => 'final',
                'color' => '#10b981',
                'icon' => 'CheckCircle',
                'max_items' => null,
                'auto_assign' => false,
                'requires_approval' => true,
                'sort_order' => 5,
            ],
        ]);
    }

    /**
     * Criar workflows de desenvolvimento
     */
    protected function createDevelopmentWorkflows(): void
    {
        $devWorkflow = Workflow::create([
            'name' => 'Desenvolvimento Ágil',
            'slug' => 'desenvolvimento-agil',
            'description' => 'Fluxo de desenvolvimento de features seguindo metodologia ágil',
            'category' => 'development',
            'tags' => ['desenvolvimento', 'agil', 'features', 'sprint'],
            'color' => '#6366f1',
            'icon' => 'Code',
            'estimated_duration_days' => 14,
            'is_required_by_default' => true,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
            'status' => BaseStatus::Published->value,
            'user_id' => $this->getFirstUserId(),
            'tenant_id' => $this->getFirstTenantId(),
        ]);

        $this->createTemplatesForWorkflow($devWorkflow, [
            [
                'name' => 'Backlog',
                'slug' => 'backlog',
                'description' => 'Item no backlog do produto',
                'category' => 'initial',
                'color' => '#6b7280',
                'icon' => 'List',
                'max_items' => null,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Em Desenvolvimento',
                'slug' => 'em-desenvolvimento',
                'description' => 'Feature sendo desenvolvida',
                'category' => 'progress',
                'color' => '#f59e0b',
                'icon' => 'Code',
                'max_items' => 8,
                'auto_assign' => true,
                'requires_approval' => false,
                'sort_order' => 2,
            ],
            [
                'name' => 'Code Review',
                'slug' => 'code-review',
                'description' => 'Revisão de código',
                'category' => 'review',
                'color' => '#8b5cf6',
                'icon' => 'Eye',
                'max_items' => 5,
                'auto_assign' => false,
                'requires_approval' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Teste',
                'slug' => 'teste',
                'description' => 'Testando a feature',
                'category' => 'review',
                'color' => '#f97316',
                'icon' => 'TestTube',
                'max_items' => 3,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 4,
            ],
            [
                'name' => 'Pronto',
                'slug' => 'pronto',
                'description' => 'Feature concluída',
                'category' => 'final',
                'color' => '#10b981',
                'icon' => 'CheckCircle',
                'max_items' => null,
                'auto_assign' => false,
                'requires_approval' => true,
                'sort_order' => 5,
            ],
        ]);
    }

    /**
     * Criar workflows financeiros
     */
    protected function createFinanceWorkflows(): void
    {
        $financeWorkflow = Workflow::create([
            'name' => 'Aprovação de Despesas',
            'slug' => 'aprovacao-despesas',
            'description' => 'Processo de aprovação e pagamento de despesas empresariais',
            'category' => 'finance',
            'tags' => ['financeiro', 'despesas', 'aprovacao', 'pagamento'],
            'color' => '#f59e0b',
            'icon' => 'DollarSign',
            'estimated_duration_days' => 7,
            'is_required_by_default' => true,
            'is_active' => true,
            'is_featured' => false,
            'sort_order' => 1,
            'status' => BaseStatus::Published->value,
            'user_id' => $this->getFirstUserId(),
            'tenant_id' => $this->getFirstTenantId(),
        ]);

        $this->createTemplatesForWorkflow($financeWorkflow, [
            [
                'name' => 'Solicitado',
                'slug' => 'solicitado',
                'description' => 'Despesa solicitada pelo funcionário',
                'category' => 'initial',
                'color' => '#6b7280',
                'icon' => 'Plus',
                'max_items' => null,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 1,
            ],
            [
                'name' => 'Análise Gestor',
                'slug' => 'analise-gestor',
                'description' => 'Análise pelo gestor direto',
                'category' => 'approval',
                'color' => '#f59e0b',
                'icon' => 'User',
                'max_items' => 20,
                'auto_assign' => true,
                'requires_approval' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Análise Financeiro',
                'slug' => 'analise-financeiro',
                'description' => 'Análise pelo departamento financeiro',
                'category' => 'approval',
                'color' => '#8b5cf6',
                'icon' => 'Calculator',
                'max_items' => 10,
                'auto_assign' => false,
                'requires_approval' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Aprovado',
                'slug' => 'aprovado',
                'description' => 'Despesa aprovada para pagamento',
                'category' => 'final',
                'color' => '#10b981',
                'icon' => 'CheckCircle',
                'max_items' => null,
                'auto_assign' => false,
                'requires_approval' => false,
                'sort_order' => 4,
            ],
        ]);
    }

    /**
     * Criar templates para um workflow
     */
    protected function createTemplatesForWorkflow(Workflow $workflow, array $templatesData): void
    {
        foreach ($templatesData as $templateData) {
            WorkflowTemplate::create(array_merge($templateData, [
                'workflow_id' => $workflow->id,
                'status' => BaseStatus::Published->value,
                'user_id' => $this->getFirstUserId(),
                'tenant_id' => $this->getFirstTenantId(),
            ]));
        }
    }

    /**
     * Obter ID do primeiro usuário
     */
    protected function getFirstUserId(): ?string
    {
        return \App\Models\User::first()?->id;
    }

    /**
     * Obter ID do primeiro tenant
     */
    protected function getFirstTenantId(): ?string
    {
        // Verificar se existe tabela tenants
        if (!\Schema::hasTable('tenants')) {
            return null;
        }

        return \DB::table('tenants')->first()?->id;
    }
} 
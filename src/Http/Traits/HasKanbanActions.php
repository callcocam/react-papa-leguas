<?php

namespace Callcocam\ReactPapaLeguas\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Callcocam\ReactPapaLeguas\Models\Workflow;
use Callcocam\ReactPapaLeguas\Models\WorkflowTemplate;
use Callcocam\ReactPapaLeguas\Models\Workflowable;
use Exception;
use Illuminate\Validation\ValidationException;

/**
 * Trait HasKanbanActions
 * 
 * Adiciona funcionalidades completas de Kanban a qualquer controller CRUD.
 * Sistema genérico que funciona com qualquer modelo e workflow real.
 * 
 * Integra com:
 * - Workflow: Define o processo de negócio
 * - WorkflowTemplate: Define as colunas/etapas do Kanban
 * - Workflowable: Relaciona entidades com workflows
 * 
 * @package Callcocam\ReactPapaLeguas\Http\Traits
 */
trait HasKanbanActions
{
    /**
     * Mover card entre colunas do Kanban
     */
    public function moveCard(Request $request)
    {
        try {
            // 🎯 Validar dados de entrada
            $validated = $request->validate([
                'card_id' => 'required',
                'from_template_id' => 'required|string',
                'to_template_id' => 'required|string',
                'workflow_slug' => 'nullable|string',
                'item' => 'nullable|array',
                'workflow_data' => 'nullable|array',
            ]);

            $cardId = $validated['card_id'];
            $fromTemplateId = $validated['from_template_id'];
            $toTemplateId = $validated['to_template_id'];
            $workflowSlug = $validated['workflow_slug'] ?? $this->detectWorkflowSlug();

            // 🔍 Buscar o item
            $item = $this->model()::find($cardId);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item não encontrado',
                ], 404);
            }

            // 🔍 Buscar templates do workflow
            $fromTemplate = WorkflowTemplate::where('slug', $fromTemplateId)->first();
            $toTemplate = WorkflowTemplate::where('slug', $toTemplateId)->first();

            if (!$fromTemplate || !$toTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template de workflow não encontrado',
                ], 404);
            }

            // ✅ Validar transição
            if (!$this->validateKanbanTransition($item, $fromTemplate, $toTemplate, $workflowSlug)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transição não permitida pelo workflow',
                    'errors' => [
                        'transition' => ['Movimento não permitido entre estes templates']
                    ]
                ], 422);
            }

            // 🔄 Executar movimento em transação
            DB::beginTransaction();

            try {
                // Buscar ou criar workflowable
                $workflowable = $item->currentWorkflow ?? $this->createWorkflowable($item, $workflowSlug);
                
                // Mover para novo template
                $workflowable->moveToTemplate($toTemplate);
                
                // Aplicar mudanças específicas do modelo
                $this->onKanbanCardMoved($item, $fromTemplate, $toTemplate, $validated);

                // Log da movimentação
                $this->logKanbanMovement($item, $fromTemplate, $toTemplate, $workflowSlug);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => "Card movido de '{$fromTemplate->name}' para '{$toTemplate->name}' com sucesso",
                    'data' => [
                        'id' => $item->id,
                        'current_template_id' => $toTemplate->id,
                        'current_step' => $workflowable->current_step,
                        'workflow_slug' => $workflowSlug,
                        'template_slug' => $toTemplate->slug,
                        'template_name' => $toTemplate->name,
                        'moved_at' => now()->toISOString(),
                        'previous_template' => $fromTemplate->slug,
                        'next_templates' => $toTemplate->getNextTemplateIds(),
                    ]
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao mover card no Kanban', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor ao mover card',
            ], 500);
        }
    }

    /**
     * Obter estatísticas do Kanban
     */
    public function getKanbanStats(Request $request)
    {
        try {
            $workflowSlug = $request->input('workflow_slug', $this->detectWorkflowSlug());
            $filters = $request->except(['workflow_slug']);

            // Buscar workflow
            $workflow = Workflow::where('slug', $workflowSlug)->first();
            if (!$workflow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workflow não encontrado',
                ], 404);
            }

            // Buscar templates do workflow
            $templates = WorkflowTemplate::where('workflow_id', $workflow->id)
                ->orderBy('sort_order')
                ->get();

            // Contar itens por template
            $query = $this->model()::whereHas('currentWorkflow', function ($q) use ($workflow) {
                $q->where('workflow_id', $workflow->id);
            });

            // Aplicar filtros específicos
            $query = $this->applyKanbanFilters($query, $filters);

            $totalItems = $query->count();
            $statsByTemplate = [];

            foreach ($templates as $template) {
                $count = $query->clone()
                    ->whereHas('currentWorkflow', function ($q) use ($template) {
                        $q->where('current_template_id', $template->id);
                    })
                    ->count();

                $statsByTemplate[] = [
                    'id' => $template->id,
                    'slug' => $template->slug,
                    'name' => $template->name,
                    'count' => $count,
                    'percentage' => $totalItems > 0 ? round(($count / $totalItems) * 100, 2) : 0,
                    'color' => $template->color ?? '#6b7280',
                    'icon' => $template->icon ?? 'circle',
                    'order' => $template->sort_order,
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $totalItems,
                    'workflow' => [
                        'id' => $workflow->id,
                        'name' => $workflow->name,
                        'slug' => $workflow->slug,
                    ],
                    'by_template' => $statsByTemplate,
                    'updated_at' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar estatísticas do Kanban', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
            ], 500);
        }
    }

    /**
     * Obter colunas/templates do workflow
     */
    public function getKanbanColumns(Request $request)
    {
        try {
            $workflowSlug = $request->input('workflow_slug', $this->detectWorkflowSlug());
            $filters = $request->except(['workflow_slug']);

            // Buscar workflow
            $workflow = Workflow::where('slug', $workflowSlug)->first();
            if (!$workflow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workflow não encontrado',
                ], 404);
            }

            // Buscar templates do workflow
            $templates = WorkflowTemplate::where('workflow_id', $workflow->id)
                ->orderBy('sort_order')
                ->get();

            // Contar total de itens
            $totalItems = $this->model()::whereHas('currentWorkflow', function ($q) use ($workflow) {
                $q->where('workflow_id', $workflow->id);
            })->count();

            // Mapear templates para formato de resposta
            $templatesData = $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'slug' => $template->slug,
                    'name' => $template->name,
                    'color' => $template->color ?? '#6b7280',
                    'icon' => $template->icon ?? 'circle',
                    'order' => $template->sort_order,
                    'max_items' => $template->max_items,
                    'is_initial' => $template->is_initial ?? false,
                    'is_final' => $template->is_final ?? false,
                    'can_transition_to' => $template->getNextTemplateIds(),
                    'estimated_duration_days' => $template->estimated_duration_days,
                    'auto_assign' => $template->auto_assign ?? false,
                    'requires_approval' => $template->requires_approval ?? false,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'workflow' => [
                        'id' => $workflow->id,
                        'name' => $workflow->name,
                        'slug' => $workflow->slug,
                        'description' => $workflow->description,
                    ],
                    'templates' => $templatesData,
                    'total_items' => $totalItems,
                    'updated_at' => now()->toISOString(),
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Erro ao buscar colunas do Kanban', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar colunas',
            ], 500);
        }
    }

    /**
     * Buscar workflowable para um card
     */
    protected function findWorkflowableForCard($card): ?Workflowable
    {
        return Workflowable::where('workflowable_type', get_class($card))
            ->where('workflowable_id', $card->id)
            ->first();
    }

    /**
     * Detectar workflow ID baseado no tipo de CRUD
     */
    protected function detectWorkflowId(string $crudType): ?string
    {
        // Mapear tipos de CRUD para slugs de workflow
        $workflowSlugs = [
            'tickets' => 'suporte-tecnico',
            'sales' => 'pipeline-vendas',
            'orders' => 'processamento-pedidos',
            'pipeline' => 'desenvolvimento',
            'generic' => 'processo-generico',
        ];

        $slug = $workflowSlugs[$crudType] ?? $workflowSlugs['generic'];
        
        $workflow = Workflow::where('slug', $slug)->first();
        return $workflow?->id;
    }

    /**
     * Ações executadas após mover um card (sobrescrever conforme necessário)
     */
    protected function onKanbanCardMoved($card, $fromTemplate, $toTemplate, array $data)
    {
        // Implementação padrão - pode ser sobrescrita por controllers específicos
        // Exemplo: atualizar status, datas, notificações, etc.
    }

    /**
     * Registrar log de movimentação do Kanban
     */
    protected function logKanbanMovement($card, $fromTemplate, $toTemplate, string $workflowSlug)
    {
        Log::info('✅ Kanban: Card movido com sucesso', [
            'card_id' => $card->id,
            'card_type' => get_class($card),
            'from_template' => $fromTemplate?->slug,
            'to_template' => $toTemplate->slug,
            'workflow_slug' => $workflowSlug,
            'user_id' => auth()->id(),
            'moved_at' => now()->toISOString(),
        ]);
    }

    // ========================================
    // 🎯 MÉTODOS PADRÃO - Podem ser sobrescritos
    // ========================================

    /**
     * Validar transição entre templates (sobrescrever conforme necessário)
     */
    protected function validateKanbanTransition($card, $fromTemplate, $toTemplate, string $workflowSlug): bool
    {
        // Validação básica: verificar se a transição é permitida pelo template
        if ($fromTemplate && !$fromTemplate->canTransitionTo($toTemplate)) {
            return false;
        }

        // Validação de limite de itens no template de destino
        if ($toTemplate->max_items) {
            $currentCount = $this->model()::whereHas('currentWorkflow', function ($q) use ($toTemplate) {
                $q->where('current_template_id', $toTemplate->id);
            })->count();

            if ($currentCount >= $toTemplate->max_items) {
                return false;
            }
        }

        // Outras validações podem ser implementadas aqui
        return true;
    }

    /**
     * Aplicar filtros específicos do Kanban (sobrescrever conforme necessário)
     */
    protected function applyKanbanFilters($query, array $filters)
    {
        // Implementação padrão - pode ser sobrescrita por controllers específicos
        return $query;
    }

    /**
     * Método abstrato que deve ser implementado no controller
     */
    abstract protected function getModelClass(): ?string;

    /**
     * Detectar workflow slug baseado no tipo de CRUD
     */
    protected function detectWorkflowSlug(): string
    {
        // Mapear tipos de CRUD para slugs de workflow
        $workflowSlugs = [
            'tickets' => 'suporte-tecnico',
            'sales' => 'pipeline-vendas',
            'orders' => 'processamento-pedidos',
            'pipeline' => 'desenvolvimento',
            'generic' => 'processo-generico',
        ];

        $slug = $workflowSlugs[$this->crudType] ?? $workflowSlugs['generic'];
        
        return $slug;
    }

    /**
     * Criar um novo workflowable para um card
     */
    protected function createWorkflowable($card, string $workflowSlug): Workflowable
    {
        // Implementação padrão: criar um novo workflowable
        return Workflowable::create([
            'workflowable_type' => get_class($card),
            'workflowable_id' => $card->id,
            'workflow_slug' => $workflowSlug,
        ]);
    }
} 
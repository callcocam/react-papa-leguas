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
     * Move um card entre colunas do Kanban
     */
    public function moveCard(Request $request): JsonResponse
    {
        try {
            // Validar dados de entrada
            $validator = Validator::make($request->all(), [
                'card_id' => 'required|string',
                'from_column_id' => 'required|string',
                'to_column_id' => 'required|string',
                'item' => 'sometimes|array',
                'workflow_data' => 'sometimes|array',
                'crud_type' => 'sometimes|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Dados inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $cardId = $request->input('card_id');
            $fromColumnId = $request->input('from_column_id');
            $toColumnId = $request->input('to_column_id');
            $item = $request->input('item', []);
            $workflowData = $request->input('workflow_data', []);
            $crudType = $request->input('crud_type', 'generic');

            // Log da tentativa de movimento
            Log::info('🎯 Kanban: Tentativa de movimento de card', [
                'card_id' => $cardId,
                'from_column' => $fromColumnId,
                'to_column' => $toColumnId,
                'crud_type' => $crudType,
                'controller' => get_class($this),
                'user_id' => auth()->id(),
            ]);

            // Verificar se o card existe
            $modelClass = $this->getModelClass();
            if (!$modelClass) {
                throw new Exception('Classe do modelo não definida no controller');
            }

            $card = $modelClass::find($cardId);
            if (!$card) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item não encontrado'
                ], 404);
            }

            // Buscar o workflowable do card
            $workflowable = $card->currentWorkflow ?? $this->findWorkflowableForCard($card);
            if (!$workflowable) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workflow não encontrado para este item'
                ], 404);
            }

            // Buscar templates de origem e destino
            $fromTemplate = WorkflowTemplate::where('slug', $fromColumnId)->first();
            $toTemplate = WorkflowTemplate::where('slug', $toColumnId)->first();

            if (!$toTemplate) {
                return response()->json([
                    'success' => false,
                    'message' => 'Template de destino não encontrado'
                ], 404);
            }

            // Validar transição
            $isValid = $this->validateKanbanTransition($card, $fromTemplate, $toTemplate, $crudType, $workflowData);
            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transição não permitida'
                ], 403);
            }

            // Iniciar transação
            DB::beginTransaction();

            // Mover workflowable para novo template
            $moved = $workflowable->moveToTemplate($toTemplate, [
                'workflow_data' => array_merge($workflowable->workflow_data ?? [], $workflowData),
            ]);

            if (!$moved) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Não foi possível mover o item'
                ], 400);
            }

            // Executar ações customizadas
            $this->onKanbanCardMoved($card, $fromColumnId, $toColumnId, $item, $crudType, $workflowData);

            // Registrar log de auditoria
            $this->logKanbanMovement($card, $fromColumnId, $toColumnId, $crudType);

            DB::commit();

            Log::info('✅ Kanban: Card movido com sucesso', [
                'card_id' => $cardId,
                'from_column' => $fromColumnId,
                'to_column' => $toColumnId,
                'new_template' => $toTemplate->name,
                'new_step' => $toTemplate->sort_order,
                'crud_type' => $crudType,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item movido com sucesso',
                'data' => [
                    'card_id' => $cardId,
                    'new_step' => $toTemplate->sort_order,
                    'new_template_id' => $toTemplate->id,
                    'from_column' => $fromColumnId,
                    'to_column' => $toColumnId,
                    'crud_type' => $crudType,
                    'updated_at' => $workflowable->fresh()->updated_at,
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('❌ Kanban: Erro ao mover card', [
                'card_id' => $request->input('card_id'),
                'crud_type' => $request->input('crud_type', 'generic'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro interno do servidor',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtém estatísticas do Kanban
     */
    public function getKanbanStats(Request $request): JsonResponse
    {
        try {
            $modelClass = $this->getModelClass();
            if (!$modelClass) {
                throw new Exception('Classe do modelo não definida');
            }

            $crudType = $request->input('crud_type', 'generic');

            // Buscar dados com workflow
            $query = $modelClass::with('currentWorkflow.currentTemplate');
            
            // Aplicar filtros
            $query = $this->applyKanbanFilters($query, $request, $crudType);

            $items = $query->get();

            // Agrupar por template
            $stats = $items->groupBy(function ($item) {
                return $item->currentWorkflow?->currentTemplate?->slug ?? 'sem-workflow';
            })->map(function ($group, $templateSlug) {
                $template = $group->first()?->currentWorkflow?->currentTemplate;
                
                return [
                    'template_slug' => $templateSlug,
                    'template_name' => $template?->name ?? 'Sem workflow',
                    'count' => $group->count(),
                    'percentage' => 0,
                    'color' => $template?->color ?? '#6b7280',
                ];
            });

            // Calcular percentuais
            $total = $items->count();
            if ($total > 0) {
                $stats = $stats->map(function ($stat) use ($total) {
                    $stat['percentage'] = round(($stat['count'] / $total) * 100, 1);
                    return $stat;
                });
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'by_template' => $stats->values(),
                    'crud_type' => $crudType,
                    'updated_at' => now()->toISOString(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('❌ Kanban: Erro ao buscar estatísticas', [
                'error' => $e->getMessage(),
                'controller' => get_class($this),
                'crud_type' => $request->input('crud_type', 'generic'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Obtém colunas do Kanban baseadas nos workflows
     */
    public function getKanbanColumns(Request $request): JsonResponse
    {
        try {
            $workflowId = $request->input('workflow_id');
            $crudType = $request->input('crud_type', 'generic');

            // Se não foi especificado workflow, tentar detectar
            if (!$workflowId) {
                $workflowId = $this->detectWorkflowId($crudType);
            }

            if (!$workflowId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workflow não encontrado'
                ], 404);
            }

            $workflow = Workflow::with('activeTemplates')->find($workflowId);
            if (!$workflow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Workflow não encontrado'
                ], 404);
            }

            // Obter colunas baseadas nos templates
            $columns = $workflow->activeTemplates->map(function (WorkflowTemplate $template) {
                return [
                    'id' => $template->slug,
                    'title' => $template->name,
                    'key' => 'current_template_id',
                    'color' => $template->color ?? '#6b7280',
                    'icon' => $template->icon ?? 'circle',
                    'maxItems' => $template->max_items,
                    'sortable' => true,
                    'order' => $template->sort_order,
                    'config' => [
                        'template_id' => $template->id,
                        'auto_assign' => $template->auto_assign,
                        'requires_approval' => $template->requires_approval,
                        'estimated_duration_days' => $template->estimated_duration_days,
                    ],
                ];
            })->sortBy('order')->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'workflow' => [
                        'id' => $workflow->id,
                        'name' => $workflow->name,
                        'slug' => $workflow->slug,
                    ],
                    'columns' => $columns,
                    'crud_type' => $crudType,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('❌ Kanban: Erro ao buscar colunas', [
                'error' => $e->getMessage(),
                'controller' => get_class($this),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar colunas',
                'error' => config('app.debug') ? $e->getMessage() : null
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
     * Registra log de auditoria do movimento
     */
    protected function logKanbanMovement($card, string $fromColumnId, string $toColumnId, string $crudType = 'generic'): void
    {
        // Log básico sempre
        Log::info('📋 Kanban: Movimento registrado', [
            'card_id' => $card->id,
            'model' => get_class($card),
            'from_column' => $fromColumnId,
            'to_column' => $toColumnId,
            'crud_type' => $crudType,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ]);

        // Se o model tem sistema de auditoria, usar
        if (method_exists($card, 'logActivity')) {
            $card->logActivity('kanban_moved', [
                'from_column' => $fromColumnId,
                'to_column' => $toColumnId,
                'crud_type' => $crudType,
                'moved_by' => auth()->id(),
            ]);
        }
    }

    // ========================================
    // 🎯 MÉTODOS PADRÃO - Podem ser sobrescritos
    // ========================================

    /**
     * Validação padrão de transições usando templates reais
     * PODE ser sobrescrito no controller para regras específicas
     */
    protected function validateKanbanTransition($card, ?WorkflowTemplate $fromTemplate, WorkflowTemplate $toTemplate, string $crudType = 'generic', array $workflowData = []): bool
    {
        // Se não há template de origem, permitir (primeiro movimento)
        if (!$fromTemplate) {
            return true;
        }

        // Verificar regras de transição do template
        if (!$fromTemplate->canTransitionTo($toTemplate)) {
            Log::warning('⚠️ Kanban: Transição não permitida pelas regras do template', [
                'from_template' => $fromTemplate->name,
                'to_template' => $toTemplate->name,
                'card_id' => $card->id,
            ]);
            return false;
        }

        // Verificar limite de itens no template de destino
        if ($toTemplate->hasMaxItems() && $toTemplate->isAtLimit()) {
            Log::warning('⚠️ Kanban: Template de destino está no limite', [
                'template' => $toTemplate->name,
                'current_count' => $toTemplate->getCurrentCount(),
                'max_items' => $toTemplate->max_items,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Ações padrão após mover card
     * PODE ser sobrescrito no controller para ações específicas
     */
    protected function onKanbanCardMoved($card, string $fromColumnId, string $toColumnId, array $item, string $crudType = 'generic', array $workflowData = []): void
    {
        // Implementação padrão: apenas log
        Log::info('📧 Kanban: Item movido', [
            'card_id' => $card->id,
            'from' => $fromColumnId,
            'to' => $toColumnId,
            'crud_type' => $crudType,
        ]);
    }

    /**
     * Filtros padrão para Kanban
     * PODE ser sobrescrito no controller para filtros específicos
     */
    protected function applyKanbanFilters(Builder $query, Request $request, string $crudType = 'generic'): Builder
    {
        // Implementação padrão: sem filtros adicionais
        // Sobrescrever no controller para filtros específicos por tipo de CRUD
        return $query;
    }

    /**
     * Método abstrato que deve ser implementado no controller
     */
    abstract protected function getModelClass(): ?string;
} 
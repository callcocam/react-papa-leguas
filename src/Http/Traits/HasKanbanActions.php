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
 * IMPORTANTE: Esta trait assume que os itens JÁ POSSUEM workflowables criados.
 * - Para aparecer no Kanban, o item deve ter um currentWorkflow ativo
 * - A criação de workflowables deve ser feita na criação do item (ex: ao criar ticket)
 * - Esta trait apenas move itens entre templates existentes
 * 
 * Integra com:
 * - Workflow: Define o processo de negócio
 * - WorkflowTemplate: Define as colunas/etapas do Kanban
 * - Workflowable: Relaciona entidades com workflows (deve existir previamente)
 * 
 * @package Callcocam\ReactPapaLeguas\Http\Traits
 */
trait HasKanbanActions
{
    use HasKanbanColumn;
    use HasKanbanStats;

    protected ?string $workflowMessage = null;
    
    /**
     * Mover card entre colunas do Kanban
     */
    public function moveCard(Request $request)
    {
        try {
            // 🎯 Validar dados de entrada - aceitar ambos os formatos para compatibilidade
            $validated = $request->validate([
                'card_id' => 'required',
                // Aceitar tanto column_id quanto template_id (compatibilidade)
                'from_column_id' => 'required|string',
                'to_column_id' => 'required|string',
                'from_template_id' => 'nullable|string',
                'to_template_id' => 'nullable|string',
                'workflow_slug' => 'nullable|string',
                'item' => 'nullable|array',
                'workflow_data' => 'nullable|array',
            ]);

            $cardId = $validated['card_id'];
            
            // 🔄 Usar column_id se template_id não estiver presente (compatibilidade)
            $fromTemplateId = $validated['from_template_id'] ?? $validated['from_column_id'];
            $toTemplateId = $validated['to_template_id'] ?? $validated['to_column_id'];
            $workflowSlug = $validated['workflow_slug'] ?? $this->detectWorkflowSlug();     
            // 🔍 Buscar o item
            $modelClass = $this->getModelClass(); // ✅ Corrigido: era resolveModelClass()
            if (!$modelClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Classe do modelo não definida no controller',
                ], 500);
            }

            $item = $modelClass::find($cardId);
            if (!$item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Item não encontrado',
                    'errors' => [
                        'card_id' => ['Item com ID ' . $cardId . ' não encontrado']
                    ]
                ], 404);
            }

            // 🔍 Buscar templates do workflow - primeiro por ID, depois por slug
            $fromTemplate = WorkflowTemplate::where('id', $fromTemplateId)
                ->orWhere('slug', $fromTemplateId)
                ->first();
                
            $toTemplate = WorkflowTemplate::where('id', $toTemplateId)
                ->orWhere('slug', $toTemplateId)
                ->first();

            if (!$fromTemplate) {
                Log::error('❌ Template de origem não encontrado', [
                    'from_template_id' => $fromTemplateId,
                    'available_templates' => WorkflowTemplate::pluck('id', 'slug')->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Template de origem não encontrado',
                    'errors' => [
                        'from_template_id' => ['Template com ID/slug ' . $fromTemplateId . ' não encontrado']
                    ]
                ], 404);
            }

            if (!$toTemplate) {
                Log::error('❌ Template de destino não encontrado', [
                    'to_template_id' => $toTemplateId,
                    'available_templates' => WorkflowTemplate::pluck('id', 'slug')->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Template de destino não encontrado',
                    'errors' => [
                        'to_template_id' => ['Template com ID/slug ' . $toTemplateId . ' não encontrado']
                    ]
                ], 404);
            }

            // ✅ Validar transição
            if (!$this->validateKanbanTransition($item, $fromTemplate, $toTemplate, $workflowSlug)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transição não permitida pelo workflow',
                    'errors' => [
                        'transition' => [$this->workflowMessage ?? 'Movimento não permitido entre estes templates']
                    ]
                ], 422);
            }

            // 🔄 Executar movimento em transação
            DB::beginTransaction();

            try {
                // Buscar workflowable existente (deve existir se está no Kanban)
                $workflowable = $item->currentWorkflow;
                
                if (!$workflowable) {
                    Log::warning('⚠️ Item sem workflowable ativo', [
                        'card_id' => $cardId,
                        'card_type' => get_class($item)
                    ]);
                    
                    DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Item não possui workflow ativo. Workflowable deve ser criado antes da movimentação.',
                        'errors' => [
                            'workflow' => ['Item não está associado a nenhum workflow ativo']
                        ]
                    ], 422);
                }

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
                Log::error('❌ Erro durante transação de movimentação', [
                    'error' => $e->getMessage(),
                    'card_id' => $cardId,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }
        } catch (ValidationException $e) {
            Log::error('❌ Dados de entrada inválidos', [
                'errors' => $e->errors(),
                'request' => $request->all()
            ]);
            
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
                'message' => 'Erro interno do servidor ao mover card: ' . $e->getMessage(),
            ], 500);
        }
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
            if(method_exists($fromTemplate, 'getTransitionMessage')){
                $this->workflowMessage = $fromTemplate->getTransitionMessage();
            }
            return false;
        } 
        // Validação de limite de itens no template de destino
        if ($toTemplate->max_items) {
            $modelClass = $this->getModelClass();
            if ($modelClass) {
                $currentCount = $modelClass::whereHas('currentWorkflow', function ($q) use ($toTemplate) {
                    $q->where('current_template_id', $toTemplate->id);
                })->count();

                if ($currentCount >= $toTemplate->max_items) {
                    $this->workflowMessage = 'Limite de itens atingido no template de destino';
                    return false;
                }
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
    protected function detectWorkflowSlug(): ?string
    {
        
        return null;
    }


}

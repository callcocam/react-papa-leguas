<?php

namespace Callcocam\ReactPapaLeguas\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Builder;
use Exception;

/**
 * Trait HasKanbanActions
 * 
 * Adiciona funcionalidades completas de Kanban a qualquer controller CRUD.
 * Inclui validações, mapeamentos, filtros e ações customizáveis.
 * 
 * @package Callcocam\ReactPapaLeguas\Http\Traits
 */
trait HasKanbanActions
{
    /**
     * Move um card entre colunas do Kanban
     * 
     * @param Request $request
     * @return JsonResponse
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

            // Log da tentativa de movimento
            Log::info('🎯 Kanban: Tentativa de movimento de card', [
                'card_id' => $cardId,
                'from_column' => $fromColumnId,
                'to_column' => $toColumnId,
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
                    'message' => 'Card não encontrado'
                ], 404);
            }

            // Validar transição
            $isValid = $this->validateKanbanTransition($card, $fromColumnId, $toColumnId);
            if (!$isValid) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transição não permitida'
                ], 403);
            }

            // Iniciar transação
            DB::beginTransaction();

            // Mapear column ID para workflow step
            $newStep = $this->mapColumnIdToWorkflowStep($toColumnId);
            
            // Atualizar workflow do card
            $this->updateCardWorkflow($card, $newStep, $toColumnId);

            // Executar ações customizadas
            $this->onKanbanCardMoved($card, $fromColumnId, $toColumnId, $item);

            // Registrar log de auditoria
            $this->logKanbanMovement($card, $fromColumnId, $toColumnId);

            DB::commit();

            Log::info('✅ Kanban: Card movido com sucesso', [
                'card_id' => $cardId,
                'from_column' => $fromColumnId,
                'to_column' => $toColumnId,
                'new_step' => $newStep,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Card movido com sucesso',
                'data' => [
                    'card_id' => $cardId,
                    'new_step' => $newStep,
                    'from_column' => $fromColumnId,
                    'to_column' => $toColumnId,
                    'updated_at' => $card->fresh()->updated_at,
                ]
            ]);

        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('❌ Kanban: Erro ao mover card', [
                'card_id' => $request->input('card_id'),
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
     * Mapeia column ID para workflow step
     * 
     * @param string $columnId
     * @return int
     */
    protected function mapColumnIdToWorkflowStep(string $columnId): int
    {
        $mapping = $this->getKanbanColumnMapping();
        return $mapping[$columnId] ?? 1;
    }

    /**
     * Atualiza o workflow do card
     * 
     * @param mixed $card
     * @param int $newStep
     * @param string $toColumnId
     * @return void
     */
    protected function updateCardWorkflow($card, int $newStep, string $toColumnId): void
    {
        // Verificar se o card tem workflow
        if (!$card->currentWorkflow) {
            Log::warning('⚠️ Kanban: Card sem workflow', [
                'card_id' => $card->id,
                'model' => get_class($card),
            ]);
            return;
        }

        // Atualizar current_step
        $card->currentWorkflow->update([
            'current_step' => $newStep,
            'current_template_id' => "step-{$newStep}-{$toColumnId}",
            'updated_at' => now(),
        ]);

        // Atualizar campos do card se necessário
        if (method_exists($card, 'updateKanbanFields')) {
            $card->updateKanbanFields($newStep, $toColumnId);
        }
    }

    /**
     * Registra log de auditoria do movimento
     * 
     * @param mixed $card
     * @param string $fromColumnId
     * @param string $toColumnId
     * @return void
     */
    protected function logKanbanMovement($card, string $fromColumnId, string $toColumnId): void
    {
        // Log básico sempre
        Log::info('📋 Kanban: Movimento registrado', [
            'card_id' => $card->id,
            'model' => get_class($card),
            'from_column' => $fromColumnId,
            'to_column' => $toColumnId,
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
        ]);

        // Se o model tem sistema de auditoria, usar
        if (method_exists($card, 'logActivity')) {
            $card->logActivity('kanban_moved', [
                'from_column' => $fromColumnId,
                'to_column' => $toColumnId,
                'moved_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Obtém estatísticas do Kanban
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getKanbanStats(Request $request): JsonResponse
    {
        try {
            $modelClass = $this->getModelClass();
            if (!$modelClass) {
                throw new Exception('Classe do modelo não definida');
            }

            // Buscar dados com workflow
            $query = $modelClass::with('currentWorkflow');
            
            // Aplicar filtros
            $query = $this->applyKanbanFilters($query, $request);

            $items = $query->get();

            // Agrupar por step
            $stats = $items->groupBy(function ($item) {
                return $item->currentWorkflow->current_step ?? 1;
            })->map(function ($group, $step) {
                return [
                    'step' => $step,
                    'count' => $group->count(),
                    'percentage' => 0,
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
                    'by_step' => $stats->values(),
                    'updated_at' => now()->toISOString(),
                ]
            ]);

        } catch (Exception $e) {
            Log::error('❌ Kanban: Erro ao buscar estatísticas', [
                'error' => $e->getMessage(),
                'controller' => get_class($this),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao buscar estatísticas',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Mapeamento padrão de colunas para workflow steps
     * DEVE ser sobrescrito no controller para customização
     */
    protected function getKanbanColumnMapping(): array
    {
        return [
            'aberto' => 1,
            'em-andamento' => 2,
            'aguardando-cliente' => 3,
            'resolvido' => 4,
            'fechado' => 5,
        ];
    }

    /**
     * Validação padrão de transições
     * PODE ser sobrescrito no controller para regras específicas
     */
    protected function validateKanbanTransition($card, string $fromColumnId, string $toColumnId): bool
    {
        // Validação padrão: permitir qualquer transição
        // Sobrescrever no controller para regras específicas
        return true;
    }

    /**
     * Ações padrão após mover card
     * PODE ser sobrescrito no controller para ações específicas
     */
    protected function onKanbanCardMoved($card, string $fromColumnId, string $toColumnId, array $item): void
    {
        // Implementação padrão: apenas log
        Log::info('📧 Kanban: Card movido', [
            'card_id' => $card->id,
            'from' => $fromColumnId,
            'to' => $toColumnId,
        ]);
    }

    /**
     * Filtros padrão para Kanban
     * PODE ser sobrescrito no controller para filtros específicos
     */
    protected function applyKanbanFilters(Builder $query, Request $request): Builder
    {
        // Implementação padrão: sem filtros adicionais
        // Sobrescrever no controller para filtros específicos
        return $query;
    }

    /**
     * Método abstrato que deve ser implementado no controller
     * para retornar a classe do modelo
     * 
     * @return string|null
     */
    abstract protected function getModelClass(): ?string;
} 
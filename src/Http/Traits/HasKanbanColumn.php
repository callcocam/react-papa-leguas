<?php

/**
 * Trait HasKanbanColumn
 *
 * Este trait fornece métodos para manipulação de colunas no kanban.
 *
 * @package Callcocam\ReactPapaLeguas\Http\Traits
 */
namespace Callcocam\ReactPapaLeguas\Http\Traits;

use Illuminate\Http\Request;
use Callcocam\ReactPapaLeguas\Models\Workflow;
use Callcocam\ReactPapaLeguas\Models\WorkflowTemplate;
use Illuminate\Support\Facades\Log;

trait HasKanbanColumn
{
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
            $modelClass = $this->getModelClass();
            if (!$modelClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Classe do modelo não definida no controller',
                ], 500);
            }

            $totalItems = $modelClass::whereHas('currentWorkflow', function ($q) use ($workflow) {
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
}

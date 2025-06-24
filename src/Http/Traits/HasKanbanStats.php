<?php

/**
 * Trait HasKanbanStats
 *
 * Este trait fornece métodos para obter estatísticas do Kanban.
 *
 * @package Callcocam\ReactPapaLeguas\Http\Traits
 */

namespace Callcocam\ReactPapaLeguas\Http\Traits;

use Illuminate\Http\Request;
use Callcocam\ReactPapaLeguas\Models\Workflow;
use Callcocam\ReactPapaLeguas\Models\WorkflowTemplate;
use Illuminate\Support\Facades\Log;

trait HasKanbanStats
{
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
            $modelClass = $this->getModelClass();
            if (!$modelClass) {
                return response()->json([
                    'success' => false,
                    'message' => 'Classe do modelo não definida no controller',
                ], 500);
            }

            $query = $modelClass::whereHas('currentWorkflow', function ($q) use ($workflow) {
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
}

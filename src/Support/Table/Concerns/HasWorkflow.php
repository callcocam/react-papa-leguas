<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Illuminate\Support\Facades\Log;
use Callcocam\ReactPapaLeguas\Models\Workflow;
use Callcocam\ReactPapaLeguas\Support\Table\Views\KanbanView;
use Illuminate\Database\Eloquent\Model;

/**
 * Trait para adicionar suporte a views condicionais baseadas em workflows
 * 
 * Este trait permite que Tables mostrem a view Kanban apenas quando
 * há um workflow configurado no banco de dados para o model correspondente.
 */
trait HasWorkflow
{
    /**
     * Detectar workflow configurado para a Table atual
     * Busca APENAS no banco de dados - sem fallbacks
     */
    protected function getWorkflowForCurrentTable(): ?array
    {
        $tableClass = static::class;
        $modelClass = $this->getModelClass();

        if (!$modelClass) {
            return null;
        }

        // Buscar no banco de dados usando models implementados
        try {
            // Buscar workflows ativos que possam corresponder ao model 
            if (!method_exists($this, 'detectWorkflowSlug')) {
                throw new \Exception('Method detectWorkflowSlug not found in ' . get_class($this));
            }
            $expectedSlug = $this->detectWorkflowSlug();
            if (!$expectedSlug) {
                return null;
            }

            // Buscar workflow ativo no banco
            $workflow = Workflow::active()
                ->where('slug', $expectedSlug)
                ->with('activeTemplates')
                ->first();

            if ($workflow && $workflow->activeTemplates->count() > 0) {
                return [
                    'slug' => $workflow->slug,
                    'name' => $workflow->name,
                    'model_target' => $modelClass,
                    'database_id' => $workflow->id,
                    'model' => $workflow
                ];
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao buscar workflow no banco de dados', [
                'model_class' => $modelClass,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Gerar colunas Kanban baseadas APENAS no workflow do banco
     * Se não encontrar workflow, retorna array vazio
     */
    protected function getKanbanColumnsFromWorkflow(): array
    {
        $workflow = $this->getWorkflowForCurrentTable();
        if (!$workflow) {
            Log::info('Sem workflow configurado - colunas Kanban não geradas');
            return [];
        }
        try {
            $workflowModel = $workflow['model'];
            $templates = $workflowModel->activeTemplates;

            if ($templates->count() > 0) {
                $columns = [];
                foreach ($templates as $template) {
                    $columns[] = $template->getKanbanColumnConfig();
                }

                // Ordenar por sort_order
                usort($columns, fn($a, $b) => $a['order'] <=> $b['order']); 
                return $columns;
            }
        } catch (\Exception $e) {
            Log::warning('Erro ao buscar templates do workflow no banco', [
                'workflow_slug' => $workflow['slug'],
                'error' => $e->getMessage()
            ]);
        }

        return [];
    }

    /**
     * Obter classe do Model (deve ser implementado pela Table)
     * Se não implementado, retorna null
     */
    protected function getModelClass(): ?string
    {
        return null;
    }

    /**
     * Retorna views com suporte condicional a workflow
     * Kanban só aparece se houver workflow configurado no banco
     */
    protected function getViewsWithWorkflowSupport(array $views): array
    {
        // Verificar se há workflow configurado
        $workflow = $this->getWorkflowForCurrentTable();

        if ($workflow) {
            // Adicionar view Kanban apenas se workflow existe
            $views[] = KanbanView::make('kanban', 'Kanban')
                ->icon('kanban')
                ->description("Quadro Kanban - {$workflow['name']}")
                ->workflow_slug($workflow['slug'])
                ->workflow_name($workflow['name'])
                ->columns($this->getKanbanColumnsFromWorkflow())->toArray();
        }

        return $views;
    }

    protected function getColumnsWithWorkflowSupport(Model $row): array
    { 
        $data = $row->toArray(); 
        return [
            'workflowables' => data_get($data, 'workflowables') ?? [],
            'currentWorkflow' => data_get($data, 'current_workflow') ?? data_get($data, 'currentWorkflow') ?? null,
        ];
    }

    protected function getModelWithWorkflowSupport(): array
    {
        // Se esta trait está sendo usada, significa que queremos workflow
        // Retornar relacionamentos necessários para workflow
        return ['workflowables', 'currentWorkflow'];
    }
}

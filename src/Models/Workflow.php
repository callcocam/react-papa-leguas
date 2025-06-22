<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Models;

use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Builder;

/**
 * Workflow Model - Representa um processo de negócio.
 * 
 * Um workflow define um processo completo (ex: Suporte Técnico, Pipeline de Vendas)
 * e contém múltiplas etapas (WorkflowTemplate) que representam as colunas do Kanban.
 * 
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property \Callcocam\ReactPapaLeguas\Enums\BaseStatus $status
 * @property string|null $user_id
 * @property string|null $tenant_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Workflow extends AbstractModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'user_id',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        // Removidos casts dos campos eliminados
        // Mantém apenas os casts herdados do AbstractModel
    ];

    /**
     * Get the workflow templates (etapas/colunas).
     */
    public function templates(): HasMany
    {
        return $this->hasMany(WorkflowTemplate::class)->orderBy('sort_order');
    }

    /**
     * Get active workflow templates.
     */
    public function activeTemplates(): HasMany
    {
        return $this->templates()->published(); // Usa o scope published do BaseStatus
    }

    /**
     * Get the workflowables (entidades usando este workflow).
     */
    public function workflowables(): HasMany
    {
        return $this->hasMany(Workflowable::class);
    }

    /**
     * Scope para workflows ativos (Published).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->published(); // Usa scope published do BaseStatus
    }

    /**
     * Scope ordenado por nome.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('name');
    }

    /**
     * Verificar se o workflow está ativo.
     */
    public function isActive(): bool
    {
        return $this->isPublished();
    }

    /**
     * Obter colunas do Kanban baseadas nos templates.
     */
    public function getKanbanColumns(): array
    {
        return $this->activeTemplates->map(function (WorkflowTemplate $template) {
            return [
                'id' => $template->slug,
                'title' => $template->name,
                'color' => $template->color,
                'icon' => $template->icon,
                'key' => 'current_template_id',
                'filter' => function ($item) use ($template) {
                    return $item->current_template_id === $template->id;
                },
                'maxItems' => $template->max_items,
                'sortable' => true,
                'config' => [
                    'template_id' => $template->id,
                    'auto_assign' => $template->auto_assign,
                    'requires_approval' => $template->requires_approval,
                    'transition_rules' => $template->transition_rules,
                ]
            ];
        })->toArray();
    }

    /**
     * Obter estatísticas do workflow.
     */
    public function getStats(): array
    {
        $workflowables = $this->workflowables();
        
        return [
            'total_items' => $workflowables->count(),
            'active_items' => $workflowables->where('status', 'active')->count(),
            'completed_items' => $workflowables->where('status', 'completed')->count(),
            'average_completion_time' => $this->getAverageCompletionTime(),
            'templates_count' => $this->templates()->count(),
            'active_templates_count' => $this->activeTemplates()->count(),
        ];
    }

    /**
     * Obter tempo médio de conclusão em dias.
     */
    public function getAverageCompletionTime(): ?float
    {
        $completed = $this->workflowables()
            ->whereNotNull('completed_at')
            ->whereNotNull('started_at')
            ->get();

        if ($completed->isEmpty()) {
            return null;
        }

        $totalDays = $completed->sum(function ($workflowable) {
            return $workflowable->started_at->diffInDays($workflowable->completed_at);
        });

        return round($totalDays / $completed->count(), 1);
    }

    /**
     * Criar um novo workflowable para uma entidade.
     */
    public function attachTo($model, array $data = []): Workflowable
    {
        $firstTemplate = $this->activeTemplates()->first();
        
        return Workflowable::create(array_merge([
            'workflowable_type' => get_class($model),
            'workflowable_id' => $model->id,
            'workflow_id' => $this->id,
            'current_template_id' => $firstTemplate?->id,
            'current_step' => 1,
            'total_steps' => $this->activeTemplates()->count(),
            'started_at' => now(),
            'status' => 'active',
            'user_id' => auth()->id(),
            'tenant_id' => $this->tenant_id,
        ], $data));
    }

    /**
     * Verificar se pode ser deletado.
     */
    public function canBeDeleted(): bool
    {
        return $this->workflowables()->count() === 0;
    }

    /**
     * Boot do model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Remove auto-slug se não tiver campo slug na migration
        // O AbstractModel já cuida do slug automaticamente
    }
} 
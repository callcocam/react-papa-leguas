<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

/**
 * WorkflowTemplate Model - Representa uma etapa/coluna de um workflow.
 * 
 * Cada template define uma coluna do Kanban com suas configurações específicas,
 * como cor, ícone, regras de transição e limites.
 * 
 * @property string $id
 * @property string $workflow_id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $instructions
 * @property string|null $category
 * @property array|null $tags
 * @property int $suggested_order
 * @property int|null $estimated_duration_days
 * @property string $color
 * @property string|null $icon
 * @property bool $is_required_by_default
 * @property bool $is_active
 * @property bool $is_featured
 * @property int $sort_order
 * @property int|null $max_items
 * @property bool $auto_assign
 * @property bool $requires_approval
 * @property array|null $transition_rules
 * @property array|null $metadata
 * @property array|null $settings
 * @property \Callcocam\ReactPapaLeguas\Enums\BaseStatus $status
 * @property string|null $user_id
 * @property string|null $tenant_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class WorkflowTemplate extends AbstractModel
{
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'workflow_id',
        'name',
        'slug',
        'description',
        'instructions',
        'category',
        'tags',
        'suggested_order',
        'estimated_duration_days',
        'color',
        'icon',
        'is_required_by_default',
        'is_active',
        'is_featured',
        'sort_order',
        'max_items',
        'auto_assign',
        'requires_approval',
        'transition_rules',
        'metadata',
        'settings',
        'status',
        'user_id',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'tags' => 'array',
        'transition_rules' => 'array',
        'metadata' => 'array',
        'settings' => 'array',
        'is_required_by_default' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'auto_assign' => 'boolean',
        'requires_approval' => 'boolean',
        'suggested_order' => 'integer',
        'estimated_duration_days' => 'integer',
        'sort_order' => 'integer',
        'max_items' => 'integer',
    ];

    /**
     * Get the workflow that owns this template.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get workflowables currently in this template.
     */
    public function workflowables(): HasMany
    {
        return $this->hasMany(Workflowable::class, 'current_template_id');
    }

    /**
     * Scope para templates ativos.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope por categoria.
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Scope ordenado por sort_order.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope por workflow.
     */
    public function scopeForWorkflow(Builder $query, string $workflowId): Builder
    {
        return $query->where('workflow_id', $workflowId);
    }

    /**
     * Verificar se o template está ativo.
     */
    public function isActive(): bool
    {
        return $this->is_active && $this->isPublished();
    }

    /**
     * Verificar se requer aprovação.
     */
    public function requiresApproval(): bool
    {
        return $this->requires_approval;
    }

    /**
     * Verificar se tem atribuição automática.
     */
    public function hasAutoAssign(): bool
    {
        return $this->auto_assign;
    }

    /**
     * Verificar se tem limite de itens.
     */
    public function hasMaxItems(): bool
    {
        return !is_null($this->max_items);
    }

    /**
     * Verificar se está no limite de itens.
     */
    public function isAtLimit(): bool
    {
        if (!$this->hasMaxItems()) {
            return false;
        }

        return $this->workflowables()->count() >= $this->max_items;
    }

    /**
     * Obter contagem atual de itens.
     */
    public function getCurrentCount(): int
    {
        return $this->workflowables()->count();
    }

    /**
     * Obter próximos templates possíveis baseado nas regras de transição.
     */
    public function getNextTemplates(): array
    {
        if (!$this->transition_rules) {
            return [];
        }

        $allowedTemplateIds = $this->transition_rules['allowed_next'] ?? [];
        
        if (empty($allowedTemplateIds)) {
            return [];
        }

        return $this->workflow->templates()
            ->whereIn('id', $allowedTemplateIds)
            ->active()
            ->ordered()
            ->get()
            ->toArray();
    }

    /**
     * Verificar se pode transicionar para outro template.
     */
    public function canTransitionTo(WorkflowTemplate $target): bool
    {
        $nextTemplates = $this->getNextTemplates();
        
        return collect($nextTemplates)->contains('id', $target->id);
    }

    /**
     * Obter configuração para coluna Kanban.
     */
    public function getKanbanColumnConfig(): array
    {
        return [
            'id' => $this->slug,
            'title' => $this->name,
            'color' => $this->color,
            'icon' => $this->icon,
            'key' => 'current_template_id',
            'maxItems' => $this->max_items,
            'sortable' => true,
            'filter' => function ($item) {
                return $item->current_template_id === $this->id;
            },
            'config' => [
                'template_id' => $this->id,
                'auto_assign' => $this->auto_assign,
                'requires_approval' => $this->requires_approval,
                'transition_rules' => $this->transition_rules,
                'estimated_duration_days' => $this->estimated_duration_days,
                'instructions' => $this->instructions,
            ]
        ];
    }

    /**
     * Obter estatísticas do template.
     */
    public function getStats(): array
    {
        $workflowables = $this->workflowables();
        
        return [
            'current_count' => $workflowables->count(),
            'max_items' => $this->max_items,
            'is_at_limit' => $this->isAtLimit(),
            'average_time_in_stage' => $this->getAverageTimeInStage(),
            'completion_rate' => $this->getCompletionRate(),
        ];
    }

    /**
     * Obter tempo médio nesta etapa em horas.
     */
    public function getAverageTimeInStage(): ?float
    {
        // TODO: Implementar quando houver histórico de transições
        return null;
    }

    /**
     * Obter taxa de conclusão desta etapa.
     */
    public function getCompletionRate(): float
    {
        // TODO: Implementar baseado no histórico
        return 0.0;
    }

    /**
     * Boot do model.
     */
    protected static function boot()
    {
        parent::boot();

        // Definir sort_order automaticamente dentro do workflow
        static::creating(function (WorkflowTemplate $template) {
            if (is_null($template->sort_order)) {
                $maxOrder = static::where('workflow_id', $template->workflow_id)
                    ->max('sort_order');
                $template->sort_order = ($maxOrder ?? 0) + 1;
            }
        });

        // Gerar slug baseado no workflow
        static::creating(function (WorkflowTemplate $template) {
            if (empty($template->slug)) {
                $baseSlug = Str::slug($template->name);
                $workflow = Workflow::find($template->workflow_id);
                
                // Verificar unicidade dentro do workflow
                $counter = 1;
                $slug = $baseSlug;
                
                while (static::where('workflow_id', $template->workflow_id)
                    ->where('slug', $slug)
                    ->exists()) {
                    $slug = $baseSlug . '-' . $counter;
                    $counter++;
                }
                
                $template->slug = $slug;
            }
        });
    }

    /**
     * Get the source field for slug generation.
     */
    protected function getSlugSource(): string
    {
        return 'name';
    }
} 
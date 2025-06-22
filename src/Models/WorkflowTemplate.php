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
 * @property int|null $estimated_duration_days
 * @property string $color
 * @property string|null $icon
 * @property bool $is_required_by_default
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
        'estimated_duration_days',
        'color',
        'icon',
        'is_required_by_default',
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
        'auto_assign' => 'boolean',
        'requires_approval' => 'boolean',
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
     * Scope para templates ativos (Published).
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->published(); // Usa scope published do BaseStatus
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
        return $this->isPublished();
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
            'status' => $this->slug,
            'color' => $this->color ?? '#6B7280',
            'icon' => $this->icon ?? 'circle',
            'limit' => $this->max_items,
            'order' => $this->sort_order ?? 0,
            'description' => $this->description,
            'template_id' => $this->id,
            'auto_assign' => $this->auto_assign ?? false,
            'requires_approval' => $this->requires_approval ?? false,
            'estimated_duration_days' => $this->estimated_duration_days,
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
            'usage_percentage' => $this->getUsagePercentage(),
            'is_at_limit' => $this->isAtLimit(),
            'average_time_in_stage' => $this->getAverageTimeInStage(),
            'completion_rate' => $this->getCompletionRate(),
        ];
    }

    /**
     * Obter percentual de uso baseado no limite.
     */
    public function getUsagePercentage(): ?float
    {
        if (!$this->hasMaxItems()) {
            return null;
        }

        return round(($this->getCurrentCount() / $this->max_items) * 100, 1);
    }

    /**
     * Obter tempo médio que itens ficam nesta etapa.
     */
    public function getAverageTimeInStage(): ?float
    {
        // Implementação para calcular tempo médio na etapa
        return null; // Por enquanto
    }

    /**
     * Obter taxa de conclusão da etapa.
     */
    public function getCompletionRate(): float
    {
        // Implementação para calcular taxa de conclusão
        return 0.0; // Por enquanto
    }

    /**
     * Boot do model.
     */
    protected static function boot()
    {
        parent::boot();

        // Definir sort_order automaticamente
        static::creating(function (WorkflowTemplate $template) {
            if (is_null($template->sort_order)) {
                $maxOrder = static::where('workflow_id', $template->workflow_id)->max('sort_order');
                $template->sort_order = ($maxOrder ?? 0) + 1;
            }
        });

        // Auto-gerar slug único no workflow
        static::creating(function (WorkflowTemplate $template) {
            if (empty($template->slug)) {
                $template->slug = $template->generateUniqueSlug();
            }
        });
    }

    /**
     * Gerar slug único dentro do workflow.
     */
    protected function generateUniqueSlug(): string
    {
        $baseSlug = Str::slug($this->name);
        $counter = 1;

        while (static::where('workflow_id', $this->workflow_id)
            ->where('slug', $baseSlug)
            ->exists()
        ) {
            $baseSlug = Str::slug($this->name) . '-' . $counter;
            $counter++;
        }

        return $baseSlug;
    }

    /**
     * Obter fonte para geração do slug.
     */
    protected function getSlugSource(): string
    {
        return $this->name;
    }
}

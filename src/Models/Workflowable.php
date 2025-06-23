<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Builder;
use Callcocam\ReactPapaLeguas\Landlord\BelongsToTenants;

/**
 * Workflowable Model - Relacionamento polimórfico entre workflows e entidades.
 * 
 * Este model conecta qualquer entidade (tickets, leads, etc) com workflows,
 * mantendo o estado atual e progresso do workflow.
 * 
 * @property string $id
 * @property string $workflowable_type
 * @property string $workflowable_id
 * @property string $workflow_id
 * @property string|null $current_template_id
 * @property array|null $workflow_data
 * @property \Carbon\Carbon|null $started_at
 * @property \Carbon\Carbon|null $completed_at
 * @property string $status
 * @property int $current_step
 * @property int|null $total_steps
 * @property float $progress_percentage
 * @property string|null $assigned_to
 * @property \Carbon\Carbon|null $assigned_at
 * @property \Carbon\Carbon|null $due_at
 * @property \Carbon\Carbon|null $escalated_at
 * @property int $time_spent_minutes
 * @property array|null $metadata
 * @property string|null $notes
 * @property string|null $user_id
 * @property string|null $tenant_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class Workflowable extends Model
{
    use HasUlids, SoftDeletes, BelongsToTenants;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'workflowable_type',
        'workflowable_id',
        'workflow_id',
        'current_template_id',
        'workflow_data',
        'started_at',
        'completed_at',
        'status',
        'current_step',
        'total_steps',
        'progress_percentage',
        'assigned_to',
        'assigned_at',
        'due_at',
        'escalated_at',
        'time_spent_minutes',
        'metadata',
        'notes',
        'user_id',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'workflow_data' => 'array',
        'metadata' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'due_at' => 'datetime',
        'escalated_at' => 'datetime',
        'current_step' => 'integer',
        'total_steps' => 'integer',
        'progress_percentage' => 'decimal:2',
        'time_spent_minutes' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     */
    protected $appends = [
        'kanban_data',
        'assigned_user_name',
        'status_formatted',
        'time_spent_formatted',
    ];

    /**
     * Get the workflowable entity (polymorphic).
     */
    public function workflowable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the workflow.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get the current template.
     */
    public function currentTemplate(): BelongsTo
    {
        return $this->belongsTo(WorkflowTemplate::class, 'current_template_id');
    }

    /**
     * Get the assigned user.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who started the workflow.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para workflowables ativos.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para workflowables concluídos.
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para workflowables pausados.
     */
    public function scopePaused(Builder $query): Builder
    {
        return $query->where('status', 'paused');
    }

    /**
     * Scope para workflowables cancelados.
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope para workflowables vencidos.
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->whereNotNull('due_at')
            ->where('due_at', '<', now())
            ->whereIn('status', ['active', 'paused']);
    }

    /**
     * Scope para workflowables atribuídos a um usuário.
     */
    public function scopeAssignedTo(Builder $query, string $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope para um workflow específico.
     */
    public function scopeForWorkflow(Builder $query, string $workflowId): Builder
    {
        return $query->where('workflow_id', $workflowId);
    }

    /**
     * Scope para um template específico.
     */
    public function scopeInTemplate(Builder $query, string $templateId): Builder
    {
        return $query->where('current_template_id', $templateId);
    }

    /**
     * Scope para carregar relacionamentos necessários para o Kanban.
     */
    public function scopeWithKanbanRelations(Builder $query): Builder
    {
        return $query->with([
            'assignedUser:id,name,email',
            'workflow:id,name,slug',
            'currentTemplate:id,name,color,icon,sort_order',
        ]);
    }

    /**
     * Verificar se está ativo.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Verificar se está concluído.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verificar se está pausado.
     */
    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    /**
     * Verificar se está cancelado.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Verificar se está vencido.
     */
    public function isOverdue(): bool
    {
        return $this->due_at &&
            $this->due_at->isPast() &&
            in_array($this->status, ['active', 'paused']);
    }

    /**
     * Verificar se está atribuído.
     */
    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to);
    }

    /**
     * Mover para próximo template.
     */
    public function moveToTemplate(WorkflowTemplate $template, array $data = []): bool
    {
        // Verificar se a transição é permitida
        if ($this->currentTemplate && !$this->currentTemplate->canTransitionTo($template)) {
            return false;
        }

        $oldTemplateId = $this->current_template_id;

        $this->update(array_merge([
            'current_template_id' => $template->id,
            'current_step' => $template->sort_order,
            'progress_percentage' => $this->calculateProgress($template),
        ], $data));

        // Auto-atribuir se configurado
        if ($template->auto_assign && !$this->assigned_to) {
            $this->autoAssign();
        }

        // Log da transição
        $this->logTransition($oldTemplateId, $template->id);

        return true;
    }

    /**
     * Calcular progresso baseado no template atual.
     */
    protected function calculateProgress(WorkflowTemplate $template): float
    {
        if (!$this->total_steps || $this->total_steps === 0) {
            return 0;
        }

        return round(($template->sort_order / $this->total_steps) * 100, 2);
    }

    /**
     * Auto-atribuir baseado em regras.
     */
    protected function autoAssign(): void
    {
        // TODO: Implementar lógica de auto-atribuição
        // Pode ser baseado em carga de trabalho, especialização, etc.
    }

    /**
     * Log da transição entre templates.
     */
    protected function logTransition(?string $fromTemplateId, string $toTemplateId): void
    {
        // TODO: Implementar log de transições para auditoria
    }

    /**
     * Atribuir a um usuário.
     */
    public function assignTo(string $userId, array $data = []): bool
    {
        return $this->update(array_merge([
            'assigned_to' => $userId,
            'assigned_at' => now(),
        ], $data));
    }

    /**
     * Remover atribuição.
     */
    public function unassign(): bool
    {
        return $this->update([
            'assigned_to' => null,
            'assigned_at' => null,
        ]);
    }

    /**
     * Pausar workflow.
     */
    public function pause(?string $reason = null): bool
    {
        $metadata = $this->metadata ?? [];
        $metadata['paused_reason'] = $reason;
        $metadata['paused_at'] = now()->toISOString();

        return $this->update([
            'status' => 'paused',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Retomar workflow.
     */
    public function resume(): bool
    {
        $metadata = $this->metadata ?? [];
        $metadata['resumed_at'] = now()->toISOString();

        return $this->update([
            'status' => 'active',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Cancelar workflow.
     */
    public function cancel(?string $reason = null): bool
    {
        $metadata = $this->metadata ?? [];
        $metadata['cancelled_reason'] = $reason;
        $metadata['cancelled_at'] = now()->toISOString();

        return $this->update([
            'status' => 'cancelled',
            'metadata' => $metadata,
        ]);
    }

    /**
     * Completar workflow.
     */
    public function complete(array $data = []): bool
    {
        return $this->update(array_merge([
            'status' => 'completed',
            'completed_at' => now(),
            'progress_percentage' => 100,
        ], $data));
    }

    /**
     * Obter tempo gasto em formato legível.
     */
    public function getTimeSpentFormatted(): string
    {
        if ($this->time_spent_minutes < 60) {
            return $this->time_spent_minutes . 'm';
        }

        $hours = floor($this->time_spent_minutes / 60);
        $minutes = $this->time_spent_minutes % 60;

        if ($hours < 24) {
            return $hours . 'h' . ($minutes > 0 ? ' ' . $minutes . 'm' : '');
        }

        $days = floor($hours / 24);
        $remainingHours = $hours % 24;

        return $days . 'd' . ($remainingHours > 0 ? ' ' . $remainingHours . 'h' : '');
    }

    /**
     * Obter nome do responsável atribuído.
     */
    public function getAssignedUserName(): string
    {
        return $this->assignedUser?->name ?? 'Não atribuído';
    }

    /**
     * Obter título formatado para o Kanban.
     */
    public function getKanbanTitle(): string
    {
        $metadata = $this->metadata ?? [];

        // Prioridade: título dos metadados > categoria + número > ID
        if (!empty($metadata['title'])) {
            return $metadata['title'];
        }

        if (!empty($metadata['ticket_number'])) {
            $category = $metadata['category'] ?? 'Item';
            return "{$category} - {$metadata['ticket_number']}";
        }

        return "Workflow " . substr($this->id, -4);
    }

    /**
     * Obter status formatado para exibição.
     */
    public function getStatusFormatted(): string
    {
        return match ($this->status) {
            'active' => 'Ativo',
            'completed' => 'Concluído',
            'paused' => 'Pausado',
            'cancelled' => 'Cancelado',
            default => ucfirst($this->status)
        };
    }

    /**
     * Obter cor do status para o frontend.
     */
    public function getStatusColor(): string
    {
        return match ($this->status) {
            'active' => '#3b82f6',      // Azul
            'completed' => '#10b981',   // Verde
            'paused' => '#f59e0b',      // Amarelo
            'cancelled' => '#6b7280',   // Cinza
            default => '#3b82f6'
        };
    }

    /**
     * Obter cor da prioridade baseada nos metadados.
     */
    public function getPriorityColor(): string
    {
        $priority = $this->metadata['priority'] ?? 'Normal';

        return match (strtolower($priority)) {
            'crítica', 'critical' => '#dc2626',     // Vermelho
            'alta', 'high' => '#ea580c',            // Laranja
            'média', 'medium' => '#d97706',         // Amarelo escuro
            'baixa', 'low' => '#16a34a',            // Verde
            default => '#6b7280'                    // Cinza
        };
    }

    /**
     * Verificar se é urgente baseado nos metadados.
     */
    public function isUrgent(): bool
    {
        $metadata = $this->metadata ?? [];
        return $metadata['is_urgent'] === true || $metadata['is_urgent'] === 'true';
    }

    /**
     * Verificar se é interno baseado nos metadados.
     */
    public function isInternal(): bool
    {
        $metadata = $this->metadata ?? [];
        return $metadata['is_internal'] === true || $metadata['is_internal'] === 'true';
    }

    /**
     * Obter dados formatados para o Kanban (frontend).
     */
    public function getKanbanData(): array
    {
        $metadata = $this->metadata ?? [];

        return [
            // Identificadores
            'workflow_id' => $this->id,
            'workflowable_id' => $this->workflowable_id,
            'workflowable_type' => $this->workflowable_type,

            // Dados principais
            'title' => $this->getKanbanTitle(),
            'ticket_number' => $metadata['ticket_number'] ?? $this->workflowable_id,
            'status' => $this->getStatusFormatted(),
            'status_raw' => $this->status,
            'status_color' => $this->getStatusColor(),

            // Prioridade e categoria
            'priority' => $metadata['priority'] ?? 'Normal',
            'priority_color' => $this->getPriorityColor(),
            'category' => $metadata['category'] ?? 'Sem categoria',

            // Responsável
            'assigned_to' => $this->getAssignedUserName(),
            'assigned_to_id' => $this->assigned_to,
            'assigned_at' => $this->assigned_at?->format('d/m/Y H:i'),

            // Progresso
            'progress_percentage' => (float) $this->progress_percentage,
            'current_step' => $this->current_step,
            'total_steps' => $this->total_steps,
            'progress_text' => "{$this->current_step}/{$this->total_steps}",

            // Timing
            'time_spent' => $this->getTimeSpentFormatted(),
            'time_spent_minutes' => $this->time_spent_minutes,
            'due_at' => $this->due_at?->format('d/m/Y'),
            'due_at_raw' => $this->due_at,
            'started_at' => $this->started_at?->format('d/m/Y H:i'),
            'completed_at' => $this->completed_at?->format('d/m/Y H:i'),

            // Flags
            'is_overdue' => $this->isOverdue(),
            'is_urgent' => $this->isUrgent(),
            'is_internal' => $this->isInternal(),
            'is_assigned' => $this->isAssigned(),

            // Metadados adicionais
            'contact_email' => $metadata['contact_email'] ?? null,
            'notes' => $this->notes,
            'metadata' => $metadata,
        ];
    }

    /**
     * Accessor para kanban_data.
     */
    public function getKanbanDataAttribute(): array
    {
        return $this->getKanbanData();
    }

    /**
     * Accessor para assigned_user_name.
     */
    public function getAssignedUserNameAttribute(): string
    {
        return $this->getAssignedUserName();
    }

    /**
     * Accessor para status_formatted.
     */
    public function getStatusFormattedAttribute(): string
    {
        return $this->getStatusFormatted();
    }

    /**
     * Accessor para time_spent_formatted.
     */
    public function getTimeSpentFormattedAttribute(): string
    {
        return $this->getTimeSpentFormatted();
    }

    /**
     * Boot do model.
     */
    protected static function boot()
    {
        parent::boot();

        // Auto-set user_id on creating
        static::creating(function (Workflowable $workflowable) {
            if (auth()->check() && !$workflowable->user_id) {
                $workflowable->user_id = auth()->id();
            }

            // Calcular total_steps baseado no workflow
            if (!$workflowable->total_steps && $workflowable->workflow_id) {
                $workflow = Workflow::find($workflowable->workflow_id);
                $workflowable->total_steps = $workflow?->activeTemplates()->count() ?? 1;
            }
        });
    }
}

<?php

namespace Callcocam\ReactPapaLeguas\Tables;

use Callcocam\ReactPapaLeguas\Enums\BaseStatus;
use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\CompoundColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\TextFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\SelectFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\DateRangeFilter;
use Callcocam\ReactPapaLeguas\Models\Workflowable;
use Illuminate\Support\Str;

/**
 * Tabela de Workflowables - Itens em Workflows
 * 
 * Gerencia todos os itens que estão passando por workflows,
 * independente do tipo (tickets, leads, vendas, etc).
 * 
 * FUNCIONALIDADES:
 * ✅ Visualização polimórfica de todos os tipos
 * ✅ Progresso e status atual em tempo real
 * ✅ SLA e prazos com alertas
 * ✅ Atribuições e responsáveis
 * ✅ Histórico de transições
 */
class WorkflowableTable extends Table
{
    /**
     * Configurar a tabela
     */
    protected function setUp(): void
    {
        $this->model(Workflowable::class)
            ->query(function () {
                return Workflowable::query()
                    ->with(['workflowable', 'workflow', 'currentTemplate', 'user'])
                    ->select([
                        'id',
                        'workflowable_type',
                        'workflowable_id',
                        'workflow_id',
                        'current_template_id',
                        'started_at',
                        'completed_at',
                        'current_step',
                        'total_steps',
                        'progress_percentage',
                        'status',
                        'user_id',
                        'created_at',
                    ]);
            })
            ->searchable()
            ->sortable()
            ->filterable()
            ->paginated()
            ->meta([
                'title' => 'Itens em Workflow',
                'description' => 'Monitore itens em processos',
            ]);
    }

    /**
     * Define as colunas da tabela
     */
    protected function columns(): array
    {
        return [
            CompoundColumn::make('workflowable_type', 'Item')
                ->title('workflowable_type')
                ->subtitle('workflowable_id')
                ->width('150px')
                ->formatUsing(function ($value, $record) {
                    $typeName = class_basename($value);
                    return [
                        'title' => $typeName . ' #' . $record->workflowable_id,
                        'subtitle' => 'Item do sistema',
                    ];
                }),

            TextColumn::make('workflow.name', 'Workflow')
                ->searchable()
                ->sortable()
                ->width('140px'),

            BadgeColumn::make('currentTemplate.name', 'Etapa Atual')
                ->sortable()
                ->width('120px')
                ->placeholder('Não iniciado'),

            TextColumn::make('progress_percentage', 'Progresso')
                ->sortable()
                ->width('100px')
                ->alignment('center')
                ->formatUsing(function ($value, $record) {
                    return ($value ?: 0) . '%';
                }),

            BadgeColumn::make('status', 'Status')
                ->sortable()
                ->width('100px'),

            DateColumn::make('started_at', 'Iniciado em')
                ->dateFormat('d/m/Y')
                ->since()
                ->sortable()
                ->width('120px'),

            DateColumn::make('completed_at', 'Concluído em')
                ->dateFormat('d/m/Y')
                ->since()
                ->sortable()
                ->width('120px')
                ->placeholder('Em andamento'),
        ];
    }

    /**
     * Define os filtros da tabela
     */
    protected function filters(): array
    {
        return [
            TextFilter::make('search')
                ->label('Buscar Item')
                ->placeholder('Digite ID...')
                ->searchColumns(['workflowable_id'])
                ->minLength(1),

            SelectFilter::make('workflow_id')
                ->label('Workflow')
                ->placeholder('Selecione um workflow')
                ->relationship('workflow', 'name'),

            SelectFilter::make('status')
                ->label('Status')
                ->placeholder('Selecione um status')
                ->options([
                    'active' => 'Ativo',
                    'completed' => 'Concluído',
                    'cancelled' => 'Cancelado',
                ]),

            DateRangeFilter::make('started_at')
                ->label('Data de Início')
                ->brazilian()
                ->dateOnly(),
        ];
    }
} 
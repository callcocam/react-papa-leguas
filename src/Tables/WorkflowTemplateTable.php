<?php

namespace Callcocam\ReactPapaLeguas\Tables;

use Callcocam\ReactPapaLeguas\Enums\BaseStatus;
use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BooleanColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\CompoundColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\TextFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\SelectFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\BooleanFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\DateRangeFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\StatusCast;
use Callcocam\ReactPapaLeguas\Models\WorkflowTemplate;
use Illuminate\Support\Str;

/**
 * Tabela de Templates de Workflow - Etapas/Colunas Kanban
 * 
 * Gerencia as etapas individuais de cada workflow, definindo
 * as colunas que aparecerão no Kanban e suas regras.
 * 
 * FUNCIONALIDADES:
 * ✅ Gestão de etapas por workflow
 * ✅ Configuração visual de colunas Kanban
 * ✅ Regras de transição e limites
 * ✅ Instruções para usuários
 * ✅ Ordenação drag-and-drop
 */
class WorkflowTemplateTable extends Table
{
    /**
     * Configurar a tabela
     */
    protected function setUp(): void
    {
        // Configurar fonte de dados
        $this->model(WorkflowTemplate::class)
            ->query(function () {
                return WorkflowTemplate::query()
                    ->withCount(['workflowables'])
                    ->with(['workflow', 'user'])
                    ->select([
                        'id',
                        'workflow_id',
                        'name',
                        'slug',
                        'description',
                        'instructions',
                        'category',
                        'color',
                        'icon',
                        'max_items',
                        'auto_assign',
                        'requires_approval',
                        'sort_order',
                        'status',
                        'user_id',
                        'tenant_id',
                        'created_at',
                        'updated_at',
                    ]);
            })
            ->searchable()
            ->sortable()
            ->filterable()
            ->paginated()
            ->selectable()
            ->meta([
                'title' => 'Templates de Workflow',
                'description' => 'Gerencie etapas e colunas dos workflows Kanban',
            ]);
    }

    /**
     * Define as colunas da tabela
     */
    protected function columns(): array
    {
        return [
            // Workflow pai
            TextColumn::make('workflow.name', 'Workflow')
                ->searchable()
                ->sortable()
                ->width('150px')
                ->formatUsing(function ($value, $record) {
                    return [
                        'type' => 'link',
                        'label' => $value,
                        'subtitle' => $record->workflow->category ?? 'Geral',
                        'color' => 'primary',
                    ];
                }),

            // Nome da etapa com ícone
            CompoundColumn::make('name', 'Etapa')
                ->icon('icon')
                ->title('name')
                ->subtitle('description')
                ->searchable()
                ->sortable()
                ->limit(30)
                ->formatUsing(function ($value, $record) {
                    return [
                        'icon' => $record->icon ?: 'Circle',
                        'icon_color' => $record->color ?: '#6b7280',
                        'title' => $record->name,
                        'subtitle' => $record->description ? 
                            Str::limit($record->description, 50) : 
                            'Sem descrição',
                    ];
                }),

            // Slug único dentro do workflow
            TextColumn::make('slug', 'Slug')
                ->searchable()
                ->sortable()
                ->copyable()
                ->width('100px')
                ->placeholder('Auto-gerado'),

            // Categoria da etapa
            BadgeColumn::make('category', 'Categoria')
                ->sortable()
                ->width('110px')
                ->formatUsing(function ($value, $record) {
                    $categories = [
                        'initial' => ['label' => 'Inicial', 'color' => 'blue'],
                        'progress' => ['label' => 'Em Progresso', 'color' => 'yellow'],
                        'review' => ['label' => 'Revisão', 'color' => 'purple'],
                        'approval' => ['label' => 'Aprovação', 'color' => 'orange'],
                        'final' => ['label' => 'Final', 'color' => 'green'],
                        'blocked' => ['label' => 'Bloqueado', 'color' => 'red'],
                    ];

                    $category = $categories[$value] ?? ['label' => $value ?: 'Geral', 'color' => 'gray'];

                    return [
                        'type' => 'badge',
                        'label' => $category['label'],
                        'color' => $category['color'],
                    ];
                })
                ->placeholder('Geral'),

            // Ordem de exibição
            TextColumn::make('sort_order', 'Ordem')
                ->sortable()
                ->width('70px')
                ->alignment('center')
                ->formatUsing(function ($value, $record) {
                    return [
                        'formatted' => '#' . $value,
                        'color' => 'secondary',
                        'subtitle' => 'Posição',
                    ];
                }),

            // Itens atualmente nesta etapa
            TextColumn::make('workflowables_count', 'Itens')
                ->sortable()
                ->width('80px')
                ->alignment('center')
                ->formatUsing(function ($value, $record) {
                    $color = 'secondary';
                    if ($value > 0) {
                        if ($record->max_items && $value >= $record->max_items) {
                            $color = 'destructive'; // Limite atingido
                        } else {
                            $color = 'success';
                        }
                    }

                    return [
                        'type' => 'badge',
                        'label' => $value . ' item' . ($value != 1 ? 's' : ''),
                        'color' => $color,
                        'subtitle' => $record->max_items ? 
                            'Máx: ' . $record->max_items : 
                            'Sem limite',
                    ];
                }),

            // Limite máximo de itens
            TextColumn::make('max_items', 'Limite')
                ->sortable()
                ->width('80px')
                ->alignment('center')
                ->formatUsing(function ($value, $record) {
                    if (!$value) return ['formatted' => 'Ilimitado', 'color' => 'secondary'];
                    
                    $current = $record->workflowables_count ?? 0;
                    $percentage = $current > 0 ? ($current / $value) * 100 : 0;
                    
                    return [
                        'formatted' => $value . ' máx',
                        'color' => $percentage >= 100 ? 'destructive' : 
                                  ($percentage >= 80 ? 'warning' : 'success'),
                        'subtitle' => $percentage > 0 ? 
                            round($percentage) . '% usado' : 
                            'Disponível',
                    ];
                })
                ->placeholder('Ilimitado'),

            // Auto-assign (atribuição automática)
            BooleanColumn::make('auto_assign', 'Auto-Assign')
                ->trueLabel('Sim')
                ->falseLabel('Não')
                ->trueIcon('UserCheck')
                ->falseIcon('UserX')
                ->trueColor('success')
                ->falseColor('secondary')
                ->displayAs('badge')
                ->width('100px'),

            // Requer aprovação
            BooleanColumn::make('requires_approval', 'Aprovação')
                ->trueLabel('Obrigatória')
                ->falseLabel('Opcional')
                ->trueIcon('Shield')
                ->falseIcon('ShieldOff')
                ->trueColor('warning')
                ->falseColor('secondary')
                ->displayAs('badge')
                ->width('100px'),

            // Status
            BadgeColumn::make('status', 'Status')
                ->sortable()
                ->width('100px')
                ->cast(
                    StatusCast::make()
                        ->formatType('badge')
                        ->variants([
                            BaseStatus::Published->value => 'success',
                            BaseStatus::Draft->value => 'warning',
                            BaseStatus::Archived->value => 'secondary',
                        ])
                        ->labels([
                            BaseStatus::Published->value => 'Ativo',
                            BaseStatus::Draft->value => 'Rascunho',
                            BaseStatus::Archived->value => 'Arquivado',
                        ])
                ),

            // Instruções para usuários (tooltip)
            TextColumn::make('instructions', 'Instruções')
                ->searchable()
                ->width('120px')
                ->limit(30)
                ->formatUsing(function ($value, $record) {
                    if (!$value) return ['formatted' => 'Sem instruções', 'color' => 'secondary'];
                    
                    return [
                        'formatted' => Str::limit($value, 30),
                        'color' => 'primary',
                        'tooltip' => $value,
                        'subtitle' => 'Clique para ver',
                    ];
                })
                ->placeholder('Sem instruções'),

            // Criador
            TextColumn::make('user.name', 'Criado por')
                ->searchable()
                ->sortable()
                ->width('120px')
                ->placeholder('Sistema'),

            // Data de criação
            DateColumn::make('created_at', 'Criado em')
                ->dateFormat('d/m/Y')
                ->since()
                ->sortable()
                ->width('120px'),

            // Data de atualização (oculta)
            DateColumn::make('updated_at', 'Atualizado em')
                ->dateFormat('d/m/Y H:i')
                ->since()
                ->sortable()
                ->hidden(),
        ];
    }

    /**
     * Define os filtros da tabela
     */
    protected function filters(): array
    {
        return [
            TextFilter::make('search')
                ->label('Buscar Template')
                ->placeholder('Digite nome, descrição ou instruções...')
                ->searchColumns(['name', 'description', 'instructions', 'slug'])
                ->operator('LIKE')
                ->caseSensitive(false)
                ->minLength(2),

            SelectFilter::make('workflow_id')
                ->label('Workflow')
                ->placeholder('Selecione um workflow')
                ->relationship('workflow', 'name')
                ->searchable(),

            SelectFilter::make('category')
                ->label('Categoria')
                ->placeholder('Selecione uma categoria')
                ->options([
                    'initial' => 'Inicial',
                    'progress' => 'Em Progresso',
                    'review' => 'Revisão',
                    'approval' => 'Aprovação',
                    'final' => 'Final',
                    'blocked' => 'Bloqueado',
                ]),

            SelectFilter::make('status')
                ->label('Status')
                ->placeholder('Selecione um status')
                ->options([
                    BaseStatus::Published->value => 'Ativo',
                    BaseStatus::Draft->value => 'Rascunho',
                    BaseStatus::Archived->value => 'Arquivado',
                ]),

            BooleanFilter::make('auto_assign')
                ->label('Auto-Assign')
                ->labels('Com Auto-Assign', 'Sem Auto-Assign', 'Todos'),

            BooleanFilter::make('requires_approval')
                ->label('Requer Aprovação')
                ->labels('Requer Aprovação', 'Sem Aprovação', 'Todos'),

            DateRangeFilter::make('created_at')
                ->label('Data de Criação')
                ->brazilian()
                ->dateOnly(),
        ];
    }
} 
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
use Callcocam\ReactPapaLeguas\Models\Workflow;
use Illuminate\Support\Str;

/**
 * Tabela de Workflows - Sistema de Processos de Negócio
 * 
 * Gerencia workflows genéricos que podem ser aplicados a qualquer entidade
 * (tickets, leads, vendas, RH, etc) fornecendo colunas para Kanban.
 * 
 * FUNCIONALIDADES:
 * ✅ Visualização de workflows com templates associados
 * ✅ Estatísticas de uso e performance
 * ✅ Configuração visual (cores, ícones)
 * ✅ Filtros por categoria e status
 * ✅ Gestão de ativação/desativação
 */
class WorkflowTable extends Table
{
    /**
     * Configurar a tabela
     */
    protected function setUp(): void
    {
        // Configurar fonte de dados
        $this->model(Workflow::class)
            ->query(function () {
                return Workflow::query()
                    ->withCount(['templates', 'workflowables'])
                    ->with(['user'])
                    ->select([
                        'id',
                        'name',
                        'slug',
                        'description',
                        'category',
                        'color',
                        'icon',
                        'estimated_duration_days',
                        'is_required_by_default',
                        'is_active',
                        'is_featured',
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
                'title' => 'Workflows',
                'description' => 'Gerencie processos de negócio e fluxos Kanban',
            ]);
    }

    /**
     * Define as colunas da tabela
     */
    protected function columns(): array
    {
        return [
            // Nome com ícone e cor
            // CompoundColumn::make('name', 'Workflow')
            //     ->icon('icon')
            //     ->title('name') 
            //     ->searchable()
            //     ->sortable()
            //     ->limit(40)
            //     ->formatUsing(function (Workflow $record, $value) {
            //         return [
            //             'icon' => $record->icon ?: 'Workflow',
            //             'icon_color' => $record->color ?: '#6b7280',
            //             'title' => $record->name,
            //             'subtitle' => $record->description ? 
            //                 Str::limit($record->description, 60) : 
            //                 'Sem descrição',
            //         ];
            //     }),

            // Slug para URLs
            TextColumn::make('name', 'Nome')
                ->searchable()
                ->sortable()
                ->copyable()
                ->width('120px')
                ->placeholder('Não definido'),

            // Slug para URLs
            TextColumn::make('slug', 'Identificador')
                ->searchable()
                ->sortable()
                ->copyable()
                ->width('120px')
                ->placeholder('Não definido'),

            // Categoria com badge colorido
            BadgeColumn::make('category', 'Categoria')
                ->sortable()
                ->width('120px')
                ->formatUsing(function (Workflow $record, $value) {
                    $categories = [
                        'support' => ['label' => 'Suporte', 'color' => 'blue'],
                        'sales' => ['label' => 'Vendas', 'color' => 'green'],
                        'hr' => ['label' => 'RH', 'color' => 'purple'],
                        'finance' => ['label' => 'Financeiro', 'color' => 'yellow'],
                        'development' => ['label' => 'Desenvolvimento', 'color' => 'indigo'],
                        'marketing' => ['label' => 'Marketing', 'color' => 'pink'],
                    ];

                    $category = $categories[$value] ?? ['label' => $value ?: 'Geral', 'color' => 'gray'];

                    return [
                        'type' => 'badge',
                        'label' => $category['label'],
                        'color' => $category['color'],
                    ];
                }) ,

            // Templates count com link para gestão
            TextColumn::make('templates_count', 'Etapas')
                ->sortable()
                ->width('80px')
                ->alignment('center')
                ->formatUsing(function (Workflow $record, $value) {
                    return [
                        'type' => 'badge',
                        'label' => $value . ' etapa' . ($value != 1 ? 's' : ''),
                        'color' => $value > 0 ? 'primary' : 'secondary',
                        'subtitle' => $value > 0 ? 'Configurado' : 'Sem etapas',
                    ];
                }),

            // Workflowables count (uso)
            TextColumn::make('workflowables_count', 'Em Uso')
                ->sortable()
                ->width('80px')
                ->alignment('center')
                ->formatUsing(function (Workflow $record, $value) {
                    return [
                        'type' => 'badge',
                        'label' => $value . ' item' . ($value != 1 ? 's' : ''),
                        'color' => $value > 0 ? 'success' : 'secondary',
                        'subtitle' => $value > 0 ? 'Ativo' : 'Não usado',
                    ];
                }),

            // Duração estimada
            TextColumn::make('estimated_duration_days', 'Duração')
                ->sortable()
                ->width('100px')
                ->alignment('center')
                // ->formatUsing(function (Workflow $record, $value) {
                //     if (!$value) return ['formatted' => 'Não definida', 'color' => 'secondary'];
                //     dd($value);
                //     return [
                //         'formatted' => $value . ' dia' . ($value != 1 ? 's' : ''),
                //         'color' => $value <= 7 ? 'success' : ($value <= 30 ? 'warning' : 'destructive'),
                //         'subtitle' => $value <= 7 ? 'Rápido' : ($value <= 30 ? 'Médio' : 'Longo'),
                //     ];
                // })
                ->placeholder('Não definida'),

            // Flags importantes
            BooleanColumn::make('is_required_by_default', 'Obrigatório')
                // ->trueLabel('Sim')
                // ->falseLabel('Não')
                ->yesNo()
                // ->trueIcon('Lock')
                // ->falseIcon('Unlock')
                // ->trueColor('warning')
                // ->falseColor('secondary')
                // ->displayAs('badge')
                ->width('100px')
                ->alignment('center'),

            BooleanColumn::make('is_active', 'Ativo')
                ->yesNo()
                ->width('80px')
                ->alignment('center'),

            BooleanColumn::make('is_featured', 'Destaque')
                ->yesNo()
                ->width('90px')
                ->alignment('center'),

            // Sort order
            TextColumn::make('sort_order', 'Ordem')
                ->sortable()
                ->width('70px')
                ->alignment('center')
                ->formatUsing(function (Workflow $record, $value) {
                    return [
                        'formatted' => '#' . data_get($record, 'value'),
                        'color' => 'secondary',
                    ];
                }),

            // Status com badge
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
                            BaseStatus::Published->value => 'Publicado',
                            BaseStatus::Draft->value => 'Rascunho',
                            BaseStatus::Archived->value => 'Arquivado',
                        ])
                ),

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

            // Data de atualização (oculta por padrão)
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
                ->label('Buscar Workflow')
                ->placeholder('Digite nome, descrição ou slug...')
                ->searchColumns(['name', 'description', 'slug'])
                ->operator('LIKE')
                ->caseSensitive(false)
                ->minLength(2),

            SelectFilter::make('category')
                ->label('Categoria')
                ->placeholder('Selecione uma categoria')
                ->options([
                    'support' => 'Suporte',
                    'sales' => 'Vendas',
                    'hr' => 'Recursos Humanos',
                    'finance' => 'Financeiro',
                    'development' => 'Desenvolvimento',
                    'marketing' => 'Marketing',
                    'operations' => 'Operações',
                    'legal' => 'Jurídico',
                ]),

            SelectFilter::make('status')
                ->label('Status')
                ->placeholder('Selecione um status')
                ->options([
                    BaseStatus::Published->value => 'Publicado',
                    BaseStatus::Draft->value => 'Rascunho',
                    BaseStatus::Archived->value => 'Arquivado',
                ]),

            BooleanFilter::make('is_active')
                ->label('Apenas Ativos')
                ->labels('Ativos', 'Inativos', 'Todos'),

            BooleanFilter::make('is_featured')
                ->label('Em Destaque')
                ->labels('Destacados', 'Normais', 'Todos'),

            BooleanFilter::make('is_required_by_default')
                ->label('Obrigatórios')
                ->labels('Obrigatórios', 'Opcionais', 'Todos'),

            DateRangeFilter::make('created_at')
                ->label('Data de Criação')
                ->brazilian()
                ->dateOnly(),
        ];
    }

    protected function actions(): array
    {
        return [
            $this->editAction('admin.workflows.edit')
                ->label('Editar')
                ->icon('Pencil')
                ->tooltip('Editar workflow')
        ];
    }
} 
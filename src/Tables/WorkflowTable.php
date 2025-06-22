<?php

namespace Callcocam\ReactPapaLeguas\Tables;

use Callcocam\ReactPapaLeguas\Enums\BaseStatus;
use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\TextFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\SelectFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\DateRangeFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\StatusCast;
use Callcocam\ReactPapaLeguas\Models\Workflow;

/**
 * Tabela de Workflows - Sistema Simplificado de Processos de Negócio
 * 
 * Gerencia workflows genéricos simplificados que podem ser aplicados a qualquer entidade
 * (tickets, leads, vendas, RH, etc). Configurações visuais estão nos templates.
 * 
 * FUNCIONALIDADES:
 * ✅ Visualização de workflows com templates associados
 * ✅ Estatísticas de uso e performance
 * ✅ Filtros por status
 * ✅ Gestão via BaseStatus enum
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
            // Nome do workflow
            TextColumn::make('name', 'Nome')
                ->searchable()
                ->sortable()
                ->copyable()
                ->width('150px')
                ->placeholder('Não definido'),

            // Slug para URLs
            TextColumn::make('slug', 'Identificador')
                ->searchable()
                ->sortable()
                ->copyable()
                ->width('120px')
                ->placeholder('Não definido'),

            // Descrição
            TextColumn::make('description', 'Descrição')
                ->searchable()
                ->width('200px')
                ->limit(60)
                ->placeholder('Sem descrição'),

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

            SelectFilter::make('status')
                ->label('Status')
                ->placeholder('Selecione um status')
                ->options([
                    BaseStatus::Published->value => 'Publicado',
                    BaseStatus::Draft->value => 'Rascunho',
                    BaseStatus::Archived->value => 'Arquivado',
                ]),

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
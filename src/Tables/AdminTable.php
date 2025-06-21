<?php

namespace Callcocam\ReactPapaLeguas\Tables;

use App\Models\User;
use Callcocam\ReactPapaLeguas\Enums\BaseStatus;
use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BooleanColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\TextFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\SelectFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\BooleanFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\DateRangeFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\DateCast;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\StatusCast;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\ClosureCast;
use Callcocam\ReactPapaLeguas\Models\Admin as AdminModel;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\CompoundColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\EditableColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\NestedTableColumn;
use Illuminate\Support\Str;

/**
 * Tabela de usuários com Sistema de Filtros Avançados
 * 
 * FUNCIONALIDADES IMPLEMENTADAS:
 * ✅ Sistema de Casts Automático por tipo de coluna
 * ✅ Casts específicos para transformações personalizadas
 * ✅ Sistema de Filtros Avançados com classes especializadas
 * ✅ Pipeline de transformação: Dados → Casts → Formatação → Frontend
 * ✅ Detecção automática baseada em padrões de dados
 * ✅ Configuração flexível por coluna e filtro
 */
class AdminTable extends Table
{
    /**
     * Configurar a tabela
     */
    protected function setUp(): void
    {
        // Definir prefixo personalizado das rotas
        $this->setRoutePrefix('landlord.users');

        // Configurar fonte de dados
        $this->model(AdminModel::class)
            ->query(function () {
                // Garante que todos os campos necessários, incluindo o email, sejam selecionados.
                return AdminModel::query()
                    ->select([
                        'id',
                        'name',
                        'email',
                        'status',
                        'email_verified_at',
                        'created_at',
                        'updated_at',
                    ]); // Adiciona contagem de posts para o resumo
            })
            ->searchable()
            ->sortable()
            ->filterable()
            ->paginated()
            ->selectable()
            ->meta([
                'title' => 'Usuários Landlord',
                'description' => 'Sistema avançado de usuários com filtros modernos e casts inteligentes',
            ]);
    }

    /**
     * Define as colunas da tabela usando o Sistema de Casts Avançado
     */
    protected function columns(): array
    {
        return [
            // TextColumn::make('id', 'ID')
            //     ->sortable()
            //     ->width('80px')
            //     ->alignment('center'),

            CompoundColumn::make('name', 'Nome')
                ->avatar('image_url')
                ->title('name')
                ->description('email')
                ->searchable()
                ->sortable()
                ->alignment('left'),


            TextColumn::make('email', 'E-mail')
                ->searchable()
                ->sortable()
                ->copyable(),
            BadgeColumn::make('status', 'Status')
                ->sortable()
                ->width('120px')
                ->cast(
                    StatusCast::make()
                        ->formatType('badge')
                        ->variants([
                            BaseStatus::Active->value => 'default',
                            BaseStatus::Published->value => 'default',
                            BaseStatus::Draft->value => 'default',
                            BaseStatus::Inactive->value => 'destructive',
                            BaseStatus::Archived->value => 'default',
                            BaseStatus::Deleted->value => 'destructive',
                        ])
                        ->labels([
                            BaseStatus::Active->value => 'Ativo',
                            BaseStatus::Published->value => 'Publicado',
                            BaseStatus::Draft->value => 'Rascunho',
                            BaseStatus::Inactive->value => 'Inativo',
                            BaseStatus::Archived->value => 'Arquivado',
                            BaseStatus::Deleted->value => 'Excluído',
                        ])
                ),


            DateColumn::make('created_at', 'Criado em')
                ->dateFormat('d/m/Y')
                ->since()
                ->sortable()
                ->width('150px')
                ->since(),

            DateColumn::make('updated_at', 'Atualizado em')
                ->dateFormat('d/m/Y H:i')
                ->since()
                ->sortable()
                ->hidden()
                ->disableAutoCasts(),

        ];
    }

    /**
     * Define os filtros da tabela usando Sistema de Filtros Avançados
     */
    protected function filters(): array
    {
        return [
            TextFilter::make('search')
                ->label('Buscar Usuário')
                ->placeholder('Digite nome ou e-mail...')
                ->searchColumns(['name', 'email'])
                ->operator('LIKE')
                ->caseSensitive(false)
                ->minLength(2),

            SelectFilter::make('status')
                ->label('Status')
                ->placeholder('Selecione um status')
                ->options([
                    BaseStatus::Active->value => 'Ativo',
                    BaseStatus::Published->value => 'Publicado',
                    BaseStatus::Draft->value => 'Rascunho',
                    BaseStatus::Inactive->value => 'Inativo',
                    BaseStatus::Archived->value => 'Arquivado',
                    BaseStatus::Deleted->value => 'Excluído',
                ]),


            DateRangeFilter::make('created_at')
                ->label('Data de Criação')
                ->brazilian()
                ->dateOnly(),

        ];
    }

    /**
     * Define as ações da tabela usando o Sistema de Ações Avançado
     */
    protected function actions(): array
    {
        return [
            // ✅ AÇÃO DE VISUALIZAÇÃO - RouteAction com visibilidade condicional
            $this->viewAction('landlord.users.show')
                ->label('Visualizar Usuário')
                ->icon('Eye')
                ->tooltip('Ver detalhes completos do usuário')
                ->variant('default')
                ->visible(function ($item, $context) {
                    // Verificação de segurança para evitar erro null
                    if (!$item) return true;
                    // Visível para todos os usuários ativos
                    return in_array($item->status, ['active', 'published']);
                }),

            // ✅ AÇÃO DE EDIÇÃO - RouteAction com habilitação condicional
            $this->editAction('landlord.users.edit')
                ->label('Editar')
                ->icon('Pencil')
                ->tooltip('Editar informações do usuário')
                ->enabled(function ($item, $context) {
                    // Verificação de segurança para evitar erro null
                    if (!$item) return true;
                    // Habilitado apenas se o usuário atual pode editar
                    return auth()->user()->can('update', $item);
                }),

            // ✅ AÇÃO DE MODAL - Abrir formulário de edição em um modal
            $this->modalAction('edit_user_modal')
                ->label('Editar no Modal')
                ->icon('FilePenLine')
                ->tooltip('Editar usuário em um modal')
                ->mode('slideover') // ou 'slideover'
                ->modalTitle('Editar Detalhes do Usuário')
                ->width('max-w-4xl')
                ->showLabel(),

            // ✅ AÇÃO EM LOTE - Excluir usuários selecionados
            $this->bulkAction('bulk_delete')
                ->label('Excluir Selecionados')
                ->icon('Trash2')
                ->variant('destructive')
                ->requiresConfirmation(
                    'Você tem certeza que deseja excluir os usuários selecionados? Esta ação não pode ser desfeita.',
                    'Confirmar Exclusão em Lote'
                )
                ->callback(function (\Illuminate\Database\Eloquent\Collection $items, array $context) {
                    // Prevenir que o usuário logado se auto-delete em lote
                    $itemsToDelete = $items->where('id', '!=', auth()->id());

                    $count = $itemsToDelete->count();

                    if ($count === 0) {
                        return [
                            'success' => false,
                            'message' => 'Nenhum usuário para excluir (o usuário logado não pode ser excluído).',
                        ];
                    }

                    $itemsToDelete->each->delete();

                    return [
                        'success' => true,
                        'message' => "{$count} usuários foram excluídos com sucesso!",
                    ];
                }),
        ];
    }
 
}

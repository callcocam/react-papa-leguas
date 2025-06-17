<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Examples;

use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\HeaderAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\RowAction;
use Callcocam\ReactPapaLeguas\Support\Table\Actions\BulkAction;
use Illuminate\Http\Request;

/**
 * Exemplo completo demonstrando todas as funcionalidades do Papa Leguas
 * 
 * Este exemplo mostra como criar uma tabela avançada com:
 * - Todas as funcionalidades de colunas
 * - Sistema completo de filtros
 * - Actions avançadas
 * - Cache e permissões
 * - Transformação e validação de dados
 * - Query e paginação otimizada
 */
class CompleteTableExample
{
    /**
     * Criar tabela de usuários completa
     */
    public static function createUsersTable(): Table
    {
        return Table::make('complete-users-table')
            ->model(\App\Models\User::class)
            
            // Query e Paginação
            ->querySystem(true)
            ->with(['profile', 'roles'])
            ->searchableColumns(['name', 'email'])
            ->defaultSort('created_at', 'desc')
            ->pagination(true)
            ->perPage(25)
            
            // Cache e Permissões
            ->cache(true, 900)
            ->cacheTags(['users', 'dashboard'])
            ->permissions(true)
            ->permissionGuard('web')
            
            // Transformação e Validação
            ->dataTransformation(true)
            ->transformDate('created_at', 'd/m/Y H:i')
            ->transformBoolean('active')
            ->validation(true)
            ->validateEmail('email')
            ->validateRequired('name')
            
            // Colunas
            ->textColumn('id', 'ID')
                ->sortable()
                ->width(80)
            
            ->textColumn('name', 'Nome')
                ->searchable()
                ->sortable()
                ->copyable()
            
            ->textColumn('email', 'Email')
                ->searchable()
                ->sortable()
                ->icon('mail')
            
            ->badgeColumn('status', 'Status')
                ->colors([
                    'active' => 'success',
                    'inactive' => 'secondary'
                ])
            
            ->dateColumn('created_at', 'Criado em')
                ->sortable()
                ->dateFormat('d/m/Y H:i')
            
            ->booleanColumn('active', 'Ativo')
                ->trueIcon('check-circle')
                ->falseIcon('x-circle')
            
            // Filtros
            ->textFilter('name', 'Nome')
                ->placeholder('Buscar por nome...')
            
            ->selectFilter('status', 'Status')
                ->options([
                    'active' => 'Ativo',
                    'inactive' => 'Inativo'
                ])
            
            ->dateFilter('created_at', 'Data de Criação')
            
            ->booleanFilter('active', 'Apenas Ativos')
            
            // Actions
            ->headerAction(
                HeaderAction::make('create')
                    ->label('Novo Usuário')
                    ->icon('plus')
                    ->color('primary')
            )
            
            ->rowAction(
                RowAction::make('edit')
                    ->label('Editar')
                    ->icon('edit')
                    ->color('warning')
            )
            
            ->bulkAction(
                BulkAction::make('delete')
                    ->label('Excluir Selecionados')
                    ->icon('trash')
                    ->color('danger')
                    ->requiresConfirmation()
            )
            
            // Configurações finais
            ->searchable()
            ->sortable()
            ->filterable()
            ->selectable()
            ->striped()
            ->hover()
            ->responsive();
    }

    /**
     * Criar tabela de produtos com funcionalidades específicas
     */
    public static function createProductsTable(): Table
    {
        return Table::make('complete-products-table')
            ->model(\App\Models\Product::class)
            
            // Configuração otimizada para produtos
            ->querySystem(true)
            ->with(['category', 'brand', 'images'])
            ->searchableColumns(['name', 'sku', 'description', 'category.name'])
            ->defaultSort('created_at', 'desc')
            
            // Cache específico para produtos
            ->cache(true, 1800) // 30 minutos
            ->cacheTags(['products', 'catalog'])
            
            // Validação específica para produtos
            ->validateRequired('name')
            ->validateRequired('sku')
            ->validateNumeric('price')
            ->validateNumeric('stock')
            
            // Transformações específicas
            ->transformCurrency('price')
            ->transformCurrency('cost')
            ->transformBoolean('active')
            ->transformEnum('status', [
                'draft' => 'Rascunho',
                'published' => 'Publicado',
                'archived' => 'Arquivado'
            ])
            
            // Colunas específicas para produtos
            ->textColumn('image', 'Imagem')
                ->renderAsImage()
                ->width(80)
            
            ->textColumn('name', 'Nome')
                ->searchable()
                ->sortable()
                ->limit(50)
            
            ->textColumn('sku', 'SKU')
                ->searchable()
                ->sortable()
                ->copyable()
                ->fontMono()
            
            ->textColumn('price', 'Preço')
                ->sortable()
                ->alignRight()
                ->currency('BRL')
            
            ->textColumn('stock', 'Estoque')
                ->sortable()
                ->alignCenter()
                ->color(fn ($value) => $value > 10 ? 'success' : ($value > 0 ? 'warning' : 'danger'))
            
            ->badgeColumn('status', 'Status')
                ->colors([
                    'draft' => 'secondary',
                    'published' => 'success',
                    'archived' => 'warning'
                ])
            
            // Filtros específicos para produtos
            ->textFilter('name', 'Nome do Produto')
            ->selectFilter('category_id', 'Categoria')
                ->relationship('category', 'name')
            ->selectFilter('status', 'Status')
                ->options([
                    'draft' => 'Rascunho',
                    'published' => 'Publicado',
                    'archived' => 'Arquivado'
                ])
            ->numericRangeFilter('price', 'Faixa de Preço')
            ->booleanFilter('active', 'Apenas Ativos')
            
            // Paginação otimizada para catálogo
            ->paginate(20)
            ->perPageOptions([20, 40, 60])
            
            ->meta([
                'title' => 'Catálogo de Produtos',
                'description' => 'Gerenciamento completo do catálogo de produtos'
            ]);
    }

    /**
     * Exemplo de uso da tabela
     */
    public static function renderUsersTable(Request $request = null): array
    {
        $table = self::createUsersTable();
        
        // Aplicar configurações específicas baseadas no contexto
        if ($request && $request->get('export')) {
            $table->noPagination(); // Para exportação, sem paginação
        }
        
        if ($request && $request->get('simple')) {
            $table->quickPagination(); // Para mobile, paginação simples
        }
        
        return $table->render($request);
    }

    /**
     * Exemplo de configuração dinâmica baseada no usuário
     */
    public static function createUserSpecificTable(\App\Models\User $user): Table
    {
        $table = self::createUsersTable();
        
        // Configurar baseado no papel do usuário
        if ($user->hasRole('admin')) {
            // Admin vê tudo
            $table->permissions(false);
        } elseif ($user->hasRole('manager')) {
            // Manager vê apenas sua equipe
            $table->scope('team', $user->team_id);
        } else {
            // Usuário comum vê apenas seus próprios dados
            $table->ownerOnly();
        }
        
        // Configurar cache baseado no papel
        if ($user->hasRole(['admin', 'manager'])) {
            $table->cacheForDashboard();
        } else {
            $table->cache(true, 300); // Cache menor para usuários comuns
        }
        
        return $table;
    }

    /**
     * Exemplo de tabela para relatórios
     */
    public static function createReportsTable(): Table
    {
        return Table::make('reports-table')
            ->model(\App\Models\User::class)
            
            // Configuração otimizada para relatórios
            ->querySystem(true)
            ->selectRaw('
                users.id,
                users.name,
                users.email,
                users.created_at,
                COUNT(orders.id) as total_orders,
                SUM(orders.total) as total_spent,
                AVG(orders.total) as avg_order_value
            ')
            ->leftJoin('orders', 'users.id', '=', 'orders.user_id')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at')
            ->having('total_orders', '>', 0)
            
            // Cache longo para relatórios
            ->cacheForReports()
            
            // Colunas específicas para relatórios
            ->textColumn('name', 'Cliente')
            ->textColumn('total_orders', 'Total de Pedidos')
                ->alignCenter()
                ->sortable()
            ->textColumn('total_spent', 'Total Gasto')
                ->currency('BRL')
                ->alignRight()
                ->sortable()
            ->textColumn('avg_order_value', 'Ticket Médio')
                ->currency('BRL')
                ->alignRight()
                ->sortable()
            
            // Sem paginação para relatórios
            ->noPagination()
            
            ->meta([
                'title' => 'Relatório de Clientes',
                'description' => 'Relatório analítico de performance dos clientes'
            ]);
    }
} 
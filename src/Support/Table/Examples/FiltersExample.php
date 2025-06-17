<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Examples;

use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BooleanColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\TextFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\SelectFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\DateFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\BooleanFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\RelationFilter;

/**
 * Exemplo completo de uso do sistema de filtros com React
 */
class FiltersExample
{
    /**
     * Exemplo 1: Tabela de Posts com filtros completos
     */
    public static function postsTable(): Table
    {
        return Table::make('posts')
            ->model('App\\Models\\Post')
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'draft' => 'warning',
                        'published' => 'success',
                        'archived' => 'secondary',
                    ]),

                TextColumn::make('category.name')
                    ->label('Categoria')
                    ->searchable(),

                TextColumn::make('author.name')
                    ->label('Autor')
                    ->searchable(),

                DateColumn::make('published_at')
                    ->label('Publicado em')
                    ->format('d/m/Y H:i')
                    ->sortable(),

                BooleanColumn::make('featured')
                    ->label('Destaque')
                    ->trueIcon('Star')
                    ->falseIcon('StarOff'),
            ])
            ->filters([
                // Busca global
                TextFilter::globalSearch(['title', 'content', 'excerpt'])
                    ->placeholder('Buscar posts...')
                    ->debounce(300)
                    ->minLength(2),

                // Filtro de categoria
                RelationFilter::category('App\\Models\\Category')
                    ->searchable()
                    ->hierarchical(),

                // Filtro de autor
                RelationFilter::author('App\\Models\\User')
                    ->searchable(),

                // Filtro de tags
                RelationFilter::tags('App\\Models\\Tag')
                    ->multiple()
                    ->searchable(),

                // Filtro de status
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Rascunho',
                        'published' => 'Publicado',
                        'archived' => 'Arquivado',
                    ])
                    ->placeholder('Todos os status...')
                    ->icon('Filter'),

                // Filtro de destaque
                BooleanFilter::featured()
                    ->asSwitch(),

                // Filtro de período de publicação
                DateFilter::period('published_at')
                    ->withPresets([
                        'today' => 'Hoje',
                        'thisWeek' => 'Esta Semana',
                        'thisMonth' => 'Este Mês',
                        'thisYear' => 'Este Ano',
                    ]),

                // Filtro de data de criação
                DateFilter::createdAt()
                    ->range(),
            ])
            ->filtersLayout('horizontal')
            ->filtersCollapsible(true, false)
            ->filtersAutoApply(true)
            ->filtersPersistence('posts-filters')
            ->filtersGrouping([
                'Conteúdo' => ['global_search', 'status', 'featured'],
                'Relacionamentos' => ['category', 'author', 'tags'],
                'Datas' => ['period', 'created_at'],
            ]);
    }

    /**
     * Exemplo 2: Tabela de Usuários com filtros administrativos
     */
    public static function usersTable(): Table
    {
        return Table::make('users')
            ->model('App\\Models\\User')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'active' => 'success',
                        'inactive' => 'secondary',
                        'suspended' => 'danger',
                    ]),

                BooleanColumn::make('email_verified_at')
                    ->label('E-mail Verificado')
                    ->formatUsing(fn($value) => !is_null($value)),

                DateColumn::make('last_login_at')
                    ->label('Último Login')
                    ->format('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                // Busca por nome ou email
                TextFilter::globalSearch(['name', 'email'])
                    ->placeholder('Buscar usuários...')
                    ->icon('Search'),

                // Filtro de status
                SelectFilter::status([
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                    'suspended' => 'Suspenso',
                ]),

                // Filtro de verificação de email
                BooleanFilter::verified()
                    ->field('email_verified_at')
                    ->applyUsing(function ($query, $value) {
                        return $value 
                            ? $query->whereNotNull('email_verified_at')
                            : $query->whereNull('email_verified_at');
                    }),

                // Filtro de departamento
                RelationFilter::department('App\\Models\\Department')
                    ->searchable()
                    ->hierarchical(),

                // Filtro de período de registro
                DateFilter::period('created_at')
                    ->label('Período de Registro'),
            ])
            ->filtersLayout('sidebar')
            ->filtersPosition('left')
            ->filtersCollapsible(false);
    }

    /**
     * Exemplo 3: Tabela de Produtos com filtros de e-commerce
     */
    public static function productsTable(): Table
    {
        return Table::make('products')
            ->model('App\\Models\\Product')
            ->columns([
                TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'active' => 'success',
                        'inactive' => 'secondary',
                        'out_of_stock' => 'warning',
                        'discontinued' => 'danger',
                    ]),

                TextColumn::make('price')
                    ->label('Preço')
                    ->formatUsing(fn($value) => 'R$ ' . number_format($value, 2, ',', '.')),

                TextColumn::make('stock_quantity')
                    ->label('Estoque')
                    ->sortable(),

                BooleanColumn::make('featured')
                    ->label('Destaque'),
            ])
            ->filters([
                // Busca por nome, SKU ou descrição
                TextFilter::globalSearch(['name', 'sku', 'description'])
                    ->placeholder('Buscar produtos...')
                    ->autocomplete([
                        'iPhone 13',
                        'Samsung Galaxy',
                        'MacBook Pro',
                        'iPad Air',
                    ]),

                // Filtro de categoria
                RelationFilter::category('App\\Models\\Category')
                    ->hierarchical()
                    ->searchable(),

                // Filtro de marca
                RelationFilter::make('brand')
                    ->label('Marca')
                    ->relationship('brand', 'id', 'name')
                    ->searchable(),

                // Filtro de status
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Ativo',
                        'inactive' => 'Inativo',
                        'out_of_stock' => 'Sem Estoque',
                        'discontinued' => 'Descontinuado',
                    ])
                    ->multiple(),

                // Filtro de faixa de preço
                TextFilter::make('price_min')
                    ->label('Preço Mínimo')
                    ->placeholder('R$ 0,00')
                    ->applyUsing(function ($query, $value) {
                        return $query->where('price', '>=', $value);
                    }),

                TextFilter::make('price_max')
                    ->label('Preço Máximo')
                    ->placeholder('R$ 9999,99')
                    ->applyUsing(function ($query, $value) {
                        return $query->where('price', '<=', $value);
                    }),

                // Filtro de estoque
                SelectFilter::make('stock_filter')
                    ->label('Estoque')
                    ->options([
                        'in_stock' => 'Em Estoque',
                        'low_stock' => 'Estoque Baixo',
                        'out_of_stock' => 'Sem Estoque',
                    ])
                    ->applyUsing(function ($query, $value) {
                        return match($value) {
                            'in_stock' => $query->where('stock_quantity', '>', 0),
                            'low_stock' => $query->where('stock_quantity', '<=', 10)->where('stock_quantity', '>', 0),
                            'out_of_stock' => $query->where('stock_quantity', '=', 0),
                            default => $query,
                        };
                    }),

                // Filtro de destaque
                BooleanFilter::featured()
                    ->asButtons(),

                // Filtro de período de criação
                DateFilter::period('created_at')
                    ->label('Período de Criação'),
            ])
            ->filtersLayout('grid')
            ->filtersGrouping([
                'Busca' => ['global_search'],
                'Classificação' => ['category', 'brand', 'status'],
                'Preço e Estoque' => ['price_min', 'price_max', 'stock_filter'],
                'Outros' => ['featured', 'period'],
            ]);
    }

    /**
     * Exemplo 4: Tabela com filtros avançados e personalizados
     */
    public static function advancedFiltersTable(): Table
    {
        return Table::make('advanced')
            ->model('App\\Models\\Order')
            ->columns([
                TextColumn::make('order_number')
                    ->label('Número do Pedido')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('Status'),

                TextColumn::make('customer.name')
                    ->label('Cliente'),

                TextColumn::make('total')
                    ->label('Total')
                    ->formatUsing(fn($value) => 'R$ ' . number_format($value, 2, ',', '.')),

                DateColumn::make('created_at')
                    ->label('Data do Pedido')
                    ->format('d/m/Y H:i'),
            ])
            ->filters([
                // Filtro de busca com sugestões
                TextFilter::make('search')
                    ->label('Busca')
                    ->placeholder('Número do pedido ou cliente...')
                    ->withSuggestions([
                        'ORD-2024-001',
                        'ORD-2024-002',
                        'João Silva',
                        'Maria Santos',
                    ])
                    ->applyUsing(function ($query, $value) {
                        return $query->where(function ($q) use ($value) {
                            $q->where('order_number', 'like', "%{$value}%")
                              ->orWhereHas('customer', function ($customerQuery) use ($value) {
                                  $customerQuery->where('name', 'like', "%{$value}%");
                              });
                        });
                    }),

                // Filtro de cliente com busca remota
                RelationFilter::make('customer')
                    ->label('Cliente')
                    ->relationship('customer', 'id', 'name')
                    ->remote('/api/customers/search')
                    ->searchable(),

                // Filtro de status com cores
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pendente',
                        'processing' => 'Processando',
                        'shipped' => 'Enviado',
                        'delivered' => 'Entregue',
                        'cancelled' => 'Cancelado',
                    ])
                    ->reactConfig([
                        'component' => 'StatusFilter',
                        'colors' => [
                            'pending' => 'warning',
                            'processing' => 'primary',
                            'shipped' => 'info',
                            'delivered' => 'success',
                            'cancelled' => 'danger',
                        ],
                    ]),

                // Filtro de faixa de valores
                SelectFilter::make('value_range')
                    ->label('Faixa de Valor')
                    ->options([
                        '0-100' => 'Até R$ 100',
                        '100-500' => 'R$ 100 - R$ 500',
                        '500-1000' => 'R$ 500 - R$ 1.000',
                        '1000+' => 'Acima de R$ 1.000',
                    ])
                    ->applyUsing(function ($query, $value) {
                        return match($value) {
                            '0-100' => $query->whereBetween('total', [0, 100]),
                            '100-500' => $query->whereBetween('total', [100, 500]),
                            '500-1000' => $query->whereBetween('total', [500, 1000]),
                            '1000+' => $query->where('total', '>', 1000),
                            default => $query,
                        };
                    }),

                // Filtro de período com presets customizados
                DateFilter::make('date_range')
                    ->label('Período')
                    ->range()
                    ->field('created_at')
                    ->withPresets([
                        'today' => 'Hoje',
                        'yesterday' => 'Ontem',
                        'last_7_days' => 'Últimos 7 dias',
                        'last_30_days' => 'Últimos 30 dias',
                        'this_month' => 'Este mês',
                        'last_month' => 'Mês passado',
                        'this_year' => 'Este ano',
                    ]),
            ])
            ->filtersLayout('horizontal')
            ->filtersAutoApply(false) // Mostrar botão "Aplicar"
            ->filtersPersistence('advanced-filters', 'sessionStorage');
    }

    /**
     * Exemplo 5: RelationManager com filtros contextuais
     */
    public static function categoryPostsRelationManager(int $categoryId): Table
    {
        return Table::make('category-posts')
            ->model('App\\Models\\Post')
            ->asRelationManager('posts', $categoryId, 'App\\Models\\Category')
            ->columns([
                TextColumn::make('title')
                    ->label('Título')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->label('Status'),

                DateColumn::make('published_at')
                    ->label('Publicado em')
                    ->format('d/m/Y'),
            ])
            ->filters([
                // Busca específica para posts da categoria
                TextFilter::globalSearch(['title', 'content'])
                    ->placeholder('Buscar posts nesta categoria...'),

                // Filtro de status
                SelectFilter::status([
                    'draft' => 'Rascunho',
                    'published' => 'Publicado',
                    'archived' => 'Arquivado',
                ]),

                // Filtro de autor (apenas autores que têm posts nesta categoria)
                RelationFilter::author('App\\Models\\User')
                    ->applyUsing(function ($query, $value) use ($categoryId) {
                        return $query->where('category_id', $categoryId)
                                   ->where('author_id', $value);
                    }),

                // Filtro de período de publicação
                DateFilter::period('published_at'),

                // Filtro de destaque
                BooleanFilter::featured(),
            ])
            ->withRelationFilters('App\\Models\\Category')
            ->filtersLayout('horizontal')
            ->filtersCollapsible(true, true); // Inicia colapsado no RelationManager
    }
} 
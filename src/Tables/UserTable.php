<?php

namespace Callcocam\ReactPapaLeguas\Tables;

use App\Models\User;
use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BooleanColumn;

/**
 * Tabela de usuários
 */
class UserTable extends Table
{
    /**
     * Configurar a tabela
     */
    protected function setUp(): void
    {
        // Definir prefixo personalizado das rotas
        $this->setRoutePrefix('landlord.users');
        
        // Configurar fonte de dados
        $this->model(User::class)
            ->searchable()
            ->sortable()
            ->filterable()
            ->paginated()
            ->selectable()
            ->meta([
                'title' => 'Usuários Landlord',
                'description' => 'Sistema de análise JSON para usuários via UserTable com colunas modernas',
            ]);
    }

    /**
     * Define as colunas da tabela usando as novas classes especializadas
     */
    protected function columns(): array
    {
        return [
            TextColumn::make('id', 'ID')
                ->sortable()
                ->width('80px')
                ->alignment('center'),

            TextColumn::make('name', 'Nome')
                ->searchable()
                ->sortable()
                ->copyable()
                ->limit(50)
                ->placeholder('Sem nome'),

            TextColumn::make('email', 'E-mail')
                ->searchable()
                ->sortable()
                ->copyable()
                ->formatUsing(function ($value) {
                    return [
                        'value' => $value,
                        'type' => 'email',
                        'formatted' => $value,
                        'copyable' => true,
                        'mailto' => "mailto:{$value}"
                    ];
                }),

            BadgeColumn::make('status', 'Status')
                ->variants([
                    'active' => 'success',
                    'inactive' => 'secondary',
                    'draft' => 'warning',
                    'published' => 'success',
                    'archived' => 'secondary',
                ])
                ->labels([
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                    'draft' => 'Rascunho',
                    'published' => 'Publicado',
                    'archived' => 'Arquivado',
                ])
                ->sortable()
                ->width('120px'),

            BooleanColumn::make('email_verified_at', 'E-mail Verificado')
                ->getValueUsing(function ($row) {
                    return !is_null($row->email_verified_at);
                })
                ->labels('Verificado', 'Não Verificado')
                ->colors('success', 'warning')
                ->icons('shield-check', 'shield-alert')
                ->asBadge()
                ->sortable()
                ->width('140px'),

            DateColumn::make('created_at', 'Criado em')
                ->dateFormat('d/m/Y H:i')
                ->since()
                ->sortable()
                ->width('150px'),

            DateColumn::make('updated_at', 'Atualizado em')
                ->dateFormat('d/m/Y H:i')
                ->since()
                ->sortable()
                ->hidden(), // Oculto por padrão

            DateColumn::make('email_verified_at', 'Verificado em')
                ->dateFormat('d/m/Y H:i')
                ->since()
                ->sortable()
                ->width('150px')
                ->formatUsing(function ($value) {
                    if (!$value) {
                        return [
                            'value' => null,
                            'type' => 'badge',
                            'variant' => 'warning',
                            'label' => 'Não verificado'
                        ];
                    }
                    
                    $date = \Carbon\Carbon::parse($value);
                    return [
                        'value' => $value,
                        'type' => 'date',
                        'formatted' => $date->format('d/m/Y H:i'),
                        'since' => $date->diffForHumans(),
                        'badge' => [
                            'variant' => 'success',
                            'label' => 'Verificado'
                        ]
                    ];
                }),
        ];
    }

    /**
     * Define os filtros da tabela
     */
    protected function filters(): array
    {
        return [
            [
                'key' => 'status',
                'label' => 'Status',
                'type' => 'select',
                'options' => [
                    ['value' => 'active', 'label' => 'Ativo'],
                    ['value' => 'inactive', 'label' => 'Inativo'],
                    ['value' => 'draft', 'label' => 'Rascunho'],
                    ['value' => 'published', 'label' => 'Publicado'],
                    ['value' => 'archived', 'label' => 'Arquivado'],
                ]
            ],
            [
                'key' => 'email_verified',
                'label' => 'E-mail Verificado',
                'type' => 'select',
                'options' => [
                    ['value' => '1', 'label' => 'Verificado'],
                    ['value' => '0', 'label' => 'Não Verificado'],
                ]
            ],
            [
                'key' => 'search',
                'label' => 'Buscar',
                'type' => 'text',
                'placeholder' => 'Buscar usuários...'
            ]
        ];
    }

    /**
     * Define as ações da tabela
     */
    protected function actions(): array
    {
        return [
            'header' => [
                [
                    'key' => 'create',
                    'label' => 'Novo Usuário',
                    'icon' => 'plus',
                    'variant' => 'primary'
                ],
                [
                    'key' => 'export',
                    'label' => 'Exportar',
                    'icon' => 'download',
                    'variant' => 'secondary'
                ]
            ],
            'row' => [
                [
                    'key' => 'view',
                    'label' => 'Visualizar',
                    'icon' => 'eye'
                ],
                [
                    'key' => 'edit',
                    'label' => 'Editar',
                    'icon' => 'edit'
                ],
                [
                    'key' => 'resend-verification',
                    'label' => 'Reenviar Verificação',
                    'icon' => 'mail',
                    'variant' => 'secondary',
                    // 'condition' => function($row) {
                    //     return is_null($row->email_verified_at);
                    // }
                ],
                [
                    'key' => 'delete',
                    'label' => 'Excluir',
                    'icon' => 'trash',
                    'variant' => 'danger'
                ]
            ],
            'bulk' => [
                [
                    'key' => 'bulk-delete',
                    'label' => 'Excluir Selecionados',
                    'icon' => 'trash',
                    'variant' => 'danger'
                ],
                [
                    'key' => 'bulk-verify',
                    'label' => 'Marcar como Verificados',
                    'icon' => 'shield-check',
                    'variant' => 'success'
                ]
            ]
        ];
    }
} 
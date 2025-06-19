<?php

namespace Callcocam\ReactPapaLeguas\Tables;

use App\Models\User;
use Callcocam\ReactPapaLeguas\Enums\BaseStatus;
use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BooleanColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\TextFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\SelectFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\BooleanFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Filters\DateRangeFilter;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\DateCast;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\StatusCast;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\ClosureCast;

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
                'description' => 'Sistema avançado de usuários com filtros modernos e casts inteligentes',
            ]);
    }

    /**
     * Define as colunas da tabela usando o Sistema de Casts Avançado
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
                ->placeholder('Sem nome')
                // Cast personalizado para transformação de texto
                ->cast(ClosureCast::make()
                    ->transform(function ($value, $context) {
                        return [
                            'value' => $value,
                            'formatted' => ucwords(strtolower($value ?? '')),
                            'initials' => $this->getInitials($value),
                            'length' => strlen($value ?? ''),
                            'type' => 'name',
                        ];
                    })
                ),

            TextColumn::make('email', 'E-mail')
                ->searchable()
                ->sortable()
                ->copyable()
                // Cast personalizado para formatação de e-mail
                ->cast(ClosureCast::make()
                    ->transform(function ($value, $context) {
                        if (!$value) return null;
                        
                        $parts = explode('@', $value);
                        $domain = $parts[1] ?? '';
                        
                        return [
                            'value' => $value,
                            'formatted' => $value,
                            'domain' => $domain,
                            'username' => $parts[0] ?? '',
                            'type' => 'email',
                            'copyable' => true,
                            'mailto' => "mailto:{$value}",
                            'is_business' => in_array($domain, ['gmail.com', 'yahoo.com', 'hotmail.com']) ? false : true,
                        ];
                    })
                ),

            BadgeColumn::make('status', 'Status')
                ->sortable()
                ->width('120px')
                ->cast(StatusCast::make()
                    ->formatType('badge')
                    ->variants([
                        BaseStatus::Active->value => 'success',
                        BaseStatus::Published->value => 'success',
                        BaseStatus::Draft->value => 'secondary',
                        BaseStatus::Inactive->value => 'secondary',
                        BaseStatus::Archived->value => 'warning',
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

            BooleanColumn::make('email_verified_at', 'E-mail Verificado')
                ->getValueUsing(function ($row) {
                    return !is_null($row->email_verified_at);
                })
                ->labels('Verificado', 'Não Verificado')
                ->colors('success', 'warning')
                ->icons('shield-check', 'shield-alert')
                ->asBadge()
                ->sortable()
                ->width('140px')
                ->cast(StatusCast::make()
                    ->formatType('badge')
                    ->variants([
                        true => 'success',
                        1 => 'success',
                        'verified' => 'success',
                        false => 'warning',
                        0 => 'warning',
                        'unverified' => 'warning',
                    ])
                    ->labels([
                        true => 'Verificado',
                        1 => 'Verificado',
                        'verified' => 'Verificado',
                        false => 'Não Verificado',
                        0 => 'Não Verificado',
                        'unverified' => 'Não Verificado',
                    ])
                ),

            DateColumn::make('created_at', 'Criado em')
                ->dateFormat('d/m/Y H:i')
                ->since()
                ->sortable()
                ->width('150px')
                ->cast(DateCast::make()
                    ->format('d/m/Y H:i')
                    ->timezone('America/Sao_Paulo')
                    ->showRelative(true, 30)
                ),

            DateColumn::make('updated_at', 'Atualizado em')
                ->dateFormat('d/m/Y H:i')
                ->since()
                ->sortable()
                ->hidden()
                ->disableAutoCasts(),

            DateColumn::make('email_verified_at', 'Verificado em')
                ->dateFormat('d/m/Y H:i')
                ->since()
                ->sortable()
                ->width('150px')
                ->cast(ClosureCast::when(
                    fn($value) => !is_null($value),
                    function ($value, $context) {
                        $dateCast = DateCast::make()
                            ->format('d/m/Y H:i')
                            ->timezone('America/Sao_Paulo')
                            ->showRelative(true);
                        
                        $result = $dateCast->cast($value, $context);
                        
                        $result['verification_status'] = [
                            'type' => 'badge',
                            'variant' => 'success',
                            'label' => 'Verificado',
                            'icon' => 'shield-check'
                        ];
                        
                        return $result;
                    },
                    function ($value, $context) {
                        return [
                            'value' => null,
                            'formatted' => null,
                            'verification_status' => [
                                'type' => 'badge',
                                'variant' => 'warning',
                                'label' => 'Não verificado',
                                'icon' => 'shield-alert'
                            ]
                        ];
                    }
                )),
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

            BooleanFilter::make('email_verified')
                ->label('E-mail Verificado')
                ->labels('Verificado', 'Não Verificado', 'Todos')
                ->allowAll(true)
                ->queryUsing(function ($query, $value) {
                    if ($value === true || $value === 1) {
                        $query->whereNotNull('email_verified_at');
                    } elseif ($value === false || $value === 0) {
                        $query->whereNull('email_verified_at');
                    }
                }),

            BooleanFilter::make('active_status')
                ->label('Situação Geral')
                ->activeInactive()
                ->allowAll(true)
                ->queryUsing(function ($query, $value) {
                    if ($value === true || $value === 1) {
                        $query->whereIn('status', [BaseStatus::Active->value, BaseStatus::Published->value]);
                    } elseif ($value === false || $value === 0) {
                        $query->whereNotIn('status', [BaseStatus::Active->value, BaseStatus::Published->value]);
                    }
                }),

            DateRangeFilter::make('created_at')
                ->label('Data de Criação')
                ->brazilian()
                ->dateOnly(),

            DateRangeFilter::make('email_verified_at')
                ->label('Data de Verificação')
                ->brazilian()
                ->dateOnly()
                ->queryUsing(function ($query, $value, $filter) {
                    $startDate = $filter->getStartDate();
                    $endDate = $filter->getEndDate();
                    
                    if ($startDate || $endDate) {
                        $query->whereNotNull('email_verified_at');
                        
                        if ($startDate) {
                            $start = $filter->parseDate($startDate, true);
                            if ($start) {
                                $query->where('email_verified_at', '>=', $start);
                            }
                        }
                        
                        if ($endDate) {
                            $end = $filter->parseDate($endDate, false);
                            if ($end) {
                                $query->where('email_verified_at', '<=', $end);
                            }
                        }
                    }
                }),
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

    /**
     * Método auxiliar para obter iniciais do nome
     */
    private function getInitials(?string $name): string
    {
        if (!$name) return '';
        
        $words = explode(' ', trim($name));
        $initials = '';
        
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        
        return $initials;
    }
} 
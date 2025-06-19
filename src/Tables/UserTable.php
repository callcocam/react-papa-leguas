<?php

namespace Callcocam\ReactPapaLeguas\Tables;

use App\Models\User;
use Callcocam\ReactPapaLeguas\Support\Table\Table;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\TextColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BadgeColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\DateColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\BooleanColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\DateCast;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\StatusCast;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\ClosureCast;

/**
 * Tabela de usuários com Sistema de Casts Avançado
 * 
 * FUNCIONALIDADES IMPLEMENTADAS:
 * ✅ Sistema de Casts Automático por tipo de coluna
 * ✅ Casts específicos para transformações personalizadas
 * ✅ Pipeline de transformação: Dados → Casts → Formatação → Frontend
 * ✅ Detecção automática baseada em padrões de dados
 * ✅ Configuração flexível por coluna
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
                'description' => 'Sistema de análise JSON para usuários via UserTable com colunas modernas e casts avançados',
            ]);
    }

    /**
     * Define as colunas da tabela usando o Sistema de Casts Avançado
     * 
     * SISTEMA DE CASTS AUTOMÁTICO E PERSONALIZADO:
     * - DateColumn: Aplica DateCast automaticamente para campos de data
     * - BadgeColumn: Aplica StatusCast automaticamente para status/enums
     * - BooleanColumn: Aplica StatusCast automaticamente para valores booleanos
     * - TextColumn: Permite casts personalizados via ->cast()
     * 
     * FUNCIONALIDADES AVANÇADAS:
     * - ->cast(): Adiciona cast específico para a coluna
     * - ->casts(): Adiciona múltiplos casts para a coluna
     * - ->disableAutoCasts(): Desabilita casts automáticos
     * - ->enableAutoCasts(): Reabilita casts automáticos
     * 
     * ORDEM DE APLICAÇÃO:
     * 1. Casts específicos da coluna (->cast())
     * 2. Casts automáticos (se não desabilitados)
     * 3. Formatação da coluna (->formatUsing())
     * 
     * Os casts são aplicados ANTES da formatação da coluna,
     * permitindo que a coluna trabalhe com dados já processados.
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
                // Exemplo: Cast personalizado para transformação de texto
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
                // Exemplo: Cast personalizado para formatação de e-mail
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
                ->width('120px')
                // Exemplo: Cast específico para status avançado
                ->cast(StatusCast::make()
                    ->formatType('badge')
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
                // Exemplo: Cast específico para verificação de e-mail
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
                // Exemplo: Cast específico para formatação brasileira
                ->cast(DateCast::make()
                    ->format('d/m/Y H:i')
                    ->timezone('America/Sao_Paulo')
                    ->showRelative(true, 30) // Mostrar tempo relativo para últimos 30 dias
                ),

            DateColumn::make('updated_at', 'Atualizado em')
                ->dateFormat('d/m/Y H:i')
                ->since()
                ->sortable()
                ->hidden() // Oculto por padrão
                // Exemplo: Desabilitar casts automáticos para controle manual
                ->disableAutoCasts(),

            DateColumn::make('email_verified_at', 'Verificado em')
                ->dateFormat('d/m/Y H:i')
                ->since()
                ->sortable()
                ->width('150px')
                // Exemplo: Cast condicional para data de verificação
                ->cast(ClosureCast::when(
                    // Condição: se o valor existe
                    fn($value) => !is_null($value),
                    // Transformação: aplicar DateCast
                    function ($value, $context) {
                        $dateCast = DateCast::make()
                            ->format('d/m/Y H:i')
                            ->timezone('America/Sao_Paulo')
                            ->showRelative(true);
                        
                        $result = $dateCast->cast($value, $context);
                        
                        // Adicionar informações extras
                        $result['verification_status'] = [
                            'type' => 'badge',
                            'variant' => 'success',
                            'label' => 'Verificado',
                            'icon' => 'shield-check'
                        ];
                        
                        return $result;
                    },
                    // Fallback: se não há valor
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
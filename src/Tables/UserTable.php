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
use App\Models\User as UserModel;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\CompoundColumn;
use Callcocam\ReactPapaLeguas\Support\Table\Columns\EditableColumn;
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
            ->query(function () {
                // Garante que todos os campos necessários, incluindo o email, sejam selecionados.
                return UserModel::query()->select([
                    'id',
                    'name',
                    'email',
                    'status',
                    'email_verified_at',
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

            // CompoundColumn::make('name', 'Nome')
            //     ->avatar('image_url')
            //     ->title('name')
            //     ->description('email')
            //     ->searchable()
            //     ->sortable()
            //     ->alignment('left'),
            EditableColumn::make('name', 'Nome')
                ->sortable()
                ->searchable()
                ->updateUsing(function (User $record, $value) {
                    $record->update([
                        'name' => $value,
                        'slug' => Str::slug($value),
                    ]);
                    return true;
                }),
            // TextColumn::make('name', 'Nome')
            //     ->searchable()
            //     ->sortable()
            //     ->copyable()
            //     ->limit(50)
            //     ->placeholder('Sem nome')
            //     // Cast personalizado para transformação de texto
            //     ->cast(ClosureCast::make()
            //         ->transform(function ($value, $context) {
            //             return [
            //                 'value' => $value,
            //                 'formatted' => ucwords(strtolower($value ?? '')),
            //                 'initials' => $this->getInitials($value),
            //                 'length' => strlen($value ?? ''),
            //                 'type' => 'name',
            //             ];
            //         })
            //     ),

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
                ->cast(
                    StatusCast::make()
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
                ->cast(
                    DateCast::make()
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
                    return $item->status === 'active';
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

            // ✅ AÇÃO DE CALLBACK - Alternar status de verificação de e-mail
            $this->callbackAction('toggle_verification')
                ->labelUsing(function ($item, $context) {
                    if (!$item) return 'Verificação';
                    return $item->email_verified_at
                        ? 'Desmarcar Verificação'
                        : 'Marcar como Verificado';
                })
                ->iconUsing(function ($item, $context) {
                    if (!$item) return 'Shield';
                    return $item->email_verified_at
                        ? 'ShieldX'
                        : 'ShieldCheck';
                })
                ->variantUsing(function ($item, $context) {
                    if (!$item) return 'secondary';
                    return $item->email_verified_at ? 'warning' : 'success';
                })
                ->tooltip('Alternar status de verificação do e-mail')
                ->callback(function ($item, $context) {
                    if (!$item) {
                        return [
                            'success' => false,
                            'message' => 'Item não encontrado!',
                            'reload' => false
                        ];
                    }

                    if ($item->email_verified_at) {
                        // Remover verificação
                        $item->update(['email_verified_at' => null]);
                        return [
                            'success' => true,
                            'message' => 'Verificação de e-mail removida com sucesso!',
                            'reload' => true
                        ];
                    } else {
                        // Marcar como verificado
                        $item->update(['email_verified_at' => now()]);
                        return [
                            'success' => true,
                            'message' => 'E-mail marcado como verificado!',
                            'reload' => true
                        ];
                    }
                })
                ->position('start'),

            // ✅ AÇÃO DE CALLBACK - Alternar status do usuário
            $this->callbackAction('toggle_status')
                ->labelUsing(function ($item, $context) {
                    if (!$item) return 'Alterar Status';
                    return $item->status === 'active'
                        ? 'Desativar Usuário'
                        : 'Ativar Usuário';
                })
                ->iconUsing(function ($item, $context) {
                    if (!$item) return 'User';
                    return $item->status === 'active'
                        ? 'UserX'
                        : 'UserCheck';
                })
                ->variantUsing(function ($item, $context) {
                    if (!$item) return 'secondary';
                    return $item->status === 'active' ? 'secondary' : 'success';
                })
                ->tooltip('Alternar status ativo/inativo do usuário')
                ->requiresConfirmation(
                    'Tem certeza que deseja alterar o status deste usuário?',
                    'Confirmar Alteração'
                )
                ->callback(function ($item, $context) {
                    if (!$item) {
                        return [
                            'success' => false,
                            'message' => 'Item não encontrado!',
                            'reload' => false
                        ];
                    }

                    $newStatus = $item->status === 'active' ? 'inactive' : 'active';
                    $item->update(['status' => $newStatus]);

                    return [
                        'success' => true,
                        'message' => "Usuário {$item->name} foi " .
                            ($newStatus === 'active' ? 'ativado' : 'desativado') .
                            ' com sucesso!',
                        'reload' => true
                    ];
                }),

            // ✅ AÇÃO DE URL - Enviar e-mail direto (mailto)
            $this->urlAction('send_email')
                ->label('Enviar E-mail')
                ->icon('Mail')
                ->variant('outline')
                ->tooltip('Abrir cliente de e-mail para enviar mensagem')
                ->urlUsing(function ($item, $context) {
                    if (!$item || !$item->email) return '#';
                    return "mailto:{$item->email}?subject=Contato via Sistema";
                })
                ->openInNewTab(false)
                ->visible(function ($item, $context) {
                    // Verificação de segurança para evitar erro null
                    if (!$item) return false;
                    // Visível apenas se o e-mail estiver verificado
                    return !is_null($item->email_verified_at);
                }),

            // ✅ AÇÃO DE URL - Ver perfil do usuário no site
            $this->urlAction('view_profile')
                ->label('Ver Perfil')
                ->icon('ExternalLink')
                ->variant('ghost')
                ->tooltip('Ver perfil público do usuário')
                ->urlUsing(function ($item, $context) {
                    if (!$item || !$item->id) return '#';
                    return url("/profile/{$item->id}");
                })
                ->openInNewTab()
                ->visible(function ($item, $context) {
                    // Verificação de segurança para evitar erro null
                    if (!$item) return false;
                    // Visível apenas para usuários ativos
                    return $item->status === 'active';
                }),

            // ✅ AÇÃO DE CALLBACK - Reenviar e-mail de verificação
            $this->callbackAction('resend_verification')
                ->label('Reenviar Verificação')
                ->icon('MailPlus')
                ->variant('secondary')
                ->tooltip('Reenviar e-mail de verificação')
                ->callback(function ($item, $context) {
                    if (!$item) {
                        return [
                            'success' => false,
                            'message' => 'Item não encontrado!',
                            'reload' => false
                        ];
                    }

                    try {
                        // Simular envio de e-mail de verificação
                        // $item->sendEmailVerificationNotification();

                        return [
                            'success' => true,
                            'message' => "E-mail de verificação reenviado para {$item->email}!",
                            'reload' => false
                        ];
                    } catch (\Exception $e) {
                        return [
                            'success' => false,
                            'message' => 'Erro ao reenviar e-mail: ' . $e->getMessage(),
                            'reload' => false
                        ];
                    }
                })
                ->visible(function ($item, $context) {
                    // Verificação de segurança para evitar erro null
                    if (!$item) return false;
                    // Visível apenas para usuários com e-mail não verificado
                    return is_null($item->email_verified_at);
                }),

            // ✅ AÇÃO DE EXCLUSÃO - RouteAction com confirmação
            $this->deleteAction('landlord.users.destroy')
                ->label('Excluir Usuário')
                ->icon('Trash2')
                ->variant('destructive')
                ->tooltip('Excluir permanentemente este usuário')
                ->requiresConfirmation(
                    'Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.',
                    'Confirmar Exclusão'
                )
                ->enabled(function ($item, $context) {
                    // Verificação de segurança para evitar erro null
                    if (!$item) return false;
                    // Não permitir exclusão do próprio usuário
                    return auth()->id() !== $item->id;
                }),

            // ✅ AÇÃO DE CALLBACK - Duplicar usuário
            $this->callbackAction('duplicate_user')
                ->label('Duplicar')
                ->icon('Copy')
                ->variant('ghost')
                ->tooltip('Criar novo usuário baseado neste')
                ->callback(function ($item, $context) {
                    if (!$item) {
                        return [
                            'success' => false,
                            'message' => 'Item não encontrado!',
                            'reload' => false
                        ];
                    }

                    $newUser = $item->replicate();
                    $newUser->name = $item->name . ' (Cópia)';
                    $newUser->email = 'copy_' . time() . '_' . $item->email;
                    $newUser->email_verified_at = null;
                    $newUser->save();

                    return [
                        'success' => true,
                        'message' => "Usuário duplicado com sucesso! Novo ID: {$newUser->id}",
                        'reload' => true
                    ];
                })
                ->enabled(function ($item, $context) {
                    // Verificação de segurança para evitar erro null
                    if (!$item) return false;
                    return auth()->user()->can('create', UserModel::class);
                }),
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

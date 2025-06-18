<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Tables;

use App\Models\User;
use Callcocam\ReactPapaLeguas\Support\Table\Table; 

/**
 * Tabela Papa Leguas para Usuários
 */
class UserTable extends Table
{
    /**
     * Configurar a tabela
     */
    protected function setUp(): void
    {
        $this->id('users-table')
             ->model(User::class);

        // Configurações básicas de colunas
        $this->textColumn('id', 'ID')
            ->sortable()
            ->width('80px')
            ->alignCenter();

        $this->textColumn('name', 'Nome')
            ->sortable()
            ->searchable()
            ->icon('user');

        $this->textColumn('email', 'E-mail')
            ->sortable()
            ->searchable()
            ->icon('mail');

        $this->badgeColumn('email_verified_at', 'Status')
            ->colors([
                'verified' => 'green',
                'pending' => 'yellow',
            ])
            ->labels([
                'verified' => 'Verificado',
                'pending' => 'Pendente',
            ])
            ->format(function ($value) {
                return $value ? 'verified' : 'pending';
            });

        $this->dateColumn('created_at', 'Criado em')
            ->sortable();

        // Configurações da tabela
        $this->searchable()
            ->sortable()
            ->filterable()
            ->selectable();
    }
} 
import React, { useState } from 'react';
import PapaLeguasTable from '../index';
import { TableColumn, TableRow, TableAction, TableFilter } from '../types';

/**
 * Exemplo simples de uso da Tabela Papa Leguas
 * 
 * Este exemplo mostra como usar a tabela com dados mock
 * Em uma aplicação real, os dados viriam do backend via Inertia
 */
export function SimpleExample() {
    const [loading, setLoading] = useState(false);

    // Dados mock (em produção viriam do backend)
    const data: TableRow[] = [
        {
            id: 1,
            name: 'João Silva',
            email: 'joao@example.com',
            status: 'ativo',
            created_at: '2024-01-15T10:30:00Z',
            is_admin: true,
        },
        {
            id: 2,
            name: 'Maria Santos',
            email: 'maria@example.com',
            status: 'inativo',
            created_at: '2024-01-10T14:20:00Z',
            is_admin: false,
        },
        {
            id: 3,
            name: 'Pedro Costa',
            email: 'pedro@example.com',
            status: 'ativo',
            created_at: '2024-01-05T09:15:00Z',
            is_admin: false,
        },
    ];

    // Configuração das colunas
    const columns: TableColumn[] = [
        {
            key: 'id',
            label: 'ID',
            type: 'text',
            sortable: true,
            width: '80px',
            align: 'center',
        },
        {
            key: 'name',
            label: 'Nome',
            type: 'text',
            sortable: true,
            searchable: true,
        },
        {
            key: 'email',
            label: 'E-mail',
            type: 'text',
            sortable: true,
            searchable: true,
            formatConfig: {
                icon: 'mail',
                copyable: true,
            },
        },
        {
            key: 'status',
            label: 'Status',
            type: 'badge',
            sortable: true,
            formatConfig: {
                colors: {
                    ativo: 'success',
                    inativo: 'secondary',
                },
            },
        },
        {
            key: 'is_admin',
            label: 'Admin',
            type: 'boolean',
            align: 'center',
            formatConfig: {
                trueIcon: 'shield-check',
                falseIcon: 'shield-x',
                trueColor: 'text-green-600',
                falseColor: 'text-gray-400',
            },
        },
        {
            key: 'created_at',
            label: 'Criado em',
            type: 'date',
            sortable: true,
            formatConfig: {
                dateFormat: 'dd/MM/yyyy HH:mm',
            },
        },
    ];

    // Filtros disponíveis
    const filters: TableFilter[] = [
        {
            key: 'search',
            label: 'Buscar',
            type: 'text',
            placeholder: 'Buscar por nome ou e-mail...',
        },
        {
            key: 'status',
            label: 'Status',
            type: 'select',
            placeholder: 'Todos os status',
            options: [
                { value: 'ativo', label: 'Ativo' },
                { value: 'inativo', label: 'Inativo' },
            ],
        },
        {
            key: 'is_admin',
            label: 'Apenas Admins',
            type: 'boolean',
        },
    ];

    // Ações do cabeçalho
    const headerActions: TableAction[] = [
        {
            key: 'create',
            label: 'Novo Usuário',
            icon: 'plus',
            color: 'primary',
            route: 'users.create',
        },
        {
            key: 'export',
            label: 'Exportar',
            icon: 'download',
            variant: 'outline',
            route: 'users.export',
        },
    ];

    // Ações das linhas
    const rowActions: TableAction[] = [
        {
            key: 'view',
            label: 'Visualizar',
            icon: 'eye',
            variant: 'ghost',
        },
        {
            key: 'edit',
            label: 'Editar',
            icon: 'edit',
            variant: 'ghost',
        },
        {
            key: 'delete',
            label: 'Excluir',
            icon: 'trash-2',
            variant: 'ghost',
            color: 'danger',
            confirm: true,
            confirmTitle: 'Confirmar exclusão',
            confirmMessage: 'Tem certeza que deseja excluir este usuário?',
        },
    ];

    // Handlers
    const handleFilterChange = (filters: Record<string, any>) => {
        console.log('Filtros alterados:', filters);
        // Em produção: fazer nova requisição com filtros
    };

    const handleSortChange = (column: string, direction: 'asc' | 'desc') => {
        console.log('Ordenação alterada:', { column, direction });
        // Em produção: fazer nova requisição com ordenação
    };

    const handleActionClick = (action: TableAction, row?: TableRow) => {
        console.log('Ação clicada:', { action: action.key, row: row?.id });
        
        // Simular loading para demonstração
        setLoading(true);
        setTimeout(() => setLoading(false), 1000);
        
        // Em produção: executar a ação (navegar, fazer requisição, etc.)
    };

    return (
        <div className="space-y-6">
            <div>
                <h2 className="text-2xl font-bold">Exemplo da Tabela Papa Leguas</h2>
                <p className="text-muted-foreground">
                    Demonstração da tabela simplificada integrada com o backend
                </p>
            </div>

            <PapaLeguasTable
                data={data}
                columns={columns}
                filters={filters}
                actions={{
                    header: headerActions,
                    row: rowActions,
                }}
                loading={loading}
                onFilterChange={handleFilterChange}
                onSortChange={handleSortChange}
                onActionClick={handleActionClick}
            />
        </div>
    );
}
import React from 'react';
import KanbanRenderer from '../renderers/KanbanRenderer';
import type { KanbanColumn, KanbanAction, KanbanFilter } from '../types';

interface UserPostsKanbanProps {
    /** Dados dos usuários (eager loaded) */
    users: any[];
    /** Callback para ações */
    onAction?: (action: string, item: any, extra?: any) => void;
    /** Callback para filtros */
    onFilter?: (filters: Record<string, any>) => void;
    /** Callback para refresh */
    onRefresh?: () => void;
}

/**
 * Exemplo específico de Kanban para sistema de Usuários e Posts.
 * 
 * Demonstra:
 * - Configuração de colunas baseada em critérios de negócio
 * - Actions específicas do domínio
 * - Filtros contextuais
 * - Uso de dados eager loaded
 */
export default function UserPostsKanban({
    users,
    onAction,
    onFilter,
    onRefresh
}: UserPostsKanbanProps) {
    
    // Configuração das colunas específicas para usuários
    const columns: KanbanColumn[] = [
        {
            id: 'new_users',
            title: 'Usuários Novos',
            key: 'created_at',
            icon: 'UserPlus',
            color: '#3b82f6',
            maxItems: 10,
            filter: (user) => {
                const thirtyDaysAgo = new Date();
                thirtyDaysAgo.setDate(thirtyDaysAgo.getDate() - 30);
                return new Date(user.created_at) > thirtyDaysAgo;
            }
        },
        {
            id: 'active_users',
            title: 'Usuários Ativos',
            key: 'email_verified_at',
            icon: 'Users',
            color: '#10b981',
            filter: (user) => user.posts_count > 0 && user.email_verified_at
        },
        {
            id: 'prolific_users',
            title: 'Usuários Prolíficos',
            key: 'posts_count',
            icon: 'Crown',
            color: '#f59e0b',
            maxItems: 5,
            filter: (user) => user.posts_count >= 5
        },
        {
            id: 'inactive_users',
            title: 'Usuários Inativos',
            key: 'email_verified_at',
            icon: 'UserX',
            color: '#ef4444',
            filter: (user) => user.posts_count === 0 || !user.email_verified_at
        }
    ];

    // Ações específicas para usuários
    const actions: KanbanAction[] = [
        {
            id: 'view_profile',
            label: 'Ver Perfil',
            icon: 'Eye',
            variant: 'outline',
            tooltip: 'Visualizar perfil do usuário'
        },
        {
            id: 'edit_user',
            label: 'Editar',
            icon: 'Edit',
            variant: 'outline',
            tooltip: 'Editar dados do usuário'
        },
        {
            id: 'view_posts',
            label: 'Ver Posts',
            icon: 'FileText',
            variant: 'outline',
            tooltip: 'Visualizar posts do usuário',
            visible: (user) => user.posts_count > 0
        },
        {
            id: 'send_email',
            label: 'Enviar Email',
            icon: 'Mail',
            variant: 'outline',
            tooltip: 'Enviar email para o usuário'
        },
        {
            id: 'deactivate_user',
            label: 'Desativar',
            icon: 'UserX',
            variant: 'destructive',
            tooltip: 'Desativar usuário',
            confirmation: {
                title: 'Desativar Usuário',
                message: 'Tem certeza que deseja desativar este usuário?',
                confirm_text: 'Desativar',
                cancel_text: 'Cancelar',
                confirm_variant: 'destructive'
            },
            visible: (user) => user.email_verified_at
        }
    ];

    // Filtros específicos para usuários
    const filters: KanbanFilter[] = [
        {
            id: 'email_verified_at',
            label: 'Status',
            type: 'select',
            options: [
                { value: 'verified', label: 'Verificado' },
                { value: 'unverified', label: 'Não Verificado' }
            ]
        },
        {
            id: 'posts_count',
            label: 'Posts',
            type: 'select',
            options: [
                { value: '0', label: 'Sem posts' },
                { value: '1-4', label: '1-4 posts' },
                { value: '5+', label: '5+ posts' }
            ]
        },
        {
            id: 'name',
            label: 'Nome',
            type: 'text'
        }
    ];

    // Configurações específicas do board
    const config = {
        searchable: true,
        filterable: true,
        height: '700px',
        columnsPerRow: 4,
        dragAndDrop: false
    };

    // Metadados do board
    const meta = {
        title: 'Gestão de Usuários',
        description: 'Organize usuários por status e atividade',
        key: 'users'
    };

    return (
        <div className="user-posts-kanban">
            {/* Header personalizado (opcional) */}
            <div className="mb-6 bg-white p-4 rounded-lg border">
                <h1 className="text-2xl font-bold text-gray-900 mb-2">
                    Sistema de Gestão de Usuários
                </h1>
                <p className="text-gray-600">
                    Visualize e gerencie usuários organizados por status e atividade no sistema.
                </p>
                
                {/* Estatísticas rápidas */}
                <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                    <div className="text-center">
                        <div className="text-2xl font-bold text-blue-600">
                            {users.filter(columns[0].filter!).length}
                        </div>
                        <div className="text-sm text-gray-500">Novos</div>
                    </div>
                    <div className="text-center">
                        <div className="text-2xl font-bold text-green-600">
                            {users.filter(columns[1].filter!).length}
                        </div>
                        <div className="text-sm text-gray-500">Ativos</div>
                    </div>
                    <div className="text-center">
                        <div className="text-2xl font-bold text-yellow-600">
                            {users.filter(columns[2].filter!).length}
                        </div>
                        <div className="text-sm text-gray-500">Prolíficos</div>
                    </div>
                    <div className="text-center">
                        <div className="text-2xl font-bold text-red-600">
                            {users.filter(columns[3].filter!).length}
                        </div>
                        <div className="text-sm text-gray-500">Inativos</div>
                    </div>
                </div>
            </div>

            {/* Kanban Board */}
            <KanbanRenderer
                data={users}
                columns={columns}
                actions={actions}
                filters={filters}
                config={config}
                meta={meta}
                onAction={onAction}
                onFilter={onFilter}
                onRefresh={onRefresh}
            />

            {/* Footer com dicas (opcional) */}
            <div className="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 className="font-medium text-blue-900 mb-2">
                    💡 Dicas de uso:
                </h3>
                <ul className="text-sm text-blue-800 space-y-1">
                    <li>• <strong>Busque</strong> por nome, email ou qualquer campo</li>
                    <li>• <strong>Filtre</strong> por status de verificação ou quantidade de posts</li>
                    <li>• <strong>Clique nas ações</strong> para gerenciar usuários específicos</li>
                    <li>• <strong>Observe os limites</strong> por coluna para melhor organização</li>
                </ul>
            </div>
        </div>
    );
} 
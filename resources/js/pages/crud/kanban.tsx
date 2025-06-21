import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '../../layouts/react-app-layout';
import { type BreadcrumbItem } from '../../types';
import { KanbanBoard } from '../../components/papa-leguas/kanban';
import type { KanbanColumn } from '../../components/papa-leguas/kanban/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'CRUD',
        href: '/crud',
    },
    {
        title: 'Kanban',
    }
];

interface CrudKanbanProps {
    table?: {
        data?: any[];
        columns?: any[];
        filters?: any[];
        actions?: any;
        pagination?: any;
        route_execute_action?: string;
        meta?: {
            title?: string;
            description?: string;
            searchable?: boolean;
            sortable?: boolean;
            filterable?: boolean;
        };
    };
    routes?: {
        index?: string;
        create?: string;
        store?: string;
        show?: (id: string | number) => string;
        edit?: (id: string | number) => string;
        update?: (id: string | number) => string;
        destroy?: (id: string | number) => string;
        export?: string;
        bulk_destroy?: string;
    };
    config?: {
        model_name?: string;
        page_title?: string;
        page_description?: string;
        route_prefix?: string;
        can_create?: boolean;
        can_edit?: boolean;
        can_delete?: boolean;
        can_export?: boolean;
        can_bulk_delete?: boolean;
    };
    capabilities?: {
        searchable_columns?: string[];
        sortable_columns?: string[];
        filterable_columns?: string[];
    };
    error?: string;
}

export default function CrudKanban({ table, routes, config, capabilities, error }: CrudKanbanProps) {
    
    // 🔍 DEBUG: Ver dados vindos do backend
    React.useEffect(() => {
        console.log('📊 DEBUG - Dados Kanban do Backend:');
        console.log('table?.data:', table?.data?.length, 'items');
        console.log('table?.columns:', table?.columns?.length, 'columns');
        console.log('table?.actions:', table?.actions);
        console.log('config:', config);
        console.log('routes:', routes);
    }, [table, config, routes]);

    // 🎯 Converter dados da table para formato Kanban
    const kanbanData = table?.data || [];
    
    // 🎯 Definir colunas Kanban baseadas no status (para tickets)
    // TODO: Isso deve vir do backend via enum getKanbanColumns()
    const kanbanColumns: KanbanColumn[] = [
        {
            id: 'open',
            title: 'Aberto',
            key: 'status',
            color: '#3b82f6',
            icon: 'AlertCircle',
            filter: (item) => item.status?.value === 'open'
        },
        {
            id: 'in_progress',
            title: 'Em Andamento',
            key: 'status',
            color: '#f59e0b',
            icon: 'Clock',
            filter: (item) => item.status?.value === 'in_progress'
        },
        {
            id: 'waiting',
            title: 'Aguardando Resposta',
            key: 'status',
            color: '#8b5cf6',
            icon: 'MessageCircle',
            filter: (item) => item.status?.value === 'waiting'
        },
        {
            id: 'resolved',
            title: 'Resolvido',
            key: 'status',
            color: '#10b981',
            icon: 'CheckCircle',
            filter: (item) => item.status?.value === 'resolved'
        },
        {
            id: 'closed',
            title: 'Fechado',
            key: 'status',
            color: '#6b7280',
            icon: 'XCircle',
            filter: (item) => item.status?.value === 'closed'
        }
    ];

    // 🎯 Configurações do Kanban
    const kanbanConfig = {
        searchable: table?.meta?.searchable ?? true,
        filterable: table?.meta?.filterable ?? true,
        height: '700px',
        columnsPerRow: 4,
        dragAndDrop: false // Futuro: implementar drag and drop
    };

    // 🎯 Metadados do Kanban
    const kanbanMeta = {
        title: table?.meta?.title || config?.page_title || 'Kanban',
        description: table?.meta?.description || config?.page_description || 'Visualização em Kanban',
        key: config?.route_prefix || 'kanban'
    };

    // 🎯 Handlers
    const handleAction = (actionId: string, item: any, extra?: any) => {
        console.log('🎬 Kanban Action:', actionId, item, extra);
        // TODO: Implementar execução de ações
    };

    const handleFilter = (filters: Record<string, any>) => {
        console.log('🔍 Kanban Filter:', filters);
        // TODO: Implementar filtros
    };

    const handleRefresh = () => {
        console.log('🔄 Kanban Refresh');
        // TODO: Implementar refresh
        window.location.reload();
    };

    return (
        <AppLayout 
            breadcrumbs={breadcrumbs}
            title={config?.page_title || 'Kanban'}
        >
            <Head title={`${config?.page_title || 'Kanban'} - Board`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {kanbanMeta.title}
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">
                            {kanbanMeta.description}
                        </p>
                    </div>
                </div>

                {/* Error State */}
                {error && (
                    <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                        <p className="text-red-700">{error}</p>
                    </div>
                )}

                {/* Kanban Board Papa Leguas */}
                <KanbanBoard
                    data={kanbanData}
                    columns={kanbanColumns}
                    actions={table?.actions || []}
                    filters={table?.filters || []}
                    config={kanbanConfig}
                    meta={kanbanMeta}
                    onAction={handleAction}
                    onFilter={handleFilter}
                    onRefresh={handleRefresh}
                />
            </div>
        </AppLayout>
    );
}
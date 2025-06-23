import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '../../layouts/react-app-layout';
import { type BreadcrumbItem } from '../../types';
import { DataTable } from '../../components/papa-leguas';
import TabbedInterface from '../../components/ui/tabbed-interface';
import ViewSelector from '../../components/ui/view-selector';
import CardView from '../../components/ui/card-view';
import KanbanView from '../../components/ui/kanban-view';
import { KanbanBoard } from '../../components/papa-leguas/kanban';
import type { KanbanColumn } from '../../components/papa-leguas/kanban/types';
import { TabConfig, TabsConfig, TabbedTableData, ViewConfig, ViewsConfig } from '../../types';

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
        title: 'Lista',
    }
];

interface CrudIndexProps extends TabbedTableData {
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

export default function CrudIndex({ table, routes, config, capabilities, error, tabs, tabsConfig, views, viewsConfig, activeView }: CrudIndexProps) {
    
    // 🎯 Definir colunas Kanban baseadas no workflow (se houver currentWorkflow nos dados)
    const getKanbanColumns = (): KanbanColumn[] => {
        // Verificar se os dados têm currentWorkflow (sistema de tickets/workflows)
        const hasWorkflow = table?.data?.some(item => item.currentWorkflow);
        
        if (hasWorkflow) {
            // Colunas específicas para workflow
            return [
                {
                    id: 'aberto',
                    title: 'Aberto',
                    key: 'status',
                    color: '#ef4444',
                    icon: 'AlertCircle',
                    filter: (item) => {
                        return item.currentWorkflow?.status === 'active' && 
                               (item.currentWorkflow?.current_step === 1 || 
                                item.currentWorkflow?.current_template_id?.includes('aberto'));
                    }
                },
                {
                    id: 'em-andamento',
                    title: 'Em Andamento',
                    key: 'status',
                    color: '#f59e0b',
                    icon: 'Clock',
                    filter: (item) => {
                        return item.currentWorkflow?.status === 'active' && 
                               (item.currentWorkflow?.current_step === 2 || 
                                item.currentWorkflow?.current_template_id?.includes('em-andamento'));
                    }
                },
                {
                    id: 'aguardando-cliente',
                    title: 'Aguardando Cliente',
                    key: 'status',
                    color: '#8b5cf6',
                    icon: 'User',
                    filter: (item) => {
                        return item.currentWorkflow?.status === 'active' && 
                               (item.currentWorkflow?.current_step === 3 || 
                                item.currentWorkflow?.current_template_id?.includes('aguardando'));
                    }
                },
                {
                    id: 'resolvido',
                    title: 'Resolvido',
                    key: 'status',
                    color: '#10b981',
                    icon: 'CheckCircle',
                    filter: (item) => {
                        return item.currentWorkflow?.status === 'active' && 
                               (item.currentWorkflow?.current_step === 4 || 
                                item.currentWorkflow?.current_template_id?.includes('resolvido'));
                    }
                },
                {
                    id: 'fechado',
                    title: 'Fechado',
                    key: 'status',
                    color: '#6b7280',
                    icon: 'Archive',
                    filter: (item) => {
                        return item.currentWorkflow?.status === 'completed' || 
                               item.currentWorkflow?.current_step === 5 ||
                               item.currentWorkflow?.current_template_id?.includes('fechado');
                    }
                }
            ];
        }
        
        // Colunas genéricas baseadas no campo 'status' tradicional
        return [
            {
                id: 'ativo',
                title: 'Ativo',
                key: 'status',
                color: '#10b981',
                icon: 'CheckCircle',
                filter: (item) => item.status === 'active' || item.status?.value === 'active'
            },
            {
                id: 'inativo',
                title: 'Inativo',
                key: 'status',
                color: '#6b7280',
                icon: 'XCircle',
                filter: (item) => item.status === 'inactive' || item.status?.value === 'inactive'
            },
            {
                id: 'pendente',
                title: 'Pendente',
                key: 'status',
                color: '#f59e0b',
                icon: 'Clock',
                filter: (item) => item.status === 'pending' || item.status?.value === 'pending'
            }
        ];
    };
    
    // 🎨 Renderizar view baseada na view ativa
    const renderView = (tabData?: any, viewId?: string) => {
        const currentView = viewId || activeView || 'list';
        const data = table?.data || [];
        const columns = table?.columns || [];
        const actions = table?.actions || [];
        
        // Encontrar configuração da view ativa
        const viewConfig = views?.find(v => v.id === currentView); 
        
        switch (currentView) {
            case 'cards':
                return (
                    <CardView
                        data={data}
                        columns={columns}
                        config={viewConfig?.config || {}}
                        actions={actions}
                    />
                );
                
            case 'kanban':
                // Usar KanbanBoard avançado com filtros inteligentes
                return (
                    <KanbanBoard
                        data={data}
                        columns={getKanbanColumns()}
                        actions={actions}
                        config={{
                            title: table?.meta?.title || config?.page_title || 'Kanban',
                            description: table?.meta?.description || config?.page_description || 'Visualização em quadro',
                            searchable: true,
                            refreshable: true,
                        }}
                        onAction={(actionId, item, extra) => {
                            // TODO: Implementar ações do Kanban
                        }}
                        onRefresh={() => {
                            window.location.reload();
                        }}
                    />
                );
                
            case 'list':
            default:
                return (
                    <DataTable
                        data={data}
                        columns={columns}
                        filters={tabData?.filters || table?.filters || []}
                        actions={actions}
                        loading={false}
                        error={error}
                        meta={tabData?.meta || table?.meta}
                    />
                );
        }
    };

    return (
        <AppLayout 
            breadcrumbs={breadcrumbs}
            title={config?.page_title || 'CRUD'}
        >
            <Head title={`${config?.page_title || 'CRUD'} - ${activeView === 'kanban' ? 'Kanban' : activeView === 'cards' ? 'Cards' : 'Lista'}`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {table?.meta?.title || config?.page_title || 'CRUD'}
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">
                            {table?.meta?.description || config?.page_description || 'Gerencie seus dados'}
                        </p>
                    </div>
                    
                    {/* Seletor de Views */}
                    {views && views.length > 0 && (
                        <ViewSelector
                            views={views}
                            activeView={activeView || 'list'}
                            config={viewsConfig}
                        />
                    )}
                </div>

                {/* Sistema de Tabs ou View Direta */}
                <TabbedInterface
                    tabs={tabs || []}
                    config={tabsConfig}
                    defaultContent={renderView()}
                >
                    {(activeTab, tabContent) => {
                        // Se a tab tem conteúdo próprio, renderizar ele
                        if (tabContent) {
                            return renderView(tabContent);
                        }
                        
                        // Se a tab não tem conteúdo, renderizar view padrão
                        return renderView();
                    }}
                </TabbedInterface>
            </div>
        </AppLayout>
    );
}
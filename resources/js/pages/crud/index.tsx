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
import { KanbanBoardProps } from '../../components/papa-leguas/kanban';

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
    console.log('views', views);
    
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
                // Usar configurações que vêm do backend nas views
                const kanbanColumns: KanbanColumn[] = Array.isArray(viewConfig?.config?.columns) 
                    ? viewConfig.config.columns 
                    : [];
                
                return (
                    <KanbanBoard
                        data={data}
                        columns={kanbanColumns}
                        tableColumns={columns}
                        actions={actions}
                        config={viewConfig?.config || {}}
                        meta={table?.meta}
                        onAction={(actionId, item, extra) => {
                            console.log('🎯 Kanban Action:', { actionId, item, extra });
                            // TODO: Implementar ações do Kanban
                        }}
                        onRefresh={() => {
                            console.log('🔄 Refreshing Kanban');
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
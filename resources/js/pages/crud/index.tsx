import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '../../layouts/react-app-layout';
import { type BreadcrumbItem } from '../../types'; 
import TabbedInterface from '../../components/ui/tabbed-interface';
import ViewSelector from '../../components/ui/view-selector'; 
import {   TabbedTableData } from '../../types';
import RendererView from '../../components/ui/views/renderer-view';

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

    // 🎨 Renderizar view baseada na view ativa
    const renderView = (viewId?: string) => {
        const currentView = viewId || activeView || 'list';
        const viewConfig = views?.find(v => v.id === currentView); 
        
        return <RendererView 
            view={currentView} 
            data={table?.data} 
            columns={table?.columns} 
            config={viewConfig?.config} 
            actions={table?.actions} 
            className={''} 
            meta={table?.meta}
        />;
    }

    return (
        <AppLayout
            breadcrumbs={breadcrumbs}
            title={config?.page_title || 'CRUD'}
            fullWidth={true}
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
                        console.log('Tab ativa:', activeTab);
                        console.log('Tab content:', tabContent);
                        
                        // Sempre renderizar a view baseada no activeView, não no tabContent
                        return renderView();
                    }}
                </TabbedInterface>
            </div>
        </AppLayout>
    );
}
import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '../../layouts/react-app-layout';
import { type BreadcrumbItem } from '../../types';
import { DataTable } from '../../components/papa-leguas';
import TabbedInterface from '@/components/ui/tabbed-interface';
import { TabConfig, TabsConfig, TabbedTableData } from '@/types';

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

export default function CrudIndex({ table, routes, config, capabilities, error, tabs, tabsConfig }: CrudIndexProps) {
    
    // 🔍 DEBUG: Ver dados vindos do backend
    React.useEffect(() => {
        console.log('📊 DEBUG - Dados do Backend:');
        console.log('table?.actions:', table?.actions.bulk);
        console.log('table?.route_execute_action:', table?.route_execute_action);
        console.log('table?.data:', table?.data?.length, 'items');
        console.log('table?.columns:', table?.columns?.length, 'columns');
        console.log('config:', config);
        console.log('routes:', routes);
        console.log('🔗 tabs:', tabs?.length, 'tabs configuradas');
        console.log('⚙️ tabsConfig:', tabsConfig);
    }, [table, config, routes, tabs, tabsConfig]);
    
    // ✅ USAR AÇÕES DO BACKEND - Sistema de Ações Avançado
    
    // 🎨 Renderizar DataTable (usado tanto nas tabs quanto no fallback)
    const renderDataTable = (tabData?: any) => (
        <DataTable
            data={tabData?.data || table?.data || []}
            columns={tabData?.columns || table?.columns || []}
            filters={tabData?.filters || table?.filters || []}
            actions={tabData?.actions || table?.actions || []}
            loading={false}
            error={error}
            meta={tabData?.meta || table?.meta}
        />
    );

    return (
        <AppLayout 
            breadcrumbs={breadcrumbs}
            title={config?.page_title || 'CRUD'}
        >
            <Head title={`${config?.page_title || 'CRUD'} - Lista`} />
            
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
                </div>

                {/* Sistema de Tabs ou DataTable Direta */}
                <TabbedInterface
                    tabs={tabs || []}
                    config={tabsConfig}
                    defaultContent={renderDataTable()}
                >
                    {(activeTab, tabContent) => {
                        console.log('🎯 DEBUG - Tab Ativa:', activeTab.id, activeTab.label);
                        console.log('📋 DEBUG - Conteúdo da Tab:', tabContent);
                        
                        // Se a tab tem conteúdo próprio, renderizar ele
                        if (tabContent) {
                            return renderDataTable(tabContent);
                        }
                        
                        // Se a tab não tem conteúdo, renderizar DataTable padrão
                        return renderDataTable();
                    }}
                </TabbedInterface>
            </div>
        </AppLayout>
    );
}
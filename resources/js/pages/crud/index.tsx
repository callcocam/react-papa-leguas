import React from 'react';
import { Head } from '@inertiajs/react';
import AppLayout from '../../layouts/react-app-layout';
import { type BreadcrumbItem } from '../../types';
import { DataTable } from '../../components/papa-leguas';

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

interface CrudIndexProps {
    table?: {
        data?: any[];
        columns?: any[];
        filters?: any[];
        actions?: any;
        pagination?: any;
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

export default function CrudIndex({ table, routes, config, capabilities, error }: CrudIndexProps) {
    
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

                {/* DataTable Modular Papa Leguas */}
                <DataTable
                    data={table?.data || []}
                    columns={table?.columns || []}
                    filters={table?.filters || []}
                    loading={false}
                    error={error}
                    meta={table?.meta}
                />
            </div>
        </AppLayout>
    );
}
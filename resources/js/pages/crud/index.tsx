import React from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../layouts/react-app-layout';
import PapaLeguasTable from '../../components/papa-leguas-table';
import { type BreadcrumbItem } from '../../types';
import { 
    TableColumn, 
    TableRow, 
    TableFilter, 
    TableActions, 
    TablePagination,
    TableAction 
} from '../../components/papa-leguas-table/types';

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
    table: {
        data: TableRow[];
        columns: TableColumn[];
        filters?: TableFilter[];
        actions?: TableActions;
        pagination?: TablePagination;
        meta?: {
            title?: string;
            description?: string;
            searchable?: boolean;
            sortable?: boolean;
            filterable?: boolean;
        };
    };
    routes: {
        index: string;
        create: string;
        store: string;
        show: (id: string | number) => string;
        edit: (id: string | number) => string;
        update: (id: string | number) => string;
        destroy: (id: string | number) => string;
        export: string;
        bulk_destroy: string;
    };
    config: {
        model_name: string;
        page_title: string;
        page_description: string;
        route_prefix: string;
        can_create: boolean;
        can_edit: boolean;
        can_delete: boolean;
        can_export: boolean;
        can_bulk_delete: boolean;
    };
    error?: string;
}

export default function CrudIndex({ table, routes, config, error }: CrudIndexProps) {
    // Handlers para integração com o backend
    const handleFilterChange = (filters: Record<string, any>) => {
        router.get(routes.index, filters, {
            preserveState: true,
            preserveScroll: true,
            only: ['table'],
        });
    };

    const handleSortChange = (column: string, direction: 'asc' | 'desc') => {
        router.get(routes.index, {
            sort: column,
            direction,
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['table'],
        });
    };

    const handlePageChange = (page: number) => {
        router.get(routes.index, { 
            page 
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['table'],
        });
    };

    const handleActionClick = (action: TableAction, row?: TableRow) => {
        console.log('Ação executada:', { action: action.key, row: row?.id });
        
        switch (action.key) {
            case 'create':
                if (config.can_create) {
                    router.visit(routes.create);
                }
                break;
            
            case 'edit':
                if (row && config.can_edit) {
                    router.visit(routes.edit(row.id));
                }
                break;
            
            case 'view':
                if (row) {
                    router.visit(routes.show(row.id));
                }
                break;
            
            case 'delete':
                if (row && config.can_delete) {
                    router.delete(routes.destroy(row.id), {
                        onSuccess: () => {
                            // Recarregar dados após exclusão
                            router.reload({ only: ['table'] });
                        },
                    });
                }
                break;
            
            case 'export':
                if (config.can_export) {
                    // Fazer download do arquivo
                    window.open(routes.export, '_blank');
                }
                break;
            
            case 'bulk-delete':
                // Implementar exclusão em lote
                console.log('Exclusão em lote não implementada ainda');
                break;
            
            default:
                console.warn('Ação não reconhecida:', action.key);
        }
    };

    const handleBulkActionClick = (action: any, selectedRows: TableRow[]) => {
        console.log('Ação em lote executada:', { 
            action: action.key, 
            count: selectedRows.length,
            ids: selectedRows.map(row => row.id)
        });
        
        switch (action.key) {
            case 'bulk-delete':
                if (selectedRows.length > 0 && config.can_bulk_delete) {
                    router.delete(routes.bulk_destroy, {
                        data: {
                            ids: selectedRows.map(row => row.id)
                        },
                        onSuccess: () => {
                            router.reload({ only: ['table'] });
                        },
                    });
                }
                break;
            
            case 'bulk-export':
                if (selectedRows.length > 0 && config.can_export) {
                    const ids = selectedRows.map(row => row.id).join(',');
                    window.open(routes.export + `?ids=${ids}`, '_blank');
                }
                break;
            
            default:
                console.warn('Ação em lote não reconhecida:', action.key);
        }
    };

    return (
        <AppLayout 
            breadcrumbs={breadcrumbs}
            title={config.page_title}
        >
            <Head title={`${config.page_title} - Lista`} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {config.page_title}
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">
                            {config.page_description}
                        </p>
                    </div>
                </div>

                {/* Tabela Papa Leguas */}
                <PapaLeguasTable
                    data={table.data}
                    columns={table.columns}
                    filters={table.filters}
                    actions={table.actions}
                    pagination={table.pagination}
                    error={error}
                    onFilterChange={handleFilterChange}
                    onSortChange={handleSortChange}
                    onPageChange={handlePageChange}
                    onActionClick={handleActionClick}
                    onBulkActionClick={handleBulkActionClick}
                />
            </div>
        </AppLayout>
    );
}

import React from 'react';
import { Head, router } from '@inertiajs/react';
import DataTable from '../../components/papa-leguas/DataTable';
import { 
    TableColumn, 
    TableFilter, 
    TableAction 
} from '../../components/papa-leguas/types';

interface UsersProps {
    table: {
        data: any[];
        columns: TableColumn[];
        filters?: TableFilter[];
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
    error?: string;
}

export default function UsersIndex({ table, error }: UsersProps) {
    // Handlers para integração com o backend
    const handleFilterChange = (filters: Record<string, any>) => {
        router.get(route('users.index'), filters, {
            preserveState: true,
            preserveScroll: true,
            only: ['table'],
        });
    };

    const handleSortChange = (column: string, direction: 'asc' | 'desc') => {
        router.get(route('users.index'), {
            sort: column,
            direction,
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['table'],
        });
    };

    const handlePageChange = (page: number) => {
        router.get(route('users.index'), { 
            page 
        }, {
            preserveState: true,
            preserveScroll: true,
            only: ['table'],
        });
    };

    const handleActionClick = (action: TableAction, row?: any) => {
        console.log('Ação executada:', { action: action.key, row: row?.id });
        
        switch (action.key) {
            case 'create':
                router.visit(route('users.create'));
                break;
            
            case 'edit':
                if (row) {
                    router.visit(route('users.edit', row.id));
                }
                break;
            
            case 'view':
                if (row) {
                    router.visit(route('users.show', row.id));
                }
                break;
            
            case 'delete':
                if (row) {
                    router.delete(route('users.destroy', row.id), {
                        onSuccess: () => {
                            // Recarregar dados após exclusão
                            router.reload({ only: ['table'] });
                        },
                    });
                }
                break;
            
            case 'export':
                // Fazer download do arquivo
                window.open(route('users.export'), '_blank');
                break;
            
            default:
                console.warn('Ação não reconhecida:', action.key);
        }
    };

    const handleBulkActionClick = (action: any, selectedRows: any[]) => {
        console.log('Ação em lote executada:', { 
            action: action.key, 
            count: selectedRows.length,
            ids: selectedRows.map(row => row.id)
        });
        
        switch (action.key) {
            case 'bulk-delete':
                if (selectedRows.length > 0) {
                    router.delete(route('users.bulk-destroy'), {
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
                if (selectedRows.length > 0) {
                    const ids = selectedRows.map(row => row.id).join(',');
                    window.open(route('users.export') + `?ids=${ids}`, '_blank');
                }
                break;
            
            default:
                console.warn('Ação em lote não reconhecida:', action.key);
        }
    };

    return (
        <>
            <Head title={table.meta?.title || "Users"} />
            
            <div className="space-y-6">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {table.meta?.title || "Users"}
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">
                            {table.meta?.description || "Gerencie usuários de forma eficiente"}
                        </p>
                    </div>
                </div>

                {/* Tabela Papa Leguas */}
                <DataTable
                    data={table.data}
                    columns={table.columns}
                    filters={table.filters}
                    actions={table.actions}
                    error={error}
                />
            </div>
        </>
    );
}
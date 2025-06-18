import React from 'react';
import { Head } from '@inertiajs/react';
import { router } from '@inertiajs/react';
import { PapaLeguasTable } from '../../../components/papa-leguas-table';
import { PapaLeguasTableProps } from '../../../components/papa-leguas-table/types';

interface UsersIndexProps {
    table: PapaLeguasTableProps;
    error?: string;
}

/**
 * Página de listagem de usuários
 * Integrada com Papa Leguas Table e LandlordController
 */
export default function UsersIndex({ table, error }: UsersIndexProps) {
    // Handler para mudança de filtros
    const handleFilterChange = (filters: Record<string, any>) => {
        router.get(route('landlord.users.index'), filters, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    // Handler para mudança de ordenação
    const handleSortChange = (column: string, direction: 'asc' | 'desc') => {
        router.get(route('landlord.users.index'), {
            sort: column,
            direction: direction,
            ...route().params,
        }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    // Handler para mudança de página
    const handlePageChange = (page: number) => {
        router.get(route('landlord.users.index'), {
            page: page,
            ...route().params,
        }, {
            preserveState: true,
            preserveScroll: true,
        });
    };

    // Handler para ações individuais
    const handleActionClick = (action: any, row?: any) => {
        if (action.confirm) {
            if (!confirm(action.confirmMessage || 'Tem certeza?')) {
                return;
            }
        }

        const routeName = action.route;
        const method = action.method || 'GET';

        if (method === 'DELETE') {
            router.delete(route(routeName, row?.id), {
                preserveScroll: true,
            });
        } else if (method === 'POST') {
            router.post(route(routeName, row?.id), {}, {
                preserveScroll: true,
            });
        } else {
            router.visit(route(routeName, row?.id));
        }
    };

    // Handler para ações em lote
    const handleBulkActionClick = (action: any, selectedRows: any[]) => {
        if (action.confirm) {
            if (!confirm(action.confirmMessage || 'Tem certeza?')) {
                return;
            }
        }

        const ids = selectedRows.map(row => row.id);
        const routeName = action.route;
        const method = action.method || 'POST';

        if (method === 'DELETE') {
            router.delete(route(routeName), {
                data: { ids },
                preserveScroll: true,
            });
        } else {
            router.post(route(routeName), {
                ids: ids,
            }, {
                preserveScroll: true,
            });
        }
    };

    return (
        <>
            <Head title="Usuários" />

            <div className="space-y-6">
                {/* Cabeçalho da página */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            Usuários
                        </h1>
                        <p className="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            Gerencie os usuários do sistema
                        </p>
                    </div>
                </div>

                {/* Mensagem de erro global */}
                {error && (
                    <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                        <h3 className="font-medium">Erro</h3>
                        <p className="mt-1 text-sm">{error}</p>
                    </div>
                )}

                {/* Tabela Papa Leguas */}
                <PapaLeguasTable
                    {...table}
                    onFilterChange={handleFilterChange}
                    onSortChange={handleSortChange}
                    onPageChange={handlePageChange}
                    onActionClick={handleActionClick}
                    onBulkActionClick={handleBulkActionClick}
                />
            </div>
        </>
    );
} 
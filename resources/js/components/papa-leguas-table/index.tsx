import React from 'react';
import { PapaLeguasTableProps } from './types';
import { TableHeader } from './components/TableHeader';
import { TableBody } from './components/TableBody';
import { TablePagination } from './components/TablePagination';
import { TableFilters } from './components/TableFilters';
import { TableActions } from './components/TableActions';

/**
 * Componente principal da Tabela Papa Leguas
 * 
 * Versão simplificada que se integra diretamente com o backend PHP.
 * Todos os dados são processados no servidor usando as classes Table do Papa Leguas.
 */
export function PapaLeguasTable({
    data,
    columns,
    filters,
    actions,
    pagination,
    loading = false,
    error = null,
    className = '',
    onFilterChange,
    onSortChange,
    onPageChange,
    onActionClick,
    onBulkActionClick,
}: PapaLeguasTableProps) {
    if (error) {
        return (
            <div className="rounded-lg border border-red-200 bg-red-50 p-4 text-red-800 dark:border-red-800 dark:bg-red-950 dark:text-red-200">
                <h3 className="font-medium">Erro ao carregar dados</h3>
                <p className="mt-1 text-sm">{error}</p>
            </div>
        );
    }

    return (
        <div className={`space-y-4 ${className}`}>
            {/* Filtros */}
            {filters && filters.length > 0 && (
                <TableFilters
                    filters={filters}
                    onFilterChange={onFilterChange}
                    loading={loading}
                />
            )}

            {/* Ações do Cabeçalho */}
            {actions?.header && actions.header.length > 0 && (
                <TableActions
                    actions={actions.header}
                    type="header"
                    onActionClick={onActionClick}
                    loading={loading}
                />
            )}

            {/* Tabela */}
            <div className="rounded-lg border bg-card">
                <div className="overflow-x-auto">
                    <table className="w-full">
                        <TableHeader
                            columns={columns}
                            onSortChange={onSortChange}
                            loading={loading}
                        />
                        <TableBody
                            data={data}
                            columns={columns}
                            actions={actions}
                            onActionClick={onActionClick}
                            onBulkActionClick={onBulkActionClick}
                            loading={loading}
                        />
                    </table>
                </div>

                {/* Estado vazio */}
                {!loading && (!data || data.length === 0) && (
                    <div className="flex flex-col items-center justify-center py-12 text-center">
                        <div className="mx-auto h-12 w-12 text-muted-foreground">
                            <svg
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                className="h-full w-full"
                            >
                                <path
                                    strokeLinecap="round"
                                    strokeLinejoin="round"
                                    strokeWidth={1}
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                                />
                            </svg>
                        </div>
                        <h3 className="mt-4 text-lg font-medium text-muted-foreground">
                            Nenhum registro encontrado
                        </h3>
                        <p className="mt-2 text-sm text-muted-foreground">
                            Tente ajustar os filtros ou adicionar novos registros.
                        </p>
                    </div>
                )}

                {/* Loading state */}
                {loading && (
                    <div className="flex items-center justify-center py-12">
                        <div className="flex items-center space-x-2 text-muted-foreground">
                            <div className="h-4 w-4 animate-spin rounded-full border-2 border-current border-t-transparent"></div>
                            <span>Carregando...</span>
                        </div>
                    </div>
                )}
            </div>

            {/* Paginação */}
            {pagination && (
                <TablePagination
                    pagination={pagination}
                    onPageChange={onPageChange}
                    loading={loading}
                />
            )}
        </div>
    );
}

// Export do componente principal
export default PapaLeguasTable;

// Re-exports dos tipos e componentes auxiliares
export * from './types';
export { TableHeader } from './components/TableHeader';
export { TableBody } from './components/TableBody';
export { TableFilters } from './components/TableFilters';
export { TableActions } from './components/TableActions';
export { TablePagination } from './components/TablePagination';
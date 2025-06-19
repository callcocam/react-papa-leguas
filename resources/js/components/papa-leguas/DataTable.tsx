import React, { useState } from 'react';
import { router } from '@inertiajs/react';

interface DataTableProps {
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
    onFilterChange?: (filters: Record<string, any>) => void;
    onPageChange?: (page: number) => void;
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
}

// Função para renderizar valor da célula baseado no tipo
function renderCellValue(value: any, column: any) {
    if (value && typeof value === 'object') {
        // Badge
        if (value.type === 'badge') {
            const variantClass = value.variant === 'success' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' : 
                               value.variant === 'warning' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' :
                               value.variant === 'destructive' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 
                               'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
            
            return (
                <span className={`inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${variantClass}`}>
                    {value.label || value.formatted || value.value}
                </span>
            );
        }

        // Email com mailto
        if (value.type === 'email') {
            return (
                <a 
                    href={value.mailto} 
                    className="text-blue-600 dark:text-blue-400 hover:underline"
                    title="Enviar email"
                >
                    {value.formatted || value.value}
                </a>
            );
        }

        // Data formatada
        if (value.formatted && value.since) {
            return (
                <div className="space-y-1">
                    <div className="text-sm">{value.formatted}</div>
                    <div className="text-xs text-gray-500 dark:text-gray-400">
                        {value.since}
                    </div>
                </div>
            );
        }

        // Valor com formatação simples
        if (value.formatted) {
            return <span>{value.formatted}</span>;
        }

        // Fallback para valor bruto
        return <span>{value.value || JSON.stringify(value)}</span>;
    }

    // Valor simples
    return <span>{value}</span>;
}

// Função para renderizar filtro
function renderFilter(filter: any, value: any, onChange: (value: any) => void) {
    const inputClass = "w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-blue-500 focus:border-transparent";
    
    switch (filter.type) {
        case 'text':
            return (
                <input
                    type="text"
                    placeholder={filter.placeholder || `Filtrar por ${filter.label}`}
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    className={inputClass}
                />
            );

        case 'select':
            return (
                <select
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    className={inputClass}
                >
                    <option value="">{filter.placeholder || `Selecione ${filter.label}`}</option>
                    {filter.options && Object.entries(filter.options).map(([key, label]: [string, any]) => (
                        <option key={key} value={key}>
                            {typeof label === 'string' ? label : label.label || key}
                        </option>
                    ))}
                </select>
            );

        case 'boolean':
            return (
                <select
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    className={inputClass}
                >
                    {filter.options && Object.entries(filter.options).map(([key, label]: [string, any]) => (
                        <option key={key} value={key}>
                            {typeof label === 'string' ? label : label.label || key}
                        </option>
                    ))}
                </select>
            );

        case 'date_range':
            return (
                <div className="space-y-2">
                    <input
                        type="date"
                        placeholder="Data inicial"
                        value={value?.start || ''}
                        onChange={(e) => onChange({ ...value, start: e.target.value })}
                        className={inputClass}
                    />
                    <input
                        type="date"
                        placeholder="Data final"
                        value={value?.end || ''}
                        onChange={(e) => onChange({ ...value, end: e.target.value })}
                        className={inputClass}
                    />
                </div>
            );

        default:
            return (
                <input
                    type="text"
                    placeholder={filter.placeholder || `Filtrar por ${filter.label}`}
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    className={inputClass}
                />
            );
    }
}

export default function DataTable({ 
    table, 
    routes, 
    config, 
    onFilterChange, 
    onPageChange, 
    onSort 
}: DataTableProps) {
    const [filters, setFilters] = useState<Record<string, any>>({});
    const [showFilters, setShowFilters] = useState(false);
    const [sortColumn, setSortColumn] = useState<string | null>(null);
    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');

    const handleFilterChange = (key: string, value: any) => {
        const newFilters = {
            ...filters,
            [key]: value
        };
        setFilters(newFilters);
        onFilterChange?.(newFilters);
    };

    const handleSort = (column: string) => {
        const newDirection = sortColumn === column && sortDirection === 'asc' ? 'desc' : 'asc';
        setSortColumn(column);
        setSortDirection(newDirection);
        onSort?.(column, newDirection);
    };

    const clearFilters = () => {
        setFilters({});
        onFilterChange?.({});
    };

    return (
        <div className="space-y-6">
            {/* Header da Tabela */}
            <div className="flex items-center justify-between">
                <div>
                    <h2 className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                        {table?.meta?.title || 'Dados'}
                    </h2>
                    <p className="text-gray-600 dark:text-gray-400 mt-1">
                        {table?.meta?.description || 'Visualize e gerencie seus dados'}
                    </p>
                </div>
                
                <div className="flex items-center gap-3">
                    {config?.can_create && routes?.create && (
                        <button 
                            onClick={() => router.visit(routes.create)}
                            className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:bg-blue-500 dark:hover:bg-blue-600"
                        >
                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                            </svg>
                            Novo {config.model_name}
                        </button>
                    )}
                    
                    {table?.filters && table.filters.length > 0 && (
                        <button
                            onClick={() => setShowFilters(!showFilters)}
                            className="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                            </svg>
                            {showFilters ? 'Ocultar' : 'Mostrar'} Filtros
                        </button>
                    )}
                </div>
            </div>

            {/* Filtros */}
            {showFilters && table?.filters && table.filters.length > 0 && (
                <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <div className="mb-4">
                        <h3 className="text-lg font-medium text-gray-900 dark:text-gray-100">Filtros</h3>
                        <p className="text-sm text-gray-600 dark:text-gray-400">
                            Use os filtros abaixo para refinar os resultados
                        </p>
                    </div>
                    
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        {table.filters.map((filter: any) => (
                            <div key={filter.key} className="space-y-2">
                                <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    {filter.label}
                                </label>
                                {renderFilter(
                                    filter,
                                    filters[filter.key],
                                    (value) => handleFilterChange(filter.key, value)
                                )}
                            </div>
                        ))}
                    </div>
                    
                    <div className="flex items-center gap-3 mt-6">
                        <button
                            onClick={clearFilters}
                            className="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 text-sm font-medium rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700"
                        >
                            <svg className="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Limpar Filtros
                        </button>
                    </div>
                </div>
            )}

            {/* Tabela */}
            <div className="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead className="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                {table?.columns?.map((column: any) => (
                                    <th 
                                        key={column.key}
                                        className={`px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider ${column.hidden ? 'hidden' : ''} ${column.sortable ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800' : ''}`}
                                        style={{ 
                                            textAlign: column.alignment || 'left',
                                            width: column.width 
                                        }}
                                        onClick={() => column.sortable && handleSort(column.key)}
                                    >
                                        <div className="flex items-center gap-2">
                                            {column.label}
                                            {column.sortable && (
                                                <span className="text-gray-400">
                                                    {sortColumn === column.key ? (
                                                        sortDirection === 'asc' ? '↑' : '↓'
                                                    ) : '↕'}
                                                </span>
                                            )}
                                        </div>
                                    </th>
                                ))}
                                <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody className="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {table?.data?.map((row: any, index: number) => (
                                <tr key={row.id || index} className="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                    {table.columns?.map((column: any) => (
                                        <td 
                                            key={column.key}
                                            className={`px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 ${column.hidden ? 'hidden' : ''}`}
                                            style={{ textAlign: column.alignment || 'left' }}
                                        >
                                            {renderCellValue(row[column.key], column)}
                                        </td>
                                    ))}
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div className="flex items-center gap-2">
                                            {config?.can_edit && routes?.edit && (
                                                <button
                                                    onClick={() => router.visit(routes.edit(row.id))}
                                                    className="text-blue-600 dark:text-blue-400 hover:text-blue-900 dark:hover:text-blue-300 transition-colors"
                                                    title="Editar"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                            )}
                                            {config?.can_delete && routes?.destroy && (
                                                <button
                                                    onClick={() => {
                                                        if (confirm('Tem certeza que deseja excluir este item?')) {
                                                            router.delete(routes.destroy(row.id));
                                                        }
                                                    }}
                                                    className="text-red-600 dark:text-red-400 hover:text-red-900 dark:hover:text-red-300 transition-colors"
                                                    title="Excluir"
                                                >
                                                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {(!table?.data || table.data.length === 0) && (
                                <tr>
                                    <td 
                                        colSpan={(table?.columns?.length || 0) + 1}
                                        className="px-6 py-12 text-center text-gray-500 dark:text-gray-400"
                                    >
                                        <div className="flex flex-col items-center">
                                            <svg className="w-12 h-12 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                            </svg>
                                            <p>Nenhum registro encontrado</p>
                                        </div>
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Paginação */}
                {table?.pagination && (
                    <div className="bg-white dark:bg-gray-800 px-4 py-3 flex items-center justify-between border-t border-gray-200 dark:border-gray-700 sm:px-6">
                        <div className="flex-1 flex justify-between sm:hidden">
                            <button
                                disabled={table.pagination.current_page <= 1}
                                onClick={() => onPageChange?.(table.pagination.current_page - 1)}
                                className="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Anterior
                            </button>
                            <button
                                disabled={table.pagination.current_page >= table.pagination.last_page}
                                onClick={() => onPageChange?.(table.pagination.current_page + 1)}
                                className="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                            >
                                Próximo
                            </button>
                        </div>
                        <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p className="text-sm text-gray-700 dark:text-gray-300">
                                    Mostrando <span className="font-medium">{table.pagination.from || 0}</span> a{' '}
                                    <span className="font-medium">{table.pagination.to || 0}</span> de{' '}
                                    <span className="font-medium">{table.pagination.total || 0}</span> registros
                                </p>
                            </div>
                            <div>
                                <nav className="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <button
                                        disabled={table.pagination.current_page <= 1}
                                        onClick={() => onPageChange?.(table.pagination.current_page - 1)}
                                        className="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clipRule="evenodd" />
                                        </svg>
                                    </button>
                                    <span className="relative inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-700 dark:text-gray-300">
                                        Página {table.pagination.current_page} de {table.pagination.last_page}
                                    </span>
                                    <button
                                        disabled={table.pagination.current_page >= table.pagination.last_page}
                                        onClick={() => onPageChange?.(table.pagination.current_page + 1)}
                                        className="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-sm font-medium text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fillRule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clipRule="evenodd" />
                                        </svg>
                                    </button>
                                </nav>
                            </div>
                        </div>
                    </div>
                )}
            </div>
        </div>
    );
} 
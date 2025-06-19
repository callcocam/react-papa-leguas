import React from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../layouts/react-app-layout';
import { type BreadcrumbItem } from '../../types';

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
    error?: string;
}

export default function CrudIndex({ table, routes, config, error }: CrudIndexProps) {
    return (
        <AppLayout 
            breadcrumbs={breadcrumbs}
            title={config?.page_title || 'CRUD'}
        >
            <Head title={`${config?.page_title || 'CRUD'} - Lista`} />
            
            <div className="space-y-6 bg-red-500">
                {/* Header */}
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                            {config?.page_title || 'CRUD'} - Análise JSON
                        </h1>
                        <p className="text-gray-600 dark:text-gray-400 mt-2">
                            {config?.page_description || 'Análise JSON'}
                        </p>
                    </div>
                </div>

                {/* Dados em JSON para análise */}
                <div className="space-y-6">
                    {/* Configuração */}
                    <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            📋 Configuração
                        </h2>
                        <pre className="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-auto text-sm">
                            {JSON.stringify(config, null, 2)}
                        </pre>
                    </div>

                    {/* Rotas */}
                    <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            🛣️ Rotas
                        </h2>
                        <pre className="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-auto text-sm">
                            {JSON.stringify(routes, null, 2)}
                        </pre>
                    </div>

                    {/* Meta da Tabela */}
                    {table?.meta && (
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                ⚙️ Meta da Tabela
                            </h2>
                            <pre className="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-auto text-sm">
                                {JSON.stringify(table?.meta, null, 2)}
                            </pre>
                        </div>
                    )}

                    {/* Colunas */}
                    <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            📊 Colunas ({table?.columns?.length || 0})
                        </h2>
                        <pre className="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-auto text-sm">
                            {JSON.stringify(table?.columns, null, 2)}
                        </pre>
                    </div>

                    {/* Dados */}
                    <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                        <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                            📄 Dados ({table?.data?.length || 0} registros)
                        </h2>
                        <pre className="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-auto text-sm max-h-96">
                            {JSON.stringify(table?.data, null, 2)}
                        </pre>
                    </div>

                    {/* Filtros */}
                    {table?.filters && table.filters.length > 0 && (
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                🔍 Filtros ({table.filters.length})
                            </h2>
                            <pre className="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-auto text-sm">
                                {JSON.stringify(table?.filters, null, 2)}
                            </pre>
                        </div>
                    )}

                    {/* Actions */}
                    {table?.actions && (
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                ⚡ Actions
                            </h2>
                            <pre className="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-auto text-sm">
                                {JSON.stringify(table?.actions, null, 2)}
                            </pre>
                        </div>
                    )}

                    {/* Paginação */}
                    {table?.pagination && (
                        <div className="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                            <h2 className="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">
                                📄 Paginação
                            </h2>
                            <pre className="bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-auto text-sm">
                                {JSON.stringify(table?.pagination, null, 2)}
                            </pre>
                        </div>
                    )}

                    {/* Erro */}
                    {error && (
                        <div className="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-6">
                            <h2 className="text-xl font-semibold text-red-900 dark:text-red-100 mb-4">
                                ❌ Erro
                            </h2>
                            <pre className="bg-red-100 dark:bg-red-900/40 p-4 rounded-lg overflow-auto text-sm text-red-800 dark:text-red-200">
                                {error}
                            </pre>
                        </div>
                    )}

                    {/* Informações de Debug */}
                    <div className="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-6">
                        <h2 className="text-xl font-semibold text-blue-900 dark:text-blue-100 mb-4">
                            🔧 Debug Info
                        </h2>
                        <div className="space-y-2 text-sm">
                            <p><strong>URL Atual:</strong> {window.location.href}</p>
                            <p><strong>Modelo:</strong> {config?.model_name || 'N/A'}</p>
                            <p><strong>Prefixo de Rota:</strong> {config?.route_prefix || 'N/A'}</p>
                            <p><strong>Total de Dados:</strong> {table?.data?.length || 0}</p>
                            <p><strong>Total de Colunas:</strong> {table?.columns?.length || 0}</p>
                            <p><strong>Tem Filtros:</strong> {table?.filters?.length ? 'Sim' : 'Não'}</p>
                            <p><strong>Tem Actions:</strong> {table?.actions ? 'Sim' : 'Não'}</p>
                            <p><strong>Tem Paginação:</strong> {table?.pagination ? 'Sim' : 'Não'}</p>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

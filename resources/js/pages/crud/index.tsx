import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../layouts/react-app-layout';
import { type BreadcrumbItem } from '../../types';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

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

// Função para renderizar valor da célula baseado no tipo
function renderCellValue(value: any, column: any) {
    // Se o valor é um objeto (dados formatados do backend)
    if (value && typeof value === 'object') {
        // Badge
        if (value.type === 'badge') {
            const variant = value.variant === 'success' ? 'default' : 
                          value.variant === 'warning' ? 'secondary' :
                          value.variant === 'destructive' ? 'destructive' : 'outline';
            
            return (
                <Badge variant={variant}>
                    {value.label || value.formatted || value.value}
                </Badge>
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
    switch (filter.type) {
        case 'text':
            return (
                <Input
                    placeholder={filter.placeholder || `Filtrar por ${filter.label}`}
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    className="w-full"
                />
            );

        case 'select':
            return (
                <select
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    className="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
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
                    className="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
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
                    <Input
                        type="date"
                        placeholder="Data inicial"
                        value={value?.start || ''}
                        onChange={(e) => onChange({ ...value, start: e.target.value })}
                    />
                    <Input
                        type="date"
                        placeholder="Data final"
                        value={value?.end || ''}
                        onChange={(e) => onChange({ ...value, end: e.target.value })}
                    />
                </div>
            );

        default:
            return (
                <Input
                    placeholder={filter.placeholder || `Filtrar por ${filter.label}`}
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                />
            );
    }
}

export default function CrudIndex({ table, routes, config, capabilities, error }: CrudIndexProps) {
    const [filters, setFilters] = useState<Record<string, any>>({});
    const [showFilters, setShowFilters] = useState(false);

    const handleFilterChange = (key: string, value: any) => {
        setFilters(prev => ({
            ...prev,
            [key]: value
        }));
    };

    const applyFilters = () => {
        // Aqui você implementaria a lógica para aplicar os filtros
        // Por exemplo, fazer uma nova requisição para o backend
        console.log('Aplicando filtros:', filters);
    };

    const clearFilters = () => {
        setFilters({});
    };

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
                    
                    <div className="flex items-center gap-3">
                        {config?.can_create && (
                            <Button onClick={() => router.visit(routes?.create || '#')}>
                                Novo {config.model_name}
                            </Button>
                        )}
                        
                        {table?.filters && table.filters.length > 0 && (
                            <Button
                                variant="outline"
                                onClick={() => setShowFilters(!showFilters)}
                            >
                                {showFilters ? 'Ocultar' : 'Mostrar'} Filtros
                            </Button>
                        )}
                    </div>
                </div>

                {/* Filtros */}
                {showFilters && table?.filters && table.filters.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>Filtros</CardTitle>
                            <CardDescription>
                                Use os filtros abaixo para refinar os resultados
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
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
                                <Button onClick={applyFilters}>
                                    Aplicar Filtros
                                </Button>
                                <Button variant="outline" onClick={clearFilters}>
                                    Limpar Filtros
                                </Button>
                            </div>
                        </CardContent>
                    </Card>
                )}

                {/* Tabela */}
                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    {table?.columns?.map((column: any) => (
                                        <TableHead 
                                            key={column.key}
                                            className={column.width ? `w-[${column.width}]` : ''}
                                            style={{ 
                                                textAlign: column.alignment || 'left',
                                                width: column.width 
                                            }}
                                        >
                                            <div className="flex items-center gap-2">
                                                {column.label}
                                                {column.sortable && (
                                                    <span className="text-gray-400 text-xs">↕</span>
                                                )}
                                            </div>
                                        </TableHead>
                                    ))}
                                    <TableHead>Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {table?.data?.map((row: any, index: number) => (
                                    <TableRow key={row.id || index}>
                                        {table.columns?.map((column: any) => (
                                            <TableCell 
                                                key={column.key}
                                                style={{ textAlign: column.alignment || 'left' }}
                                                className={column.hidden ? 'hidden' : ''}
                                            >
                                                {renderCellValue(row[column.key], column)}
                                            </TableCell>
                                        ))}
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                {config?.can_edit && (
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() => router.visit(routes?.edit?.(row.id) || '#')}
                                                    >
                                                        Editar
                                                    </Button>
                                                )}
                                                {config?.can_delete && (
                                                    <Button
                                                        variant="destructive"
                                                        size="sm"
                                                        onClick={() => {
                                                            if (confirm('Tem certeza que deseja excluir?')) {
                                                                router.delete(routes?.destroy?.(row.id) || '#');
                                                            }
                                                        }}
                                                    >
                                                        Excluir
                                                    </Button>
                                                )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>

                        {/* Paginação */}
                        {table?.pagination && (
                            <div className="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                                <div className="text-sm text-gray-600 dark:text-gray-400">
                                    Mostrando {table.pagination.from || 0} a {table.pagination.to || 0} de {table.pagination.total || 0} registros
                                </div>
                                
                                <div className="flex items-center gap-2">
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={table.pagination.current_page <= 1}
                                        onClick={() => {
                                            // Implementar navegação de página
                                            console.log('Página anterior');
                                        }}
                                    >
                                        Anterior
                                    </Button>
                                    
                                    <span className="text-sm text-gray-600 dark:text-gray-400">
                                        Página {table.pagination.current_page} de {table.pagination.last_page}
                                    </span>
                                    
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        disabled={table.pagination.current_page >= table.pagination.last_page}
                                        onClick={() => {
                                            // Implementar navegação de página
                                            console.log('Próxima página');
                                        }}
                                    >
                                        Próximo
                                    </Button>
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                {/* Estatísticas */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {table?.data?.length || 0}
                            </div>
                            <div className="text-sm text-gray-600 dark:text-gray-400">
                                Registros Exibidos
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {table?.pagination?.total || table?.data?.length || 0}
                            </div>
                            <div className="text-sm text-gray-600 dark:text-gray-400">
                                Total de Registros
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {table?.columns?.length || 0}
                            </div>
                            <div className="text-sm text-gray-600 dark:text-gray-400">
                                Colunas
                            </div>
                        </CardContent>
                    </Card>
                    
                    <Card>
                        <CardContent className="p-4">
                            <div className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {table?.filters?.length || 0}
                            </div>
                            <div className="text-sm text-gray-600 dark:text-gray-400">
                                Filtros Disponíveis
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Erro */}
                {error && (
                    <Card className="border-red-200 dark:border-red-800">
                        <CardContent className="p-6">
                            <div className="text-red-900 dark:text-red-100">
                                <h3 className="font-semibold">❌ Erro</h3>
                                <p className="mt-2 text-sm">{error}</p>
                            </div>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
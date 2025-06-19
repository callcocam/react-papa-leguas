import React, { useState } from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '../../layouts/react-app-layout';
import { type BreadcrumbItem } from '../../types';

// Utilitário para gerar keys únicos
const generateUniqueKey = (...parts: (string | number | undefined)[]): string => {
    return parts.filter(Boolean).join('-');
};
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
function renderFilter(filter: any, value: any, onChange: (value: any) => void, filterIndex: number = 0, onApplyFilters?: () => void) {
    // Verificação de segurança
    if (!filter || typeof filter !== 'object') {
        console.warn('⚠️ Filtro inválido:', filter);
        return null;
    }

    switch (filter.type) {
        case 'text':
            return (
                <Input
                    key={`filter-${filterIndex}-text-input`}
                    placeholder={filter.placeholder || `Filtrar por ${filter.label || 'campo'}`}
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' && onApplyFilters) {
                            e.preventDefault();
                            onApplyFilters();
                        }
                    }}
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
                    <option key={`filter-${filterIndex}-select-placeholder`} value="">{filter.placeholder || `Selecione ${filter.label || 'opção'}`}</option>
                    {filter.options && typeof filter.options === 'object' && Object.entries(filter.options).map(([key, label]: [string, any], optionIndex: number) => (
                        <option key={`filter-${filterIndex}-select-option-${key}-${optionIndex}`} value={key}>
                            {typeof label === 'string' ? label : (label && label.label) || key}
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
                    {filter.options && typeof filter.options === 'object' && Object.entries(filter.options).map(([key, label]: [string, any], optionIndex: number) => (
                        <option key={`filter-${filterIndex}-boolean-option-${key}-${optionIndex}`} value={key}>
                            {typeof label === 'string' ? label : (label && label.label) || key}
                        </option>
                    ))}
                </select>
            );

        case 'date_range':
            return (
                <div className="space-y-2">
                    <Input
                        key={`filter-${filterIndex}-date-start-input`}
                        type="date"
                        placeholder="Data inicial"
                        value={value?.start || ''}
                        onChange={(e) => onChange({ ...value, start: e.target.value })}
                    />
                    <Input
                        key={`filter-${filterIndex}-date-end-input`}
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
                    key={`filter-${filterIndex}-default-input`}
                    placeholder={filter.placeholder || `Filtrar por ${filter.label || 'campo'}`}
                    value={value || ''}
                    onChange={(e) => onChange(e.target.value)}
                    onKeyDown={(e) => {
                        if (e.key === 'Enter' && onApplyFilters) {
                            e.preventDefault();
                            onApplyFilters();
                        }
                    }}
                />
            );
    }
}

export default function CrudIndex({ table, routes, config, capabilities, error }: CrudIndexProps) {
    const [filters, setFilters] = useState<Record<string, any>>({});
    const [showFilters, setShowFilters] = useState(false);
    const [isApplyingFilters, setIsApplyingFilters] = useState(false);

    // Inicializar filtros com valores da URL (se existirem)
    React.useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const urlFilters: Record<string, any> = {};
        
        urlParams.forEach((value, key) => {
            if (key.startsWith('filter_')) {
                const filterKey = key.replace('filter_', '');
                // Parse valores especiais
                if (value === 'true') urlFilters[filterKey] = true;
                else if (value === 'false') urlFilters[filterKey] = false;
                else if (key.includes('date_range')) {
                    // Parse date range
                    try {
                        urlFilters[filterKey] = JSON.parse(value);
                    } catch {
                        urlFilters[filterKey] = value;
                    }
                } else {
                    urlFilters[filterKey] = value;
                }
            }
        });
        
        if (Object.keys(urlFilters).length > 0) {
            setFilters(urlFilters);
            setShowFilters(true); // Mostrar filtros se existirem na URL
        }
    }, []);

    // Debug para verificar duplicatas
    React.useEffect(() => {
        if (table?.data) {
            const rowIds = table.data.map((row: any, index: number) => row.id || index);
            const uniqueIds = new Set(rowIds);
            if (rowIds.length !== uniqueIds.size) {
                console.warn('⚠️ Keys duplicados detectados nos dados:', rowIds);
            }
        }
    }, [table?.data]);

    const handleFilterChange = (key: string, value: any) => {
        setFilters(prev => ({
            ...prev,
            [key]: value
        }));
    };

    const buildFilterParams = () => {
        const params: Record<string, any> = {};
        
        Object.entries(filters).forEach(([key, value]) => {
            // Só inclui filtros que têm valor
            if (value !== null && value !== undefined && value !== '') {
                // Para date_range, serializa como JSON se for objeto
                if (typeof value === 'object' && value !== null) {
                    params[`filter_${key}`] = JSON.stringify(value);
                } else {
                    params[`filter_${key}`] = value;
                }
            }
        });
        
        return params;
    };

    const applyFilters = async () => {
        if (isApplyingFilters) return;
        
        setIsApplyingFilters(true);
        
        try {
            const filterParams = buildFilterParams();
            const currentUrl = window.location.pathname;
            
            // Fazer requisição Inertia com os filtros
            router.get(currentUrl, filterParams, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    console.log('✅ Filtros aplicados com sucesso');
                },
                onError: (errors) => {
                    console.error('❌ Erro ao aplicar filtros:', errors);
                },
                onFinish: () => {
                    setIsApplyingFilters(false);
                }
            });
        } catch (error) {
            console.error('❌ Erro inesperado ao aplicar filtros:', error);
            setIsApplyingFilters(false);
        }
    };

    const clearFilters = async () => {
        if (isApplyingFilters) return;
        
        setIsApplyingFilters(true);
        setFilters({});
        
        try {
            const currentUrl = window.location.pathname;
            
            // Fazer requisição sem parâmetros de filtro
            router.get(currentUrl, {}, {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    console.log('✅ Filtros limpos com sucesso');
                },
                onError: (errors) => {
                    console.error('❌ Erro ao limpar filtros:', errors);
                },
                onFinish: () => {
                    setIsApplyingFilters(false);
                }
            });
        } catch (error) {
            console.error('❌ Erro inesperado ao limpar filtros:', error);
            setIsApplyingFilters(false);
        }
    };

    // Função debounce personalizada
    const debounce = (func: Function, wait: number) => {
        let timeout: NodeJS.Timeout;
        return (...args: any[]) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(null, args), wait);
        };
    };

    // Aplicar filtros automaticamente quando há mudanças (opcional)
    const handleAutoFilter = React.useCallback(
        debounce((newFilters: Record<string, any>) => {
            // Auto-aplicar filtros após 500ms de inatividade
            // Descomente a linha abaixo para ativar filtros automáticos
            // applyFilters();
        }, 500),
        []
    );

    // Verificar se há filtros ativos
    const hasActiveFilters = Object.values(filters).some(value => 
        value !== null && value !== undefined && value !== ''
    );

    // Contar quantos filtros estão ativos
    const activeFiltersCount = Object.values(filters).filter(value => 
        value !== null && value !== undefined && value !== ''
    ).length;

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
                            <div className="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    onClick={() => setShowFilters(!showFilters)}
                                    className="relative"
                                >
                                    {showFilters ? 'Ocultar' : 'Mostrar'} Filtros
                                    {activeFiltersCount > 0 && (
                                        <span className="absolute -top-2 -right-2 bg-blue-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                            {activeFiltersCount}
                                        </span>
                                    )}
                                </Button>
                                {hasActiveFilters && (
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        onClick={clearFilters}
                                        disabled={isApplyingFilters}
                                        className="text-red-600 hover:text-red-700"
                                    >
                                        {isApplyingFilters ? 'Limpando...' : 'Limpar Tudo'}
                                    </Button>
                                )}
                            </div>
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
                                {table.filters.filter(filter => filter && typeof filter === 'object').map((filter: any, filterIndex: number) => (
                                    <div key={generateUniqueKey('filter', filter.key, filterIndex)} className="space-y-2">
                                        <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                            {filter.label || 'Filtro'}
                                        </label>
                                        {renderFilter(
                                            filter,
                                            filters[filter.key],
                                            (value) => handleFilterChange(filter.key, value),
                                            filterIndex,
                                            applyFilters
                                        )}
                                    </div>
                                ))}
                            </div>
                            
                            <div className="flex items-center justify-between mt-6">
                                <div className="flex items-center gap-3">
                                    <Button 
                                        onClick={applyFilters}
                                        disabled={isApplyingFilters}
                                        className="min-w-[120px]"
                                    >
                                        {isApplyingFilters ? (
                                            <>
                                                <span className="animate-spin mr-2">⚪</span>
                                                Aplicando...
                                            </>
                                        ) : (
                                            'Aplicar Filtros'
                                        )}
                                    </Button>
                                    <Button 
                                        variant="outline" 
                                        onClick={clearFilters}
                                        disabled={isApplyingFilters || !hasActiveFilters}
                                    >
                                        {isApplyingFilters ? 'Limpando...' : 'Limpar Filtros'}
                                    </Button>
                                </div>
                                
                                {hasActiveFilters && (
                                    <div className="text-sm text-gray-600 dark:text-gray-400">
                                        {activeFiltersCount} filtro{activeFiltersCount !== 1 ? 's' : ''} ativo{activeFiltersCount !== 1 ? 's' : ''}
                                    </div>
                                )}
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
                                    {table?.columns?.map((column: any, columnIndex: number) => (
                                        <TableHead 
                                            key={generateUniqueKey('header', column.key, columnIndex)}
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
                                    <TableHead key="actions-header">Ações</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {table?.data?.map((row: any, rowIndex: number) => (
                                    <TableRow key={generateUniqueKey('row', row.id, rowIndex)}>
                                        {table.columns?.map((column: any, columnIndex: number) => (
                                            <TableCell 
                                                key={generateUniqueKey('cell', row.id, rowIndex, column.key, columnIndex)}
                                                style={{ textAlign: column.alignment || 'left' }}
                                                className={column.hidden ? 'hidden' : ''}
                                            >
                                                {renderCellValue(row[column.key], column)}
                                            </TableCell>
                                        ))}
                                        <TableCell key={generateUniqueKey('actions', row.id, rowIndex)}>
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
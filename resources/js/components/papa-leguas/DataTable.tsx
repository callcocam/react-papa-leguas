import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';
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
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import FilterRenderer from './filters/FilterRenderer';
import ColumnRenderer from './columns/ColumnRenderer';
import ActionRenderer from './actions/ActionRenderer';
import { type PapaLeguasTableProps } from './types';

// Utilitário para gerar keys únicos
const generateUniqueKey = (...parts: (string | number | undefined)[]): string => {
    return parts.filter(Boolean).join('-');
};

// Interfaces do DataTable
interface DataTableColumn {
    key: string;
    label: string;
    renderAs?: string;
    sortable?: boolean;
    width?: string;
    alignment?: 'left' | 'center' | 'right';
    hidden?: boolean;
}

interface DataTableFilter {
    key: string;
    label: string;
    type: string;
    placeholder?: string;
    options?: Record<string, any>;
}

interface DataTableProps {
    // Dados principais
    data: any[];
    columns: DataTableColumn[];
    
    // Filtros
    filters?: DataTableFilter[];
    appliedFilters?: Record<string, any>;
    
    // Meta informações
    meta?: {
        title?: string;
        description?: string;
    };
    
    // Estados
    loading?: boolean;
    error?: string;
}

export default function DataTable({
    data = [],
    columns = [],
    filters = [],
    actions = [],
    loading = false,
    error,
    meta
}: PapaLeguasTableProps) {
    const [filterValues, setFilterValues] = useState<Record<string, any>>({});
    const [showFilters, setShowFilters] = useState(false);
    const [isApplyingFilters, setIsApplyingFilters] = useState(false);

    // Inicializar filtros com valores da URL (se existirem)
    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const urlFilters: Record<string, any> = {};
        
        urlParams.forEach((value, key) => {
            if (key.startsWith('filter_')) {
                const filterKey = key.replace('filter_', '');
                // Parse valores especiais
                if (value === 'true') urlFilters[filterKey] = true;
                else if (value === 'false') urlFilters[filterKey] = false;
                else if (key.includes('date_range')) {
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
            setFilterValues(urlFilters);
            setShowFilters(true);
        }
    }, []);

    const handleFilterChange = (key: string, value: any) => {
        setFilterValues(prev => ({
            ...prev,
            [key]: value
        }));
    };

    const buildFilterParams = () => {
        const params: Record<string, any> = {};
        
        Object.entries(filterValues).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
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
        setFilterValues({});
        
        try {
            const currentUrl = window.location.pathname;
            
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

    // Verificar se há filtros ativos
    const hasActiveFilters = Object.values(filterValues).some(value => 
        value !== null && value !== undefined && value !== ''
    );

    // Contar quantos filtros estão ativos
    const activeFiltersCount = Object.values(filterValues).filter(value => 
        value !== null && value !== undefined && value !== ''
    ).length;

    if (loading) {
        return (
            <Card>
                <CardContent className="p-6">
                    <div className="flex items-center justify-center">
                        <span className="animate-spin mr-2">⚪</span>
                        Carregando dados...
                    </div>
                </CardContent>
            </Card>
        );
    }

    if (error) {
        return (
            <Card className="border-red-200 dark:border-red-800">
                <CardContent className="p-6">
                    <div className="text-red-900 dark:text-red-100">
                        <h3 className="font-semibold">❌ Erro</h3>
                        <p className="mt-2 text-sm">{error}</p>
                    </div>
                </CardContent>
            </Card>
        );
    }

    return (
        <div className="space-y-6">
            {/* Header com Meta Informações */}
            {meta && (
                <div className="flex items-center justify-between">
                    <div>
                        {meta.title && (
                            <h1 className="text-3xl font-bold text-gray-900 dark:text-gray-100">
                                {meta.title}
                            </h1>
                        )}
                        {meta.description && (
                            <p className="text-gray-600 dark:text-gray-400 mt-2">
                                {meta.description}
                            </p>
                        )}
                    </div>
                </div>
            )}

            {/* Filtros */}
            {filters.length > 0 && (
                <div className="flex items-center gap-3 mb-4">
                    <Button
                        variant="outline"
                        onClick={() => setShowFilters(!showFilters)}
                        className="relative"
                    >
                        {showFilters ? 'Ocultar' : 'Mostrar'} Filtros
                        {activeFiltersCount > 0 && (
                            <Badge className="absolute -top-2 -right-2 h-5 w-5 flex items-center justify-center text-xs">
                                {activeFiltersCount}
                            </Badge>
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

            {showFilters && filters.length > 0 && (
                <Card>
                    <CardHeader>
                        <CardTitle>Filtros</CardTitle>
                        <CardDescription>
                            Use os filtros abaixo para refinar os resultados
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {filters.map((filter, filterIndex) => (
                                <div key={generateUniqueKey('filter', filter.key, filterIndex)} className="space-y-2">
                                    <label className="text-sm font-medium text-gray-700 dark:text-gray-300">
                                        {filter.label || filter.key}
                                    </label>
                                    <FilterRenderer
                                        filter={{ ...filter, onApply: applyFilters }}
                                        value={filterValues[filter.key]}
                                        onChange={(value) => handleFilterChange(filter.key, value)}
                                    />
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

            {/* Tabela Principal */}
            <Card>
                <CardContent className="p-0">
                    <Table>
                        <TableHeader>
                            <TableRow>
                                {columns.map((column, columnIndex) => (
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
                                {actions.length > 0 && (
                                    <TableHead key="actions-header">Ações</TableHead>
                                )}
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {data.map((row, rowIndex) => (
                                <TableRow key={generateUniqueKey('row', row.id, rowIndex)}>
                                    {columns.map((column, columnIndex) => (
                                        <TableCell 
                                            key={generateUniqueKey('cell', row.id, rowIndex, column.key, columnIndex)}
                                            style={{ textAlign: column.alignment || 'left' }}
                                            className={column.hidden ? 'hidden' : ''}
                                        >
                                            <ColumnRenderer
                                                column={column}
                                                value={row[column.key || column.name || '']}
                                                item={row}
                                            />
                                        </TableCell>
                                    ))}
                                    {actions.length > 0 && (
                                        <TableCell key={generateUniqueKey('actions', row.id, rowIndex)}>
                                            <div className="flex items-center gap-2">
                                                {actions.map((action, actionIndex) => (
                                                    <ActionRenderer
                                                        key={generateUniqueKey('action', action.key, rowIndex, actionIndex)}
                                                        action={action}
                                                        item={row}
                                                    />
                                                ))}
                                            </div>
                                        </TableCell>
                                    )}
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    );
} 
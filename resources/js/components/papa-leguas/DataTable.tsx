import React, { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';   
import { Card, CardContent } from '@/components/ui/card';
import Filters from './components/Filters';
import Table from './components/Table';
import Resume from './components/Resume';
import { type PapaLeguasTableProps } from './types';
import { TableProvider } from './contexts/TableContext';

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
    const [sortColumn, setSortColumn] = useState<string>('');
    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');

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

        // Verificar ordenação na URL
        const sortParam = urlParams.get('sort');
        const directionParam = urlParams.get('direction');
        if (sortParam) {
            setSortColumn(sortParam);
            setSortDirection((directionParam as 'asc' | 'desc') || 'asc');
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

    const handleSort = (column: string, direction: 'asc' | 'desc') => {
        setSortColumn(column);
        setSortDirection(direction);
        
        // Aplicar ordenação via URL
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('sort', column);
        currentUrl.searchParams.set('direction', direction);
        
        router.visit(currentUrl.toString(), {
            preserveState: true,
            preserveScroll: true
        });
    };

    const handlePageChange = (page: number) => {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('page', page.toString());
        
        router.visit(currentUrl.toString(), {
            preserveState: true,
            preserveScroll: true
        });
    };

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
        <TableProvider initialData={data} meta={meta}>
            <div className="space-y-6">
                {/* Filtros */}
                <Filters
                    filters={filters}
                    filterValues={filterValues}
                    showFilters={showFilters}
                    isApplyingFilters={isApplyingFilters}
                    onFilterChange={handleFilterChange}
                    onToggleFilters={() => setShowFilters(!showFilters)}
                    onApplyFilters={applyFilters}
                    onClearFilters={clearFilters}
                />

                {/* Tabela Principal */}
                <Table
                    columns={columns}
                    actions={actions}
                    loading={loading}
                    pagination={(meta as any)?.pagination}
                    onSort={handleSort}
                    onPageChange={handlePageChange}
                    sortColumn={sortColumn}
                    sortDirection={sortDirection}
                />

                {/* Resumo/Estatísticas */}
                <Resume
                    data={data}
                    columns={columns}
                    filters={filters}
                    pagination={(meta as any)?.pagination}
                    activeFiltersCount={activeFiltersCount}
                />
            </div>
        </TableProvider>
    );
} 
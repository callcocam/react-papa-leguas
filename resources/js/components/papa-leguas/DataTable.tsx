import React, { useState, useEffect, useCallback } from 'react';
import { router } from '@inertiajs/react';   
import { Card, CardContent } from '@/components/ui/card';
import SearchField from './SearchField';
import Filters from './table/components/Filters';
import Table from './table/components/Table';
import PaginationWrapper from './PaginationWrapper';
import Resume from './table/components/Resume';
import { type PapaLeguasTableProps } from './types';
import { TableProvider } from './table/contexts/TableContext';
import { ConfirmationDialogProvider } from './table/contexts/ConfirmationDialogContext';
import { ModalProvider } from './table/contexts/ModalContext';

export default function DataTable({ 
    data = [], 
    columns = [], 
    filters = [],
    actions = { row: [], bulk: [] },
    loading = false,
    error,
    meta 
}: PapaLeguasTableProps) {
    const [filterValues, setFilterValues] = useState<Record<string, any>>({});
    const [showFilters, setShowFilters] = useState(false);
    const [isApplyingFilters, setIsApplyingFilters] = useState(false);
    const [sortColumn, setSortColumn] = useState<string>('');
    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');
    const [searchTerm, setSearchTerm] = useState<string>('');
    const [isSearching, setIsSearching] = useState(false);

    // Inicializar filtros com valores da URL (se existirem)
    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        const urlFilters: Record<string, any> = {};
        
        urlParams.forEach((value, key) => {
            // Suportar tanto filters[key] quanto filter_key (retrocompatibilidade)
            let filterKey = '';
            if (key.match(/^filters\[(.+)\]$/)) {
                filterKey = key.match(/^filters\[(.+)\]$/)![1];
            } else if (key.startsWith('filter_')) {
                filterKey = key.replace('filter_', '');
            }
            
            if (filterKey) {
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

        // Verificar ordenação na URL (suportar ambos os formatos)
        const sortParam = urlParams.get('sort_column') || urlParams.get('sort');
        const directionParam = urlParams.get('sort_direction') || urlParams.get('direction');
        if (sortParam) {
            setSortColumn(sortParam);
            setSortDirection((directionParam as 'asc' | 'desc') || 'asc');
        }

        // Verificar busca na URL
        const searchParam = urlParams.get('search');
        if (searchParam) {
            setSearchTerm(searchParam);
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
                    params[`filters[${key}]`] = JSON.stringify(value);
                } else {
                    params[`filters[${key}]`] = value;
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
        
        // Aplicar ordenação via URL usando padrão sort_column e sort_direction
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('sort_column', column);
        currentUrl.searchParams.set('sort_direction', direction);
        
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

    // Handler para aplicar busca
    const applySearch = useCallback(async (term: string) => {
        if (isSearching) return;
        
        setIsSearching(true);
        
        try {
            const currentUrl = new URL(window.location.href);
            
            if (term.trim()) {
                currentUrl.searchParams.set('search', term.trim());
            } else {
                currentUrl.searchParams.delete('search');
            }
            
            // Resetar para primeira página ao fazer busca
            currentUrl.searchParams.delete('page');
            
            router.visit(currentUrl.toString(), {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    console.log('✅ Busca aplicada com sucesso');
                },
                onError: (errors) => {
                    console.error('❌ Erro ao aplicar busca:', errors);
                },
                onFinish: () => {
                    setIsSearching(false);
                }
            });
        } catch (error) {
            console.error('❌ Erro inesperado ao aplicar busca:', error);
            setIsSearching(false);
        }
    }, [isSearching]);

    // Handler para limpar busca
    const clearSearch = useCallback(() => {
        setSearchTerm('');
        applySearch('');
    }, [applySearch]);

    // Handler para mudança no campo de busca
    const handleSearchChange = (value: string) => {
        setSearchTerm(value);
    };

    // Handler para pressionar Enter no campo de busca
    const handleSearchKeyPress = (e: React.KeyboardEvent) => {
        if (e.key === 'Enter') {
            applySearch(searchTerm);
        }
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
            <ConfirmationDialogProvider>
                <ModalProvider>
                    <div className="space-y-6">
                        {/* Campo de Busca */}
                        <SearchField
                            searchTerm={searchTerm}
                            isSearching={isSearching}
                            onSearchChange={handleSearchChange}
                            onSearch={applySearch}
                            onClear={clearSearch}
                            onKeyPress={handleSearchKeyPress}
                        />

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
                            onSort={handleSort}
                            sortColumn={sortColumn}
                            sortDirection={sortDirection}
                        />

                        {/* Paginação */}
                        <PaginationWrapper
                            pagination={(meta as any)?.pagination}
                            onPageChange={handlePageChange}
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
                </ModalProvider>
            </ConfirmationDialogProvider>
        </TableProvider>
    );
} 
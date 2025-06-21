import React from 'react';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import FilterRenderer from '../filters/FilterRenderer';
import { type TableFilter } from '../../types';

// Utilitário para gerar keys únicos
const generateUniqueKey = (...parts: (string | number | undefined)[]): string => {
    return parts.filter(Boolean).join('-');
};

interface FiltersProps {
    filters: TableFilter[];
    filterValues: Record<string, any>;
    showFilters: boolean;
    isApplyingFilters: boolean;
    onFilterChange: (key: string, value: any) => void;
    onToggleFilters: () => void;
    onApplyFilters: () => void;
    onClearFilters: () => void;
}

export default function Filters({
    filters,
    filterValues,
    showFilters,
    isApplyingFilters,
    onFilterChange,
    onToggleFilters,
    onApplyFilters,
    onClearFilters
}: FiltersProps) {
    // Verificar se há filtros ativos
    const hasActiveFilters = Object.values(filterValues).some(value => 
        value !== null && value !== undefined && value !== ''
    );

    // Contar quantos filtros estão ativos
    const activeFiltersCount = Object.values(filterValues).filter(value => 
        value !== null && value !== undefined && value !== ''
    ).length;

    if (filters.length === 0) {
        return null;
    }

    return (
        <div className="space-y-4">
            {/* Botões de controle dos filtros */}
            <div className="flex items-center gap-3">
                <Button
                    variant="outline"
                    onClick={onToggleFilters}
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
                        onClick={onClearFilters}
                        disabled={isApplyingFilters}
                        className="text-red-600 hover:text-red-700"
                    >
                        {isApplyingFilters ? 'Limpando...' : 'Limpar Tudo'}
                    </Button>
                )}
            </div>

            {/* Painel de filtros */}
            {showFilters && (
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
                                        filter={{ ...filter, onApply: onApplyFilters }}
                                        value={filterValues[filter.key]}
                                        onChange={(value) => onFilterChange(filter.key, value)}
                                    />
                                </div>
                            ))}
                        </div>
                        
                        <div className="flex items-center justify-between mt-6">
                            <div className="flex items-center gap-3">
                                <Button 
                                    onClick={onApplyFilters}
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
                                    onClick={onClearFilters}
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
        </div>
    );
} 
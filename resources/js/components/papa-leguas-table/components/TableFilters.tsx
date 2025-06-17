import React, { useState, useEffect } from 'react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Filter, X } from 'lucide-react';
import { TableFiltersProps } from '../types';

export function TableFilters({ filters, onFilterChange, loading }: TableFiltersProps) {
    const [filterValues, setFilterValues] = useState<Record<string, any>>({});
    const [showFilters, setShowFilters] = useState(false);

    // Inicializar valores dos filtros
    useEffect(() => {
        const initialValues: Record<string, any> = {};
        filters.forEach(filter => {
            initialValues[filter.key] = filter.value || '';
        });
        setFilterValues(initialValues);
    }, [filters]);

    const handleFilterChange = (key: string, value: any) => {
        const newValues = { ...filterValues, [key]: value };
        setFilterValues(newValues);
        
        if (onFilterChange) {
            onFilterChange(newValues);
        }
    };

    const clearFilters = () => {
        const clearedValues: Record<string, any> = {};
        filters.forEach(filter => {
            clearedValues[filter.key] = '';
        });
        setFilterValues(clearedValues);
        
        if (onFilterChange) {
            onFilterChange(clearedValues);
        }
    };

    const hasActiveFilters = Object.values(filterValues).some(value => 
        value !== '' && value !== null && value !== undefined
    );

    if (filters.length === 0) {
        return null;
    }

    return (
        <div className="space-y-4">
            {/* Botão para mostrar/ocultar filtros */}
            <div className="flex items-center justify-between">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => setShowFilters(!showFilters)}
                    disabled={loading}
                >
                    <Filter className="mr-2 h-4 w-4" />
                    Filtros
                    {hasActiveFilters && (
                        <span className="ml-2 rounded-full bg-primary px-2 py-0.5 text-xs text-primary-foreground">
                            {Object.values(filterValues).filter(v => v !== '' && v !== null && v !== undefined).length}
                        </span>
                    )}
                </Button>

                {hasActiveFilters && (
                    <Button
                        variant="ghost"
                        size="sm"
                        onClick={clearFilters}
                        disabled={loading}
                    >
                        <X className="mr-2 h-4 w-4" />
                        Limpar filtros
                    </Button>
                )}
            </div>

            {/* Painel de filtros */}
            {showFilters && (
                <div className="rounded-lg border bg-card p-4">
                    <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                        {filters.map((filter) => (
                            <div key={filter.key} className="space-y-2">
                                <Label htmlFor={filter.key}>{filter.label}</Label>
                                <Input
                                    id={filter.key}
                                    placeholder={filter.placeholder}
                                    value={filterValues[filter.key] || ''}
                                    onChange={(e) => handleFilterChange(filter.key, e.target.value)}
                                    disabled={loading}
                                />
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
} 
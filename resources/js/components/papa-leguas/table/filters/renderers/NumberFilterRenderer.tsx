import React from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type FilterRendererProps } from '../../../types';

/**
 * Renderizador de Filtro Numérico
 * Usado para filtros de números simples ou range numérico
 */
export default function NumberFilterRenderer({ filter, value, onChange }: FilterRendererProps) {
    // Se for number_range, renderizar dois inputs
    if (filter.type === 'number_range') {
        const rangeValue = value || { min: '', max: '' };
        
        return (
            <div className="space-y-2">
                <div className="space-y-1">
                    <Label htmlFor={`${filter.key}-min`} className="text-xs text-gray-600 dark:text-gray-400">
                        Valor mínimo
                    </Label>
                    <Input
                        id={`${filter.key}-min`}
                        type="number"
                        value={rangeValue.min || ''}
                        onChange={(e) => onChange({ ...rangeValue, min: e.target.value })}
                        className="w-full"
                        placeholder="Min"
                    />
                </div>
                <div className="space-y-1">
                    <Label htmlFor={`${filter.key}-max`} className="text-xs text-gray-600 dark:text-gray-400">
                        Valor máximo
                    </Label>
                    <Input
                        id={`${filter.key}-max`}
                        type="number"
                        value={rangeValue.max || ''}
                        onChange={(e) => onChange({ ...rangeValue, max: e.target.value })}
                        className="w-full"
                        placeholder="Max"
                    />
                </div>
            </div>
        );
    }

    // Número simples
    return (
        <Input
            type="number"
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            onKeyDown={(e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (filter.onApply) {
                        filter.onApply();
                    }
                }
            }}
            className="w-full"
            placeholder={filter.placeholder || `Filtrar por ${filter.label || 'número'}`}
        />
    );
} 
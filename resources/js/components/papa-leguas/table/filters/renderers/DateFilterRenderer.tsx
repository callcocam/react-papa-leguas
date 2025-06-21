import React from 'react';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type FilterRendererProps } from '../../../types';

/**
 * Renderizador de Filtro de Data
 * Usado para filtros de data simples ou range de datas
 */
export default function DateFilterRenderer({ filter, value, onChange }: FilterRendererProps) {
    // Se for date_range, renderizar dois inputs
    if (filter.type === 'date_range') {
        const rangeValue = value || { start: '', end: '' };
        
        return (
            <div className="space-y-2">
                <div className="space-y-1">
                    <Label htmlFor={`${filter.key}-start`} className="text-xs text-gray-600 dark:text-gray-400">
                        Data inicial
                    </Label>
                    <Input
                        id={`${filter.key}-start`}
                        type="date"
                        value={rangeValue.start || ''}
                        onChange={(e) => onChange({ ...rangeValue, start: e.target.value })}
                        className="w-full"
                    />
                </div>
                <div className="space-y-1">
                    <Label htmlFor={`${filter.key}-end`} className="text-xs text-gray-600 dark:text-gray-400">
                        Data final
                    </Label>
                    <Input
                        id={`${filter.key}-end`}
                        type="date"
                        value={rangeValue.end || ''}
                        onChange={(e) => onChange({ ...rangeValue, end: e.target.value })}
                        className="w-full"
                    />
                </div>
            </div>
        );
    }

    // Data simples
    return (
        <Input
            type="date"
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            className="w-full"
            placeholder={filter.placeholder}
        />
    );
} 
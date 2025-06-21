import React from 'react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type FilterRendererProps } from '../../../types';
import { filterValidOptions, hasValidOptions, getOptionLabel } from '../utils/filterUtils';

/**
 * Renderizador de Filtro Select/Dropdown
 * Usado para filtros com opções predefinidas
 */
export default function SelectFilterRenderer({ filter, value, onChange }: FilterRendererProps) {
    console.log(filter);
    
    if (!filter.options || typeof filter.options !== 'object') {
        console.warn('⚠️ SelectFilterRenderer: opções inválidas ou ausentes', filter);
        return null;
    }

    if (!hasValidOptions(filter.options)) {
        console.warn('⚠️ SelectFilterRenderer: nenhuma opção válida encontrada', filter.options);
        return null;
    }

    const validOptions = filterValidOptions(filter.options);

    return (
        <Select value={value || undefined} onValueChange={onChange}>
            <SelectTrigger className="w-full">
                <SelectValue placeholder={filter.placeholder || `Selecione ${filter.label || 'opção'}`} />
            </SelectTrigger>
            <SelectContent>
                {validOptions.map(([key, label]: [string, any], optionIndex: number) => (
                    <SelectItem key={`select-option-${key}-${optionIndex}`} value={key}>
                        {getOptionLabel(label, key)}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
} 
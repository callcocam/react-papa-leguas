import React from 'react';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { type FilterRendererProps } from '../../types';
import { filterValidOptions, hasValidOptions, getOptionLabel } from '../utils/filterUtils';

/**
 * Renderizador de Filtro Boolean
 * Usado para filtros com valores true/false/todos
 */
export default function BooleanFilterRenderer({ filter, value, onChange }: FilterRendererProps) {
    const defaultOptions = {
        '': 'Todos',
        'true': 'Sim',
        'false': 'Não'
    };
    
    const options = filter.options || defaultOptions;
    
    if (!hasValidOptions(options)) {
        console.warn('⚠️ BooleanFilterRenderer: nenhuma opção válida encontrada', options);
        return null;
    }

    const validOptions = filterValidOptions(options);
    
    const handleValueChange = (val: string) => {
        // Converter para boolean se necessário
        if (val === 'true') onChange(true);
        else if (val === 'false') onChange(false);
        else onChange('');
    };

    // Converter valor atual para string para o Select
    const currentValue = value === true ? 'true' : value === false ? 'false' : '';

    return (
        <Select value={currentValue || undefined} onValueChange={handleValueChange}>
            <SelectTrigger className="w-full">
                <SelectValue placeholder={filter.placeholder || `Selecione ${filter.label || 'opção'}`} />
            </SelectTrigger>
            <SelectContent>
                {validOptions.map(([key, label]: [string, any], optionIndex: number) => (
                    <SelectItem key={`boolean-option-${key}-${optionIndex}`} value={key}>
                        {getOptionLabel(label, key)}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
} 
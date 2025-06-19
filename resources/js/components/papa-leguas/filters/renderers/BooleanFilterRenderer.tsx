import React from 'react';
import { type FilterRendererProps } from '../../types';

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
    
    return (
        <select
            value={value || ''}
            onChange={(e) => {
                const val = e.target.value;
                // Converter para boolean se necessário
                if (val === 'true') onChange(true);
                else if (val === 'false') onChange(false);
                else onChange('');
            }}
            className="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
        >
            {Object.entries(options).map(([key, label]: [string, any], optionIndex: number) => (
                <option key={`boolean-option-${key}-${optionIndex}`} value={key}>
                    {typeof label === 'string' ? label : (label && label.label) || key}
                </option>
            ))}
        </select>
    );
} 
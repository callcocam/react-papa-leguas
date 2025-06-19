import React from 'react';
import { type FilterRendererProps } from '../../types';

/**
 * Renderizador de Filtro Select/Dropdown
 * Usado para filtros com opções predefinidas
 */
export default function SelectFilterRenderer({ filter, value, onChange }: FilterRendererProps) {
    return (
        <select
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            className="w-full px-3 py-2 text-sm border border-gray-200 dark:border-gray-700 rounded-md bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100"
        >
            <option value="">{filter.placeholder || `Selecione ${filter.label || 'opção'}`}</option>
            {filter.options && typeof filter.options === 'object' && Object.entries(filter.options).map(([key, label]: [string, any], optionIndex: number) => (
                <option key={`select-option-${key}-${optionIndex}`} value={key}>
                    {typeof label === 'string' ? label : (label && label.label) || key}
                </option>
            ))}
        </select>
    );
} 
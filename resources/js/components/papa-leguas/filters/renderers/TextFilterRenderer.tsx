import React from 'react';
import { Input } from '@/components/ui/input';
import { type FilterRendererProps } from '../../types';

/**
 * Renderizador de Filtro de Texto
 * Usado para filtros de busca por texto
 */
export default function TextFilterRenderer({ filter, value, onChange }: FilterRendererProps) {
    return (
        <Input
            type="text"
            placeholder={filter.placeholder || `Filtrar por ${filter.label || 'texto'}`}
            value={value || ''}
            onChange={(e) => onChange(e.target.value)}
            onKeyDown={(e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    // Trigger aplicar filtros se callback estiver disponível
                    if (filter.onApply) {
                        filter.onApply();
                    }
                }
            }}
            className="w-full"
        />
    );
} 
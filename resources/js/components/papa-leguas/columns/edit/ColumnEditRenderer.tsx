import React from 'react';
import { type RendererProps } from '../../types';

/**
 * Renderizador de texto simples
 * Usado para exibir valores de texto padrão
 */
export default function ColumnEditRenderer({ value, item, column }: RendererProps) {
    // Se o valor é um objeto formatado do backend
    if (value && typeof value === 'object') {
        return <span>{value.formatted || value.value || ''}</span>;
    }
    
    // Valor simples
    return <span>{value || ''}</span>;
} 
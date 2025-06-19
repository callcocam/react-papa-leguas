import React from 'react';
import { Badge } from '@/components/ui/badge';
import { type RendererProps } from '../../types';

/**
 * Renderizador de Badge/Status
 * Usado para exibir status, estados, categorias, etc.
 */
export default function BadgeRenderer({ value, item, column }: RendererProps) {
    // Se o valor é um objeto formatado do backend
    if (value && typeof value === 'object') {
        const variant = value.variant === 'success' ? 'default' : 
                      value.variant === 'warning' ? 'secondary' :
                      value.variant === 'danger' || value.variant === 'destructive' ? 'destructive' : 
                      value.variant === 'info' ? 'outline' : 'default';
        
        return (
            <Badge variant={variant}>
                {value.label || value.formatted || value.value}
            </Badge>
        );
    }
    
    // Valor simples - criar badge padrão
    return (
        <Badge variant="outline">
            {value || ''}
        </Badge>
    );
} 
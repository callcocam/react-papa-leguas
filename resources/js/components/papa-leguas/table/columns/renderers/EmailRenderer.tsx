import React from 'react';
import { type RendererProps } from '../../../types';

/**
 * Renderizador de Email
 * Usado para exibir emails como links mailto clicáveis
 */
export default function EmailRenderer({ value, item, column }: RendererProps) {
    // Se o valor é um objeto formatado do backend
    if (value && typeof value === 'object') {
        const email = value.value || value.formatted;
        const mailto = value.mailto || `mailto:${email}`;
        
        return (
            <a 
                href={mailto} 
                className="text-blue-600 dark:text-blue-400 hover:underline"
                title="Enviar email"
            >
                {value.formatted || email}
            </a>
        );
    }
    
    // Valor simples - criar link mailto
    const email = value || '';
    if (email && email.includes('@')) {
        return (
            <a 
                href={`mailto:${email}`} 
                className="text-blue-600 dark:text-blue-400 hover:underline"
                title="Enviar email"
            >
                {email}
            </a>
        );
    }
    
    // Se não é um email válido, mostrar como texto
    return <span>{email}</span>;
} 
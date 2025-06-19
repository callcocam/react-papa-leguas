import React from 'react';
import { Link } from '@inertiajs/react';
import { type ActionRendererProps } from '../../types';

/**
 * Renderizador de Ação Link
 * Usado para ações que são links navegáveis
 */
export default function LinkActionRenderer({ action, item }: ActionRendererProps) {
    // URL pode ser string ou função
    const url = typeof action.url === 'function' ? action.url(item) : action.url || '#';
    
    const className = `inline-flex items-center text-sm font-medium text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-200 hover:underline ${action.className || ''}`;

    return (
        <Link
            href={url}
            className={className}
            title={action.tooltip || action.label}
        >
            {action.icon && <span className="mr-1">{action.icon}</span>}
            {action.label}
        </Link>
    );
} 
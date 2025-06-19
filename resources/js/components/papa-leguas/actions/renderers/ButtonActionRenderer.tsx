import React from 'react';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { type ActionRendererProps } from '../../types';

/**
 * Renderizador de Ação Button
 * Usado para ações básicas como Edit, Delete, View
 */
export default function ButtonActionRenderer({ action, item }: ActionRendererProps) {
    const handleClick = () => {
        if (action.onClick) {
            action.onClick(item);
        } else if (action.url) {
            // URL pode ser string ou função
            const url = typeof action.url === 'function' ? action.url(item) : action.url;
            
            if (action.method === 'delete') {
                // Confirmação para delete
                const confirmMessage = action.confirmMessage || 'Tem certeza que deseja excluir este item?';
                if (confirm(confirmMessage)) {
                    router.delete(url, {
                        onSuccess: () => {
                            console.log('✅ Item excluído com sucesso');
                        },
                        onError: (errors) => {
                            console.error('❌ Erro ao excluir item:', errors);
                        }
                    });
                }
            } else if (action.method === 'post') {
                router.post(url);
            } else if (action.method === 'put') {
                router.put(url);
            } else {
                // GET padrão
                router.visit(url);
            }
        }
    };

    // Definir variante baseada no tipo de ação
    let variant: "default" | "destructive" | "outline" | "secondary" | "ghost" | "link" = 'outline';
    
    if (action.variant) {
        variant = action.variant;
    } else if (action.type === 'delete' || action.method === 'delete') {
        variant = 'destructive';
    } else if (action.type === 'primary' || action.type === 'edit') {
        variant = 'default';
    }

    return (
        <Button
            variant={variant}
            size={action.size || 'sm'}
            onClick={handleClick}
            disabled={action.disabled}
            className={action.className}
            title={action.tooltip || action.label}
        >
            {action.icon && <span className="mr-1">{action.icon}</span>}
            {action.label}
        </Button>
    );
} 
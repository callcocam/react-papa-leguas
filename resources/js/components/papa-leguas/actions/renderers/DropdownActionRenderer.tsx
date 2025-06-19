import React from 'react';
import { router } from '@inertiajs/react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';
import { type ActionRendererProps } from '../../types';

/**
 * Renderizador de Ação Dropdown
 * Usado para múltiplas ações agrupadas em um menu dropdown
 */
export default function DropdownActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    // Para dropdown, esperamos que action.actions contenha as sub-ações
    const subActions = (action as any).actions || [];

    const handleSubActionClick = (subAction: any) => {
        if (subAction.onClick) {
            subAction.onClick(item);
        } else if (subAction.url) {
            const url = typeof subAction.url === 'function' ? subAction.url(item) : subAction.url;
            
            if (subAction.method === 'delete') {
                const confirmMessage = subAction.confirmMessage || 'Tem certeza que deseja excluir este item?';
                if (confirm(confirmMessage)) {
                    router.delete(url, {
                        onSuccess: () => console.log('✅ Ação executada com sucesso'),
                        onError: (errors) => console.error('❌ Erro ao executar ação:', errors)
                    });
                }
            } else if (subAction.method === 'post') {
                router.post(url);
            } else if (subAction.method === 'put') {
                router.put(url);
            } else {
                router.visit(url);
            }
        }
    };

    if (subActions.length === 0) {
        return null;
    }

    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button 
                    variant="outline" 
                    size="sm"
                    className={action.className}
                >
                    {IconComponent && <IconComponent className="mr-1" />}
                    <span>{action.label || 'Ações'}</span>
                    <span className="ml-1">▼</span>
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                {subActions.map((subAction: any, index: number) => (
                    <DropdownMenuItem
                        key={`dropdown-action-${subAction.key || index}`}
                        onClick={() => handleSubActionClick(subAction)}
                        disabled={subAction.disabled}
                        className={subAction.type === 'delete' ? 'text-red-600 dark:text-red-400' : ''}
                    >
                        {subAction.icon && <span className="mr-2">{subAction.icon}</span>}
                        {subAction.label}
                    </DropdownMenuItem>
                ))}
            </DropdownMenuContent>
        </DropdownMenu>
    );
} 
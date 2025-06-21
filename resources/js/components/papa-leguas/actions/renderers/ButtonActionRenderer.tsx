import React from 'react';
import { router } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { type ActionRendererProps } from '../../types';
import { useConfirmationDialog } from '../../contexts/ConfirmationDialogContext';

/**
 * Renderizador de Ação Button
 * Usado para ações básicas como Edit, Delete, View que utilizam navegação ou router do Inertia.
 */
export default function ButtonActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    const { confirm } = useConfirmationDialog();

    // Função que executa a ação de fato (navegação/requisição)
    const executeAction = () => {
        if (action.onClick) {
            action.onClick(item);
        } else if (action.url) {
            const url = typeof action.url === 'function' ? action.url(item) : action.url;
            const method = action.method || 'get';

            router.visit(url, {
                method: method,
                onSuccess: () => {
                    console.log(`✅ Ação '${action.key}' executada com sucesso.`);
                },
                onError: (errors) => {
                    console.error(`❌ Erro ao executar a ação '${action.key}':`, errors);
                }
            });
        }
    };

    // Handler do clique, que primeiro verifica se precisa de confirmação
    const handleClick = () => {
        if (action.confirmation) {
            confirm({
                title: action.confirmation.title || 'Confirmar Ação',
                message: action.confirmation.message,
                confirmText: action.confirmation.confirm_text || 'Confirmar',
                cancelText: action.confirmation.cancel_text || 'Cancelar',
                confirmVariant: action.confirmation.confirm_variant || (action.variant === 'destructive' ? 'destructive' : 'default'),
                onConfirm: executeAction,
            });
        } else {
            executeAction();
        }
    };

    return (
        <Button
            variant={action.variant || 'outline'}
            size={action.size || 'sm'}
            onClick={handleClick}
            disabled={action.disabled}
            className={action.className}
            title={action.tooltip || action.label}
        >
            {IconComponent && <IconComponent className="mr-2 h-4 w-4" />}
            {action.showLabel !== false && <span>{action.label}</span>}
        </Button>
    );
} 
import React, { useContext } from 'react';
import { Button } from '@/components/ui/button';
import { Loader2 } from 'lucide-react';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useConfirmationDialog } from '../../contexts/ConfirmationDialogContext';
import { type ActionRendererProps } from '../../types';
import { cn } from '@/lib/utils';
import { useActionProcessor } from '../../hooks/useActionProcessor';
import { TableContext } from '../../contexts/TableContext';
import { router } from '@inertiajs/react';

/**
 * Renderiza ações de callback normais da tabela (não colunas editáveis)
 * 
 * Este renderer é usado para:
 * - CallbackActions definidas no método actions() da tabela
 * - Ações que executam callbacks no backend
 * - Ações com confirmação opcional
 */
export default function TableActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    const { confirm } = useConfirmationDialog();
    const { processAction, isLoading } = useActionProcessor();
    const { meta } = useContext(TableContext);

    if (action.hidden) {
        return null;
    }

    const {
        disabled,
        variant = 'ghost',
        label,
        tooltip,
        showLabel,
        confirmation,
        className,
    } = action;

    const runAction = async () => {
        if (!meta?.key) {
            console.error("Erro crítico: A chave da tabela (meta.key) não foi encontrada no contexto.");
            return;
        }

        if (!item?.id) {
            console.error("Erro: ID do item não encontrado.", item);
            return;
        }

        const result = await processAction({
            table: meta.key,
            actionKey: action.key,
            actionType: action.type || 'callback',
            item: item,
            data: action.data || {},
        });

        if (result?.success) {
            // Se o backend não disser explicitamente para não recarregar
            if (result.reload !== false) {
                // Usa o router do Inertia para recarregar apenas os dados, mantendo a posição do scroll
                router.visit(window.location.href, {
                    preserveScroll: true,
                });
            }
        }
    };

    const handleClick = (e: React.MouseEvent) => {
        e.stopPropagation();

        if (confirmation) {
            confirm({
                title: confirmation.title || 'Você tem certeza?',
                message: confirmation.message,
                confirmText: confirmation.confirm_text,
                cancelText: confirmation.cancel_text,
                confirmVariant: confirmation.confirm_variant,
                onConfirm: runAction,
            });
        } else {
            runAction();
        }
    };

    return (
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger asChild>
                    <Button
                        variant={variant}
                        size={showLabel ? 'sm' : 'icon'}
                        onClick={handleClick}
                        disabled={disabled || isLoading}
                        className={cn(
                            showLabel ? 'h-auto text-xs px-2 py-1.5' : 'h-8 w-8',
                            className
                        )}
                    >
                        {isLoading ? (
                            <Loader2 className={cn('h-4 w-4 animate-spin', showLabel && 'mr-2')} />
                        ) : (
                            IconComponent && <IconComponent className={cn('h-4 w-4', showLabel && 'mr-2')} />
                        )}
                        {showLabel && <span>{isLoading ? 'Processando...' : label}</span>}
                        {!showLabel && <span className="sr-only">{isLoading ? 'Processando...' : label}</span>}
                    </Button>
                </TooltipTrigger>
                {tooltip && (
                    <TooltipContent>
                        <p>{tooltip}</p>
                    </TooltipContent>
                )}
            </Tooltip>
        </TooltipProvider>
    );
} 
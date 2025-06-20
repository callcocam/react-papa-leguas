import React from 'react';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useConfirmationDialog } from '../../contexts/ConfirmationDialogContext';
import { type ActionRendererProps } from '../../types';
import { cn } from '@/lib/utils';
import { useActionProcessor } from '../ActionRenderer';

/**
 * Renderiza uma ação que dispara um callback no backend,
 * potencialmente com um diálogo de confirmação.
 */
export default function CallbackActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    const { confirm } = useConfirmationDialog();
    const { executeAction } = useActionProcessor();

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

    // A função que efetivamente executa a ação no backend
    const runAction = () => {
        executeAction(action, item);
    };

    const handleClick = (e: React.MouseEvent) => {
        e.stopPropagation();

        if (confirmation) {
            // Se a confirmação for necessária, usa o hook do diálogo
            confirm({
                title: confirmation.title || 'Você tem certeza?',
                message: confirmation.message,
                confirmText: confirmation.confirm_text,
                cancelText: confirmation.cancel_text,
                confirmVariant: confirmation.confirm_variant,
                onConfirm: runAction,
            });
        } else {
            // Caso contrário, executa a ação diretamente
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
                        disabled={disabled}
                        className={cn(
                            showLabel ? 'h-auto text-xs px-2 py-1.5' : 'h-8 w-8',
                            className
                        )}
                    >
                        {IconComponent && <IconComponent className={cn('h-4 w-4', showLabel && 'mr-2')} />}
                        {showLabel && <span>{label}</span>}
                        {!showLabel && <span className="sr-only">{label}</span>}
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
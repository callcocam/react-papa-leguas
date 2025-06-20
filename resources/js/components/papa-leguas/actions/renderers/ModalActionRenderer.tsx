import React from 'react';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { useModal } from '../../contexts/ModalContext';
import { type ActionRendererProps } from '../../types';
import { cn } from '@/lib/utils';

/**
 * Renderiza uma ação que abre um modal ou um slide-over.
 */
export default function ModalActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    const { openModal } = useModal();

    if (action.hidden) {
        return null;
    }

    const {
        disabled,
        variant = 'ghost',
        label,
        tooltip,
        showLabel,
        className,
    } = action;

    const handleClick = (e: React.MouseEvent) => {
        e.stopPropagation();

        openModal({
            title: action.modal_title || action.label,
            mode: action.mode,
            width: action.width,
            // O conteúdo virá aqui no futuro
            content: (
                <div>
                    <p>Conteúdo do modal para o item: {item.id}</p>
                    <p>Ação: {action.key}</p>
                    <p>Formulário a ser implementado...</p>
                </div>
            ),
        });
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
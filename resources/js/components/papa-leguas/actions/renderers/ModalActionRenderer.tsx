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

/**
 * Renderiza uma ação que abre um modal ou um slide-over.
 */
export default function ModalActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    const { openModal } = useModal();

    const handleClick = (e: React.MouseEvent) => {
        e.stopPropagation();

        openModal({
            title: action.modal_title || action.label,
            mode: action.mode,
            // O conteúdo virá aqui no futuro
            content: (
                <div>
                    <p>Conteúdo do modal para o item: {item.id}</p>
                    <p>Ação: {action.key}</p>
                    <p>Formulário a ser implementado...</p>
                </div>
            )
        });
    };

    return (
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger asChild>
                    <Button
                        variant={action.variant || 'ghost'}
                        size="icon"
                        onClick={handleClick}
                        disabled={action.disabled}
                    >
                        {IconComponent && <IconComponent className="h-4 w-4" />}
                        <span className="sr-only">{action.label}</span>
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{action.tooltip || action.label}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    );
} 
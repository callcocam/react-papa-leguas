import React from 'react';
import { Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { type ActionRendererProps } from '../../types';

/**
 * Renderizador de Ação Link
 * Cria um botão de ícone que funciona como um link e exibe um tooltip.
 */
export default function LinkActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    const url = typeof action.url === 'function' ? action.url(item) : (action.url || '#');

    const handleClick = (e: React.MouseEvent) => {
        e.stopPropagation();
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
                        className={action.className}
                        asChild
                    >
                        <Link href={url}>
                            {IconComponent && <IconComponent className="h-4 w-4" />}
                            <span className="sr-only">{action.label}</span>
                        </Link>
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{action.tooltip || action.label}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    );
} 
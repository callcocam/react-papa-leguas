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
import { cn } from '@/lib/utils';

/**
 * Renderiza uma ação como um link, geralmente para navegação.
 * Usa o componente Link do Inertia para navegação SPA.
 */
export default function LinkActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    if (action.hidden) {
        return null;
    }

    const { disabled, variant = 'ghost', label, tooltip, showLabel } = action;

    // Resolve a URL, seja ela uma string ou uma função
    const finalUrl = typeof action.url === 'function' ? action.url(item) : (action.url || '#');

    return (
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger asChild>
                    <Button
                        asChild
                        variant={variant}
                        size={showLabel ? 'sm' : 'icon'}
                        disabled={disabled}
                        className={showLabel ? 'h-auto text-xs px-2 py-1.5' : 'h-8 w-8'}
                    >
                        <Link href={finalUrl}>
                            {IconComponent && <IconComponent className={cn('h-4 w-4', showLabel && 'mr-2')} />}
                            {showLabel && <span>{label}</span>}
                            {!showLabel && <span className="sr-only">{label}</span>}
                        </Link>
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
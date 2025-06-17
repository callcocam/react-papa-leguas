import React from 'react';
import { Button } from '@/components/ui/button';
import { TableActionsProps } from '../types';

export function TableActions({ actions, type, onActionClick, loading, row }: TableActionsProps) {
    const handleActionClick = (action: any) => {
        if (onActionClick) {
            onActionClick(action, row);
        }
    };

    if (actions.length === 0) {
        return null;
    }

    return (
        <div className="flex items-center gap-2">
            {actions.map((action) => (
                <Button
                    key={action.key}
                    variant={action.variant || 'default'}
                    size={action.size === 'md' ? 'default' : (action.size || 'sm')}
                    onClick={() => handleActionClick(action)}
                    disabled={loading || action.disabled}
                >
                    {action.icon && (
                        <i className={`lucide lucide-${action.icon} mr-2 h-4 w-4`} />
                    )}
                    {action.label}
                </Button>
            ))}
        </div>
    );
}
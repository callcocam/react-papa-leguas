import React, { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { MoreHorizontal } from 'lucide-react';
import { TableAction, TableRow } from '../types';

interface TableRowActionsProps {
    actions: TableAction[];
    row: TableRow;
    onActionClick?: (action: TableAction, row?: TableRow) => void;
    loading?: boolean;
}

export function TableRowActions({ actions, row, onActionClick, loading }: TableRowActionsProps) {
    const [confirmAction, setConfirmAction] = useState<TableAction | null>(null);

    const handleActionClick = (action: TableAction) => {
        if (action.confirm) {
            setConfirmAction(action);
        } else {
            executeAction(action);
        }
    };

    const executeAction = (action: TableAction) => {
        if (onActionClick) {
            onActionClick(action, row);
        }
        setConfirmAction(null);
    };

    const visibleActions = actions.filter(action => action.visible !== false);

    if (visibleActions.length === 0) {
        return null;
    }

    // Se há apenas uma ação, mostrar como botão simples
    if (visibleActions.length === 1) {
        const action = visibleActions[0];
        
        return (
            <>
                <Button
                    variant={action.variant || 'ghost'}
                    size={action.size as any || 'sm'}  
                    onClick={() => handleActionClick(action)}
                    disabled={loading || action.disabled}
                    className="h-8 px-2"
                >
                    {action.icon && (
                        <i className={`lucide lucide-${action.icon} h-4 w-4`} />
                    )}
                    <span className="sr-only">{action.label}</span>
                </Button>

                {/* Dialog de confirmação */}
                <AlertDialog open={!!confirmAction} onOpenChange={() => setConfirmAction(null)}>
                    <AlertDialogContent>
                        <AlertDialogHeader>
                            <AlertDialogTitle>
                                {confirmAction?.confirmTitle || 'Confirmar ação'}
                            </AlertDialogTitle>
                            <AlertDialogDescription>
                                {confirmAction?.confirmMessage || 'Tem certeza que deseja executar esta ação?'}
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel>Cancelar</AlertDialogCancel>
                            <AlertDialogAction
                                onClick={() => confirmAction && executeAction(confirmAction)}
                            >
                                Confirmar
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            </>
        );
    }

    // Múltiplas ações - mostrar como dropdown
    return (
        <>
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-8 w-8 p-0"
                        disabled={loading}
                    >
                        <MoreHorizontal className="h-4 w-4" />
                        <span className="sr-only">Abrir menu de ações</span>
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-[160px]">
                    {visibleActions.map((action, index) => {
                        const isDangerous = action.color === 'danger';
                        
                        return (
                            <React.Fragment key={action.key}>
                                <DropdownMenuItem
                                    onClick={() => handleActionClick(action)}
                                    disabled={action.disabled}
                                    className={isDangerous ? 'text-red-600 focus:text-red-600' : ''}
                                >
                                    {action.icon && (
                                        <i className={`lucide lucide-${action.icon} mr-2 h-4 w-4`} />
                                    )}
                                    {action.label}
                                </DropdownMenuItem>
                                
                                {/* Separador antes de ações perigosas */}
                                {index < visibleActions.length - 1 && 
                                 visibleActions[index + 1]?.color === 'danger' && 
                                 action.color !== 'danger' && (
                                    <DropdownMenuSeparator />
                                )}
                            </React.Fragment>
                        );
                    })}
                </DropdownMenuContent>
            </DropdownMenu>

            {/* Dialog de confirmação */}
            <AlertDialog open={!!confirmAction} onOpenChange={() => setConfirmAction(null)}>
                <AlertDialogContent>
                    <AlertDialogHeader>
                        <AlertDialogTitle>
                            {confirmAction?.confirmTitle || 'Confirmar ação'}
                        </AlertDialogTitle>
                        <AlertDialogDescription>
                            {confirmAction?.confirmMessage || 'Tem certeza que deseja executar esta ação?'}
                        </AlertDialogDescription>
                    </AlertDialogHeader>
                    <AlertDialogFooter>
                        <AlertDialogCancel>Cancelar</AlertDialogCancel>
                        <AlertDialogAction
                            onClick={() => confirmAction && executeAction(confirmAction)}
                            className={confirmAction?.color === 'danger' ? 'bg-red-600 hover:bg-red-700' : ''}
                        >
                            Confirmar
                        </AlertDialogAction>
                    </AlertDialogFooter>
                </AlertDialogContent>
            </AlertDialog>
        </>
    );
}
import React, { useContext } from 'react';
import { Button } from '@/components/ui/button';
import { type TableAction } from '../../types';
import { TableContext } from '../../contexts/TableContext';
import { useConfirmationDialog } from '../../contexts/ConfirmationDialogContext';
import { useActionProcessor } from '../../hooks/useActionProcessor';
import { LucideIcon } from 'lucide-react';
import { Icons } from '../../icons';
import { router } from '@inertiajs/react';

interface BulkActionRendererProps {
    action: TableAction;
}
/**
 * Renderizador para uma única ação em lote.
 * Ele aparece na BulkActionsBar.
 */
export default function BulkActionRenderer({ action }: BulkActionRendererProps) {
    const { selectedRows, meta } = useContext(TableContext);
    const { confirm } = useConfirmationDialog();
    const { processAction, isLoading } = useActionProcessor();

    const IconComponent = action.icon && typeof action.icon === 'string'
        ? (Icons[action.icon as keyof typeof Icons] as LucideIcon)
        : null;

    const executeAction = async () => {
        if (!meta?.key) {
            console.error("A 'key' da tabela não foi definida nos metadados.");
            return;
        }
        const selectedIds = Array.from(selectedRows ?? []);
        const result = await processAction({
            table: meta.key,
            actionKey: action.key,
            actionType: 'bulk',
            item: { selectedIds: selectedIds },
        });

        if (result?.success) {
            if (result.reload !== false) {
                router.reload();
            }
        }
    };

    const handleClick = () => {
        if (action.confirmation) {
            confirm({
                title: action.confirmation.title || 'Confirmar Ação em Lote',
                message: action.confirmation.message,
                confirmText: action.confirmation.confirm_text || 'Confirmar',
                cancelText: action.confirmation.cancel_text || 'Cancelar',
                confirmVariant: action.confirmation.confirm_variant || 'destructive',
                onConfirm: executeAction,
            });
        } else {
            executeAction();
        }
    };

    const isDisabled = !selectedRows || selectedRows.size === 0 || isLoading;

    return (
        <Button
            variant={action.variant || 'destructive'}
            size={action.size || 'sm'}
            onClick={handleClick}
            disabled={isDisabled}
            title={action.tooltip || action.label}
        >
            {IconComponent && <IconComponent className="mr-0 h-4 w-4" />}
            <span>{action.label}</span>
        </Button>
    );
} 
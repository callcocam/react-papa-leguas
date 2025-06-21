import React, { useContext } from 'react';
import { Button } from '@/components/ui/button';
import { TableContext } from '../contexts/TableContext';
import { type TableAction } from '../types';
import ActionRenderer from '../actions/ActionRenderer';
import { X } from 'lucide-react';

interface BulkActionsBarProps {
    actions: TableAction[];
}

export default function BulkActionsBar({ actions }: BulkActionsBarProps) {
    const { selectedRows, clearSelection } = useContext(TableContext);
    const selectedCount = selectedRows?.size || 0;

    if (selectedCount === 0) {
        return null;
    }

    return (
        <div className="fixed bottom-4 left-1/2 -translate-x-1/2 z-50">
            <div className="flex items-center gap-4 p-3 bg-background border rounded-lg shadow-xl animate-in fade-in-0 slide-in-from-bottom-5">
                <div className="text-sm font-medium">
                    {selectedCount} item(s) selecionado(s)
                </div>
                <div className="h-6 border-l" />
                <div className="flex items-center gap-2">
                    {actions.map((action) => (
                        <ActionRenderer
                            key={action.key}
                            action={{...action, showLabel: true}} // Forçar a exibição do label
                            item={{ selectedIds: Array.from(selectedRows || []) }}
                        />
                    ))}
                </div>
                <div className="h-6 border-l" />
                <Button
                    variant="ghost"
                    size="icon"
                    className="h-8 w-8"
                    onClick={() => clearSelection && clearSelection()}
                >
                    <X className="h-4 w-4" />
                    <span className="sr-only">Limpar seleção</span>
                </Button>
            </div>
        </div>
    );
} 
import React, { useState } from 'react';
import { TableBodyProps, TableRow } from '../types';
import { TableCell } from './TableCell';
import { TableRowActions } from './TableRowActions';
import { Checkbox } from '@/components/ui/checkbox';

export function TableBody({ 
    data, 
    columns, 
    actions, 
    onActionClick, 
    onBulkActionClick, 
    loading 
}: TableBodyProps) {
    const [selectedRows, setSelectedRows] = useState<Set<string | number>>(new Set());

    const handleSelectAll = (checked: boolean) => {
        if (checked) {
            setSelectedRows(new Set(data.map(row => row.id)));
        } else {
            setSelectedRows(new Set());
        }
    };

    const handleSelectRow = (rowId: string | number, checked: boolean) => {
        const newSelected = new Set(selectedRows);
        if (checked) {
            newSelected.add(rowId);
        } else {
            newSelected.delete(rowId);
        }
        setSelectedRows(newSelected);
    };

    const handleBulkAction = (action: any) => {
        if (!onBulkActionClick) return;
        
        const selectedRowsData = data.filter(row => selectedRows.has(row.id));
        onBulkActionClick(action, selectedRowsData);
        setSelectedRows(new Set()); // Limpar seleção após ação
    };

    const visibleColumns = columns.filter(column => column.visible !== false);
    const hasRowActions = actions?.row && actions.row.length > 0;
    const hasBulkActions = actions?.bulk && actions.bulk.length > 0;
    const allSelected = data.length > 0 && selectedRows.size === data.length;
    const someSelected = selectedRows.size > 0 && selectedRows.size < data.length;

    return (
        <tbody className="divide-y">
            {/* Linha de ações em lote (se houver itens selecionados) */}
            {hasBulkActions && selectedRows.size > 0 && (
                <tr className="bg-blue-50 dark:bg-blue-950/50">
                    <td colSpan={visibleColumns.length + (hasRowActions ? 1 : 0)} className="px-4 py-3">
                        <div className="flex items-center justify-between">
                            <span className="text-sm text-blue-700 dark:text-blue-300">
                                {selectedRows.size} item(ns) selecionado(s)
                            </span>
                            <div className="flex gap-2">
                                {actions?.bulk?.map((action) => (
                                    <button
                                        key={action.key}
                                        onClick={() => handleBulkAction(action)}
                                        className="inline-flex items-center gap-1 rounded px-3 py-1 text-sm font-medium text-blue-700 hover:bg-blue-100 dark:text-blue-300 dark:hover:bg-blue-900"
                                        disabled={loading}
                                    >
                                        {action.icon && (
                                            <i className={`lucide lucide-${action.icon} h-4 w-4`} />
                                        )}
                                        {action.label}
                                    </button>
                                ))}
                            </div>
                        </div>
                    </td>
                </tr>
            )}

            {/* Cabeçalho com checkbox (se há ações em lote) */}
            {hasBulkActions && (
                <tr className="border-b bg-muted/25">
                    <td className="px-4 py-2">
                        <Checkbox
                            checked={allSelected}
                            ref={(el) => {
                                if (el) el.indeterminate = someSelected;
                            }}
                            onCheckedChange={handleSelectAll}
                            disabled={loading}
                        />
                    </td>
                    {visibleColumns.map((column) => (
                        <td key={column.key} className="px-4 py-2 text-xs font-medium text-muted-foreground">
                            {column.label}
                        </td>
                    ))}
                    {hasRowActions && (
                        <td className="px-4 py-2 text-xs font-medium text-muted-foreground text-right">
                            Ações
                        </td>
                    )}
                </tr>
            )}

            {/* Linhas de dados */}
            {data.map((row) => (
                <tr 
                    key={row.id} 
                    className="hover:bg-muted/50 transition-colors"
                >
                    {/* Checkbox para seleção em lote */}
                    {hasBulkActions && (
                        <td className="px-4 py-3">
                            <Checkbox
                                checked={selectedRows.has(row.id)}
                                onCheckedChange={(checked) => handleSelectRow(row.id, checked as boolean)}
                                disabled={loading}
                            />
                        </td>
                    )}

                    {/* Células de dados */}
                    {visibleColumns.map((column) => (
                        <TableCell
                            key={column.key}
                            column={column}
                            row={row}
                            value={row[column.key]}
                        />
                    ))}

                    {/* Ações da linha */}
                    {hasRowActions && (
                        <td className="px-4 py-3 text-right">
                            <TableRowActions
                                actions={actions.row || []}
                                row={row}
                                onActionClick={onActionClick}
                                loading={loading}
                            />
                        </td>
                    )}
                </tr>
            ))}
        </tbody>
    );
}
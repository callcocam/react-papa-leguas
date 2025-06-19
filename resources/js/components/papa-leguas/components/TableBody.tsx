import React from 'react';
import { TableBody as UITableBody, TableCell, TableRow } from '@/components/ui/table';
import ColumnRenderer from '../columns/ColumnRenderer';
import ActionRenderer from '../actions/ActionRenderer';
import { type TableColumn, type TableAction } from '../types';

// Utilitário para gerar keys únicos
const generateUniqueKey = (...parts: (string | number | undefined)[]): string => {
    return parts.filter(Boolean).join('-');
};

interface TableBodyProps {
    data: any[];
    columns: TableColumn[];
    actions: TableAction[];
    loading?: boolean;
}

export default function TableBody({
    data,
    columns,
    actions,
    loading = false
}: TableBodyProps) {
    if (loading) {
        return (
            <UITableBody>
                <TableRow>
                    <TableCell 
                        colSpan={columns.length + 1} // +1 para coluna de ações (sempre presente)
                        className="text-center py-8"
                    >
                        <div className="flex items-center justify-center">
                            <span className="animate-spin mr-2">⚪</span>
                            Carregando dados...
                        </div>
                    </TableCell>
                </TableRow>
            </UITableBody>
        );
    }

    if (data.length === 0) {
        return (
            <UITableBody>
                <TableRow>
                    <TableCell 
                        colSpan={columns.length + 1} // +1 para coluna de ações (sempre presente)
                        className="text-center py-8 text-gray-500 dark:text-gray-400"
                    >
                        Nenhum registro encontrado
                    </TableCell>
                </TableRow>
            </UITableBody>
        );
    }

    return (
        <UITableBody>
            {data.map((row, rowIndex) => (
                <TableRow key={generateUniqueKey('row', row.id, rowIndex)}>
                    {columns.map((column, columnIndex) => (
                        <TableCell 
                            key={generateUniqueKey('cell', row.id, rowIndex, column.key, columnIndex)}
                            style={{ textAlign: column.alignment || 'left' }}
                            className={column.hidden ? 'hidden' : ''}
                        >
                            <ColumnRenderer
                                column={column}
                                value={row[column.key || column.name || '']}
                                item={row}
                            />
                        </TableCell>
                    ))}
                    {/* ✅ USAR AÇÕES DO ITEM - vindas do backend via _actions */}
                    {(row._actions && row._actions.length > 0) && (
                        <TableCell 
                            key={generateUniqueKey('actions', row.id, rowIndex)}
                            className="text-center"
                        >
                            <div className="flex items-center justify-center gap-2">
                                {row._actions.map((action: any, actionIndex: number) => (
                                    <ActionRenderer
                                        key={generateUniqueKey('action', action.key, rowIndex, actionIndex)}
                                        action={action}
                                        item={row}
                                    />
                                ))}
                            </div>
                        </TableCell>
                    )}
                </TableRow>
            ))}
        </UITableBody>
    );
} 
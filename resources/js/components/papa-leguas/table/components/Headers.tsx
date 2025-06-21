import React, { useContext } from 'react';
import { TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { type TableColumn, type TableAction } from '../../types';
import { TableContext } from '../contexts/TableContext';
import { Checkbox } from '@/components/ui/checkbox';

// Utilitário para gerar keys únicos
const generateUniqueKey = (...parts: (string | number | undefined)[]): string => {
    return parts.filter(Boolean).join('-');
};

interface HeadersProps {
    columns: TableColumn[];
    actions: TableAction[];
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
    sortColumn?: string;
    sortDirection?: 'asc' | 'desc';
}

export default function Headers({
    columns,
    actions,
    onSort,
    sortColumn,
    sortDirection
}: HeadersProps) {
    const { toggleSelectAll, isAllSelected, isSomeSelected } = useContext(TableContext);

    const handleSort = (column: TableColumn) => {
        if (!column.sortable || !onSort) return;
        
        const newDirection = sortColumn === column.key && sortDirection === 'asc' ? 'desc' : 'asc';
        onSort(column.key || column.name || '', newDirection);
    };

    return (
        <TableHeader>
            <TableRow>
                <TableHead className="w-[40px]">
                    <Checkbox
                        checked={isAllSelected || isSomeSelected}
                        onCheckedChange={toggleSelectAll}
                        aria-label="Selecionar todos"
                        data-state={isSomeSelected ? 'indeterminate' : (isAllSelected ? 'checked' : 'unchecked')}
                    />
                </TableHead>
                {columns.map((column, columnIndex) => (
                    <TableHead 
                        key={generateUniqueKey('header', column.key, columnIndex)}
                        className={`${column.width ? `w-[${column.width}]` : ''} ${column.sortable ? 'cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800' : ''}`}
                        style={{ 
                            textAlign: column.alignment || 'left',
                            width: column.width 
                        }}
                        onClick={() => handleSort(column)}
                    >
                        <div className="flex items-center gap-2">
                            {column.label}
                            {column.sortable && (
                                <span className="text-gray-400 text-xs">
                                    {sortColumn === (column.key || column.name) ? (
                                        sortDirection === 'asc' ? '↑' : '↓'
                                    ) : (
                                        '↕'
                                    )}
                                </span>
                            )}
                        </div>
                    </TableHead>
                ))}
                {/* ✅ SEMPRE MOSTRAR COLUNA DE AÇÕES - ações vêm por item */}
                <TableHead key="actions-header" className="text-center">
                    Ações
                </TableHead>
            </TableRow>
        </TableHeader>
    );
} 
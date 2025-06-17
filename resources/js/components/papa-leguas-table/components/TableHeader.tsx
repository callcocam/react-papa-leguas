import React, { useState } from 'react';
import { ChevronUp, ChevronDown, ChevronsUpDown } from 'lucide-react';
import { TableHeaderProps } from '../types';

export function TableHeader({ columns, onSortChange, loading }: TableHeaderProps) {
    const [sortColumn, setSortColumn] = useState<string | null>(null);
    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');

    const handleSort = (columnKey: string) => {
        if (!onSortChange || loading) return;

        let direction: 'asc' | 'desc' = 'asc';
        
        if (sortColumn === columnKey) {
            direction = sortDirection === 'asc' ? 'desc' : 'asc';
        }

        setSortColumn(columnKey);
        setSortDirection(direction);
        onSortChange(columnKey, direction);
    };

    const getSortIcon = (columnKey: string, sortable: boolean) => {
        if (!sortable) return null;

        if (sortColumn === columnKey) {
            return sortDirection === 'asc' ? (
                <ChevronUp className="h-4 w-4" />
            ) : (
                <ChevronDown className="h-4 w-4" />
            );
        }

        return <ChevronsUpDown className="h-4 w-4 opacity-50" />;
    };

    return (
        <thead className="border-b bg-muted/50">
            <tr>
                {columns
                    .filter(column => column.visible !== false)
                    .map((column) => (
                        <th
                            key={column.key}
                            className={`px-4 py-3 text-left font-medium text-muted-foreground ${
                                column.align === 'center' ? 'text-center' : 
                                column.align === 'right' ? 'text-right' : 'text-left'
                            } ${
                                column.sortable && !loading ? 'cursor-pointer hover:text-foreground' : ''
                            }`}
                            style={{ width: column.width }}
                            onClick={() => column.sortable && handleSort(column.key)}
                        >
                            <div className={`flex items-center gap-2 ${
                                column.align === 'center' ? 'justify-center' : 
                                column.align === 'right' ? 'justify-end' : 'justify-start'
                            }`}>
                                <span>{column.label}</span>
                                {getSortIcon(column.key, column.sortable || false)}
                            </div>
                        </th>
                    ))}
            </tr>
        </thead>
    );
} 
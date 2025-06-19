import React from 'react';
import { Table as UITable } from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';
import Headers from './Headers';
import TableBody from './TableBody';
import Pagination from './Pagination';
import { type TableColumn, type TableAction } from '../types';

interface TableProps {
    data: any[];
    columns: TableColumn[];
    actions: TableAction[];
    loading?: boolean;
    pagination?: any;
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
    onPageChange?: (page: number) => void;
    sortColumn?: string;
    sortDirection?: 'asc' | 'desc';
}

export default function Table({
    data,
    columns,
    actions,
    loading = false,
    pagination,
    onSort,
    onPageChange,
    sortColumn,
    sortDirection
}: TableProps) {
    return (
        <Card>
            <CardContent className="p-0">
                <UITable>
                    <Headers
                        columns={columns}
                        actions={actions}
                        onSort={onSort}
                        sortColumn={sortColumn}
                        sortDirection={sortDirection}
                    />
                    <TableBody
                        data={data}
                        columns={columns}
                        actions={actions}
                        loading={loading}
                    />
                </UITable>
                
                {pagination && (
                    <Pagination
                        pagination={pagination}
                        onPageChange={onPageChange}
                    />
                )}
            </CardContent>
        </Card>
    );
} 
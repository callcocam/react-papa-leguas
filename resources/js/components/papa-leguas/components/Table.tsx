import React, { useContext } from 'react';
import { Table as UITable } from '@/components/ui/table';
import { Card, CardContent } from '@/components/ui/card';
import Headers from './Headers';
import TableBody from './TableBody';
import Pagination from './Pagination';
import { type TableColumn, type TableAction } from '../types';
import { TableContext, TableContextProps } from '../contexts/TableContext';
import BulkActionsBar from './BulkActionsBar';

interface TableProps {
    columns: TableColumn[];
    actions: {
        row: TableAction[];
        bulk: TableAction[];
    };
    loading?: boolean;
    pagination?: any;
    onSort?: (column: string, direction: 'asc' | 'desc') => void;
    onPageChange?: (page: number) => void;
    sortColumn?: string;
    sortDirection?: 'asc' | 'desc';
}

export default function Table({
    columns,
    actions,
    loading = false,
    pagination,
    onSort,
    onPageChange,
    sortColumn,
    sortDirection
}: TableProps) {

    const { tableData } = useContext(TableContext) as TableContextProps;

    return (
        <Card>
            <CardContent className="p-0">
                <BulkActionsBar actions={actions?.bulk || []} />
                <UITable>
                    <Headers
                        columns={columns}
                        actions={actions?.row || []}
                        onSort={onSort}
                        sortColumn={sortColumn}
                        sortDirection={sortDirection}
                    />
                    <TableBody
                        data={tableData || []}
                        columns={columns}
                        actions={actions?.row || []}
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
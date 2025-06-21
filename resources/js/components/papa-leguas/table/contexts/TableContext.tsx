import React, { createContext, useState, ReactNode, useEffect, useCallback } from 'react';
import { type TableMeta } from '../../types';

type DataItem = Record<string, any>;
type RowId = string | number;

export interface TableContextProps {
    tableData: DataItem[];
    setTableData: React.Dispatch<React.SetStateAction<DataItem[]>>;
    meta?: TableMeta;
    // Estado de seleção
    selectedRows: Set<RowId>;
    // Funções de seleção
    toggleRow: (id: RowId) => void;
    toggleSelectAll: () => void;
    clearSelection: () => void;
    // Verificadores de estado
    isAllSelected: boolean;
    isSomeSelected: boolean;
}

export const TableContext = createContext<Partial<TableContextProps>>({});

interface TableProviderProps {
    children: ReactNode;
    initialData: DataItem[];
    meta?: TableMeta;
}

export const TableProvider: React.FC<TableProviderProps> = ({ children, initialData, meta }) => {
    const [tableData, setTableData] = useState<DataItem[]>(initialData);
    const [selectedRows, setSelectedRows] = useState<Set<RowId>>(new Set());

    // Sincroniza o estado interno com as props externas quando elas mudam.
    useEffect(() => {
        setTableData(initialData);
    }, [initialData]);

    // Limpa a seleção sempre que os dados da tabela mudam (ex: paginação)
    useEffect(() => {
        setSelectedRows(new Set());
    }, [initialData]);

    const toggleRow = useCallback((id: RowId) => {
        setSelectedRows(prev => {
            const newSelection = new Set(prev);
            if (newSelection.has(id)) {
                newSelection.delete(id);
            } else {
                newSelection.add(id);
            }
            return newSelection;
        });
    }, []);

    const allRowIdsOnPage = initialData.map(row => row.id).filter(id => id !== undefined);
    const isAllSelected = allRowIdsOnPage.length > 0 && allRowIdsOnPage.every(id => selectedRows.has(id));

    const toggleSelectAll = useCallback(() => {
        if (isAllSelected) {
            setSelectedRows(new Set());
        } else {
            setSelectedRows(new Set(allRowIdsOnPage));
        }
    }, [isAllSelected, allRowIdsOnPage]);

    const clearSelection = useCallback(() => {
        setSelectedRows(new Set());
    }, []);

    return (
        <TableContext.Provider
            value={{
                tableData,
                setTableData,
                meta,
                selectedRows,
                toggleRow,
                toggleSelectAll,
                clearSelection,
                isAllSelected,
                isSomeSelected: selectedRows.size > 0 && !isAllSelected,
            }}
        >
            {children}
        </TableContext.Provider>
    );
}; 
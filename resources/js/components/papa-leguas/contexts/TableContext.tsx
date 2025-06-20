import React, { createContext, useState, ReactNode } from 'react';
import { type PapaLeguasTableMeta } from '../types';

type DataItem = Record<string, any>;

export interface TableContextProps {
    tableData: DataItem[];
    setTableData: React.Dispatch<React.SetStateAction<DataItem[]>>;
    meta?: PapaLeguasTableMeta;
}

export const TableContext = createContext<Partial<TableContextProps>>({});

interface TableProviderProps {
    children: ReactNode;
    initialData: DataItem[];
    meta?: PapaLeguasTableMeta;
}

export const TableProvider: React.FC<TableProviderProps> = ({ children, initialData, meta }) => {
    const [tableData, setTableData] = useState<DataItem[]>(initialData);

    return (
        <TableContext.Provider value={{ tableData, setTableData, meta }}>
            {children}
        </TableContext.Provider>
    );
}; 
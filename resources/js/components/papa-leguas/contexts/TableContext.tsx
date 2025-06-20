import React, { createContext, useState, ReactNode, useEffect } from 'react';
import { type TableMeta } from '../types';

type DataItem = Record<string, any>;

export interface TableContextProps {
    tableData: DataItem[];
    setTableData: React.Dispatch<React.SetStateAction<DataItem[]>>;
    meta?: TableMeta;
}

export const TableContext = createContext<Partial<TableContextProps>>({});

interface TableProviderProps {
    children: ReactNode;
    initialData: DataItem[];
    meta?: TableMeta;
}

export const TableProvider: React.FC<TableProviderProps> = ({ children, initialData, meta }) => {
    const [tableData, setTableData] = useState<DataItem[]>(initialData);

    // Sincroniza o estado interno com as props externas quando elas mudam.
    // Isso é crucial para que os dados sejam atualizados após ações do Inertia (filtragem, paginação).
    useEffect(() => {
        setTableData(initialData);
    }, [initialData]);

    return (
        <TableContext.Provider value={{ tableData, setTableData, meta }}>
            {children}
        </TableContext.Provider>
    );
}; 
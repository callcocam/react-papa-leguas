import React, { createContext, useState, ReactNode } from 'react';

type DataItem = Record<string, any>;

interface TableContextProps {
    tableData: DataItem[];
    setTableData: React.Dispatch<React.SetStateAction<DataItem[]>>;
}

export const TableContext = createContext<Partial<TableContextProps>>({});

interface TableProviderProps {
    children: ReactNode;
    initialData: DataItem[];
}

export const TableProvider: React.FC<TableProviderProps> = ({ children, initialData }) => {
    const [tableData, setTableData] = useState<DataItem[]>(initialData);

    return (
        <TableContext.Provider value={{ tableData, setTableData }}>
            {children}
        </TableContext.Provider>
    );
}; 
export interface TableColumn {
    key: string;
    label: string;
    type?: 'text' | 'badge' | 'boolean' | 'date' | 'compound' | 'editable';
    sortable?: boolean;
    searchable?: boolean;
    hidden?: boolean;
    width?: string;
    alignment?: 'left' | 'center' | 'right';
    renderAs?: string; // Usado tanto para renderização quanto para edição
}

export interface TableConfig {
    // ... existing code ...
}

export interface PapaLeguasTableMeta {
    key: string;
    title: string;
    description?: string;
    [key: string]: any; // Permite outras propriedades
}

export interface PapaLeguasTableProps {
    data: any[];
    // ... existing code ...
    actions?: TableAction[];
    loading?: boolean;
    error?: string;
    meta?: PapaLeguasTableMeta;
} 
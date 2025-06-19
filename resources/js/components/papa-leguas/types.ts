export interface RendererProps {
    value: any;
    item: any;
    column: TableColumn;
}

export interface TableColumn {
    name?: string;
    key?: string;
    label?: string;
    renderAs?: string;
    width?: string;
    alignment?: 'left' | 'center' | 'right';
    sortable?: boolean;
    hidden?: boolean;
}

export interface TableFilter {
    key: string;
    type: string;
    label?: string;
    placeholder?: string;
    options?: Record<string, any>;
    onApply?: () => void;
}

export interface FilterRendererProps {
    filter: TableFilter;
    value: any;
    onChange: (value: any) => void;
}

export interface TableAction {
    key: string;
    label: string;
    type?: 'edit' | 'delete' | 'view' | 'primary' | 'secondary' | 'custom' | 'link' | 'dropdown' | 'callback' | 'button' | 'route' | 'url';
    method?: 'get' | 'post' | 'put' | 'delete';
    url?: string | ((item: any) => string);
    onClick?: (item: any) => void;
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
    size?: 'sm' | 'md' | 'lg';
    icon?: string | React.ReactNode;
    disabled?: boolean;
    className?: string;
    tooltip?: string;
    confirmMessage?: string;
    renderAs?: string;
}

export interface ActionRendererProps {
    action: TableAction;
    item: any;
    IconComponent?: React.ElementType;
}

export interface PapaLeguasTableProps {
    data?: any[];
    columns?: TableColumn[];
    filters?: TableFilter[];
    actions?: TableAction[];
    loading?: boolean;
    error?: string;
    meta?: {
        title?: string;
        description?: string;
        searchable?: boolean;
        sortable?: boolean;
        filterable?: boolean;
    };
}
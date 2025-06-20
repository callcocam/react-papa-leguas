export interface RendererProps {
    value: any;
    item: any;
    column: TableColumn;
}

export interface TableMeta {
    key: string;
    title?: string;
    description?: string;
}

export interface TableColumn {
    key: string | null;
    label: string;
    sortable?: boolean;
    searchable?: boolean;
    hidden?: boolean;
    type?: string;
    renderAs?: string;
    rendererOptions?: Record<string, any>;
    options?: { value: string; label: string }[];
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

export interface TableRow {
    [key: string]: any;
}

export interface TableData {
    data: TableRow[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}
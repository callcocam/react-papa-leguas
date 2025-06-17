/**
 * Tipos para integração com o sistema Papa Leguas do backend
 */

// Dados de uma linha da tabela
export interface TableRow {
    id: string | number;
    [key: string]: any;
}

// Configuração de uma coluna
export interface TableColumn {
    key: string;
    label: string;
    type: 'text' | 'badge' | 'date' | 'boolean' | 'image' | 'currency' | 'actions';
    sortable?: boolean;
    searchable?: boolean;
    visible?: boolean;
    align?: 'left' | 'center' | 'right';
    width?: string;
    
    // Configurações específicas por tipo
    formatConfig?: {
        // Para badge
        colors?: Record<string, string>;
        
        // Para date
        dateFormat?: string;
        since?: boolean;
        
        // Para boolean
        trueIcon?: string;
        falseIcon?: string;
        trueColor?: string;
        falseColor?: string;
        trueLabel?: string;
        falseLabel?: string;
        
        // Para text
        copyable?: boolean;
        icon?: string;
        limit?: number;
        placeholder?: string;
        
        // Para currency
        currency?: string;
        
        // Para image
        renderAsImage?: boolean;
    };
}

// Configuração de um filtro
export interface TableFilter {
    key: string;
    label: string;
    type: 'text' | 'select' | 'date' | 'boolean';
    placeholder?: string;
    options?: Array<{ value: string; label: string }>;
    value?: any;
    multiple?: boolean;
}

// Configuração de uma ação
export interface TableAction {
    key: string;
    label: string;
    icon?: string;
    color?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger';
    variant?: 'default' | 'outline' | 'ghost';
    size?: 'sm' | 'md' | 'lg';
    route?: string;
    url?: string;
    method?: 'GET' | 'POST' | 'PUT' | 'DELETE';
    confirm?: boolean;
    confirmTitle?: string;
    confirmMessage?: string;
    visible?: boolean;
    disabled?: boolean;
}

// Configuração de ações em lote
export interface TableBulkAction {
    key: string;
    label: string;
    icon?: string;
    color?: 'primary' | 'secondary' | 'success' | 'warning' | 'danger';
    confirm?: boolean;
    confirmTitle?: string;
    confirmMessage?: string;
    limit?: number;
}

// Configuração de ações da tabela
export interface TableActions {
    header?: TableAction[];
    row?: TableAction[];
    bulk?: TableBulkAction[];
}

// Configuração de paginação
export interface TablePagination {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
    from: number;
    to: number;
    hasPages: boolean;
    hasMorePages: boolean;
    onFirstPage: boolean;
    onLastPage: boolean;
    links?: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
}

// Props principais do componente PapaLeguasTable
export interface PapaLeguasTableProps {
    // Dados da tabela (já processados pelo backend)
    data: TableRow[];
    
    // Configuração das colunas
    columns: TableColumn[];
    
    // Filtros disponíveis
    filters?: TableFilter[];
    
    // Ações disponíveis
    actions?: TableActions;
    
    // Informações de paginação
    pagination?: TablePagination;
    
    // Estados
    loading?: boolean;
    error?: string | null;
    
    // Styling
    className?: string;
    
    // Callbacks
    onFilterChange?: (filters: Record<string, any>) => void;
    onSortChange?: (column: string, direction: 'asc' | 'desc') => void;
    onPageChange?: (page: number) => void;
    onActionClick?: (action: TableAction, row?: TableRow) => void;
    onBulkActionClick?: (action: TableBulkAction, selectedRows: TableRow[]) => void;
}

// Props para componentes filhos
export interface TableHeaderProps {
    columns: TableColumn[];
    onSortChange?: (column: string, direction: 'asc' | 'desc') => void;
    loading?: boolean;
}

export interface TableBodyProps {
    data: TableRow[];
    columns: TableColumn[];
    actions?: TableActions;
    onActionClick?: (action: TableAction, row?: TableRow) => void;
    onBulkActionClick?: (action: TableBulkAction, selectedRows: TableRow[]) => void;
    loading?: boolean;
}

export interface TableFiltersProps {
    filters: TableFilter[];
    onFilterChange?: (filters: Record<string, any>) => void;
    loading?: boolean;
}

export interface TableActionsProps {
    actions: TableAction[];
    type: 'header' | 'row';
    onActionClick?: (action: TableAction, row?: TableRow) => void;
    loading?: boolean;
    row?: TableRow;
}

export interface TablePaginationProps {
    pagination: TablePagination;
    onPageChange?: (page: number) => void;
    loading?: boolean;
}

// Dados que vêm do backend PHP (via Inertia)
export interface PapaLeguasTableData {
    table: {
        data: TableRow[];
        columns: TableColumn[];
        filters?: TableFilter[];
        actions?: TableActions;
        pagination?: TablePagination;
        meta?: {
            title?: string;
            description?: string;
            searchable?: boolean;
            sortable?: boolean;
            filterable?: boolean;
        };
    };
    error?: string;
}

// Tipos principais da tabela
export interface PapaLeguasTableProps {
    // Dados obrigatórios
    data: any[]

    // Props dinâmicas (vem do backend via Inertia)
    columns?: ColumnConfig[]
    filters?: FilterConfig[]
    actions?: ActionConfig[]
    bulkActions?: BulkActionConfig[]
    permissions?: PermissionsData
    pagination?: PaginationData

    // Props de UI e comportamento
    loading?: boolean
    className?: string
    onRowClick?: (row: any, index: number) => void
    onSelectionChange?: (selectedRows: any[]) => void

    // Children declarativos (sintaxe JSX)
    children?: React.ReactNode

    // Configurações avançadas
    config?: any
    debug?: boolean // Habilita logs detalhados
}


// Tipos básicos (serão expandidos em arquivos específicos)
export interface ColumnConfig {
    key: string
    label: string
    sortable?: boolean
    filterable?: boolean
    visible?: boolean
    width?: string
    type?: 'text' | 'email' | 'money' | 'date' | 'status' | 'actions' | 'custom'
    permission?: string | string[]
    className?: string
    render?: (value: any, row: any, index: number) => React.ReactNode
    [key: string]: any
}

export interface FilterConfig {
    key: string
    label: string
    type: 'text' | 'select' | 'date' | 'daterange' | 'number' | 'boolean'
    options?: Array<{ value: any; label: string }>
    placeholder?: string
    [key: string]: any
}

export interface ActionConfig {
    key: string
    label: string
    icon?: string
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost'
    permission?: string | string[]
    onClick?: (row: any) => void
    route?: string
    method?: 'get' | 'post' | 'put' | 'delete'
    requireConfirmation?: boolean
    [key: string]: any
}

export interface BulkActionConfig {
    key: string
    label: string
    icon?: string
    variant?: 'default' | 'destructive' | 'outline' | 'secondary'
    permission?: string | string[]
    onClick?: (selectedRows: any[]) => void
    route?: string
    method?: 'post' | 'put' | 'delete'
    requireConfirmation?: boolean
    maxSelection?: number
    [key: string]: any
}

export interface PermissionsData {
    user_permissions: string[]
    user_roles: string[]
    is_super_admin: boolean
    [key: string]: any
}

export interface PaginationData {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number
    to: number
    links: Array<{
        url: string | null
        label: string
        active: boolean
    }>
    [key: string]: any
}

/**
 * Componente principal da tabela Papa Leguas
 * 
 * Suporta três modos de operação:
 * - Dynamic: Configuração via props (backend-driven)
 * - Declarative: Configuração via children (JSX)
 * - Hybrid: Ambos simultaneamente (children tem prioridade)
 */
export const PapaLeguasTable: React.FC<PapaLeguasTableProps> = ({
    children,
    columns,
    data,
    permissions,
    config,
    debug = false,
    ...props
}) => {
    return (
        <div className="papa-leguas-table">
            {children}
        </div>
    );
}
// PapaLeguasTable.Column = Column;
// PapaLeguasTable.Content = Content;
// PapaLeguasTable.Rows = Rows;
export default PapaLeguasTable;
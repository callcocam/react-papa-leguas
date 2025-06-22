 
export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href?: string;
}
 
export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

// ===== TIPOS PARA SISTEMA DE TABS =====

export interface TabConfig {
    id: string;
    label: string;
    icon?: string; // Nome do ícone Lucide
    badge?: string | number;
    color?: 'default' | 'primary' | 'secondary' | 'success' | 'warning' | 'destructive';
    disabled?: boolean;
    hidden?: boolean;
    active?: boolean; // Indica se a tab está ativa (vem do backend)
    content?: any; // Conteúdo da tab
    loadContent?: () => Promise<any>; // Para lazy loading
    keepAlive?: boolean; // Manter conteúdo carregado
    tableConfig?: {
        searchable?: boolean;
        sortable?: boolean;
        filterable?: boolean;
        paginated?: boolean;
        selectable?: boolean;
        pageSize?: number;
    };
}

export interface TabsConfig {
    variant?: 'default' | 'pills' | 'enclosed';
    size?: 'sm' | 'md' | 'lg';
    position?: 'top' | 'bottom' | 'left' | 'right';
    defaultTab?: string;
    fullWidth?: boolean;
    scrollable?: boolean;
    lazy?: boolean; // Carregamento lazy das tabs
    keepAlive?: boolean; // Manter conteúdo das tabs carregado
    showBadges?: boolean;
    showIcons?: boolean;
    // Callbacks
    onTabChange?: (tabId: string, previousTabId?: string) => void;
    onTabLoad?: (tabId: string) => void;
    onTabError?: (tabId: string, error: any) => void;
}

export interface TabbedTableData {
    tabs?: TabConfig[];
    tabsConfig?: TabsConfig;
}

// Tipos base importados do sistema de tabelas
export interface KanbanColumn {
    /** ID único da coluna */
    id: string;
    /** Título exibido na coluna */
    title: string;
    /** Chave do campo para filtrar dados */
    key: string;
    /** Função de filtro personalizada */
    filter?: (item: any) => boolean;
    /** Cor da coluna (hex) */
    color?: string;
    /** Ícone da coluna */
    icon?: string;
    /** Limite máximo de itens */
    maxItems?: number;
    /** Se a coluna é ordenável */
    sortable?: boolean;
    /** Configurações específicas da coluna */
    config?: Record<string, any>;
}

export interface KanbanAction {
    /** ID único da ação */
    id: string;
    /** Label da ação */
    label: string;
    /** Ícone da ação */
    icon?: string;
    /** Variante visual */
    variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
    /** Se requer confirmação */
    confirmation?: {
        title?: string;
        message: string;
        confirm_text?: string;
        cancel_text?: string;
        confirm_variant?: string;
    };
    /** Tooltip da ação */
    tooltip?: string;
    /** Se a ação está visível */
    visible?: boolean | ((item: any) => boolean);
    /** Configurações específicas da ação */
    config?: Record<string, any>;
}

export interface KanbanFilter {
    /** ID único do filtro */
    id: string;
    /** Label do filtro */
    label: string;
    /** Tipo do filtro */
    type: 'select' | 'text' | 'date' | 'number' | 'boolean';
    /** Opções para filtros select */
    options?: Array<{ value: any; label: string }>;
    /** Valor padrão */
    defaultValue?: any;
    /** Se é um filtro múltiplo */
    multiple?: boolean;
    /** Configurações específicas do filtro */
    config?: Record<string, any>;
}

export interface KanbanConfig {
    /** Permite busca global */
    searchable?: boolean;
    /** Mostra filtros */
    filterable?: boolean;
    /** Altura do board */
    height?: string;
    /** Colunas por linha */
    columnsPerRow?: number;
    /** Permite drag and drop */
    dragAndDrop?: boolean;
    /** Configurações de paginação */
    pagination?: {
        enabled: boolean;
        perPage: number;
    };
    /** Configurações visuais */
    appearance?: {
        cardSpacing?: string;
        columnSpacing?: string;
        headerHeight?: string;
    };
    /** Configurações específicas */
    [key: string]: any;
}

export interface KanbanMeta {
    /** Informações de paginação */
    pagination?: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    /** Chave da tabela/recurso */
    key?: string;
    /** Título do board */
    title?: string;
    /** Descrição do board */
    description?: string;
    /** Configurações adicionais */
    [key: string]: any;
}

export interface KanbanRendererProps {
    /** Dados do board (eager loaded) */
    data: any[];
    /** Configuração das colunas (do backend) */
    columns: KanbanColumn[];
    /** Ações disponíveis (do backend) */
    actions?: KanbanAction[];
    /** Filtros disponíveis (do backend) */
    filters?: KanbanFilter[];
    /** Configurações do board */
    config?: KanbanConfig;
    /** Metadados */
    meta?: KanbanMeta;
    /** Callback para ações */
    onAction?: (action: string, item: any, extra?: any) => void;
    /** Callback para filtros */
    onFilter?: (filters: Record<string, any>) => void;
    /** Callback para refresh */
    onRefresh?: () => void;
}

export interface KanbanBoardProps {
    /** Dados do board */
    data: any[];
    /** Configuração das colunas */
    columns: KanbanColumn[];
    /** Ações disponíveis */
    actions?: KanbanAction[];
    /** Filtros disponíveis */
    filters?: KanbanFilter[];
    /** Configurações do board */
    config?: KanbanConfig;
    /** Metadados */
    meta?: KanbanMeta;
    /** Callback para ações */
    onAction?: (action: string, item: any, extra?: any) => void;
    /** Callback para filtros */
    onFilter?: (filters: Record<string, any>) => void;
    /** Callback para refresh */
    onRefresh?: () => void;
}

export interface KanbanColumnProps {
    /** Configuração da coluna */
    column: KanbanColumn;
    /** Dados desta coluna */
    data: any[];
    /** Ações disponíveis */
    actions?: KanbanAction[];
    /** Callback para ações */
    onAction?: (action: string, item: any, extra?: any) => void;
    /** Callback para drag and drop */
    onDrop?: (item: any, fromColumn: string, toColumn: string) => void;
}

export interface KanbanCardProps {
    /** Item de dados do card */
    item: any;
    /** Configuração da coluna pai */
    column: KanbanColumn;
    /** Ações disponíveis */
    actions?: KanbanAction[];
    /** Se o card está expandido */
    isExpanded?: boolean;
    /** Callback para toggle expansão */
    onToggleExpansion?: () => void;
    /** Callback para ações */
    onAction?: (action: string, item: any, extra?: any) => void;
    /** Callback para drag and drop */
    onDragStart?: (item: any) => void;
    /** Se o card é arrastável */
    draggable?: boolean;
} 
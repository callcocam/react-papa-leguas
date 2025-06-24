import { UniqueIdentifier } from '@dnd-kit/core';

// Tipos base importados do sistema de tabelas
export interface KanbanColumn {
    /** ID único da coluna */
    id: string;
    /** Slug da coluna para matching */
    slug?: string;
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
    /** Ordem da coluna (número sequencial) */
    order?: number;
    /** Ordem de classificação alternativa */
    sort_order?: number;
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
    /** Tipo de CRUD (tickets, sales, orders, pipeline, generic, etc.) */
    crudType?: string;
    /** Slug do workflow para identificação dinâmica */
    workflowSlug?: string;
    /** Endpoint da API para mover cards */
    apiEndpoint?: string;
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
    /** Validação de transições personalizada */
    validateTransition?: (fromColumnId: string, toColumnId: string, item: any) => Promise<boolean>;
    /** Callback personalizado para mover cards */
    onMoveCard?: (cardId: string, fromColumnId: string, toColumnId: string, item: any) => Promise<any>;
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
    /** Tipo de CRUD para contexto */
    crudType?: string;
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
    /** Colunas formatadas do backend (sistema Papa Leguas) */
    tableColumns?: any[];
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

/**
 * Props para o componente KanbanColumn
 */
export interface KanbanColumnProps {
    /** Configuração da coluna */
    column: KanbanColumn;
    /** Dados filtrados para esta coluna */
    data: any[];
    /** Colunas da tabela para renderização */
    tableColumns?: any[];
    /** Ações disponíveis */
    actions?: KanbanAction[];
    /** Handler para ações */
    onAction?: (actionId: string, item: any, extra?: any) => void;
    /** Handler para drop (legacy) */
    onDrop?: (item: any, fromColumn: string, toColumn: string) => void;
    /** Se drag & drop está habilitado */
    dragAndDrop?: boolean;
    /** Se há um drag ativo no board */
    isDragActive?: boolean;
}

/**
 * Props para o componente KanbanCard
 */
export interface KanbanCardProps {
    /** Item de dados */
    item: any;
    /** Coluna */
    column: KanbanColumn;
    /** Colunas da tabela para renderização */
    tableColumns?: any[];
    /** Ações disponíveis para o card */
    actions?: KanbanAction[];
    /** Handler para ações */
    onAction?: (actionId: string, item: any, extra?: any) => void;
    /** Se o card é arrastável */
    draggable?: boolean;
    /** Se o card está sendo arrastado */
    isDragging?: boolean;
    /** Se é um overlay de drag */
    dragOverlay?: boolean;
}

/**
 * Tipos para Drag & Drop usando @dnd-kit
 */
export interface DragStartEvent {
    active: {
        id: UniqueIdentifier;
        data: {
            current?: any;
        };
    };
}

export interface DragEndEvent {
    active: {
        id: UniqueIdentifier;
        data: {
            current?: any;
        };
    };
    over: {
        id: UniqueIdentifier;
        data: {
            current?: any;
        };
    } | null;
}

export interface DragOverEvent {
    active: {
        id: UniqueIdentifier;
        data: {
            current?: any;
        };
    };
    over: {
        id: UniqueIdentifier;
        data: {
            current?: any;
        };
    } | null;
}

/**
 * Props para componentes com drag & drop
 */
export interface DraggableKanbanCardProps extends KanbanCardProps {
    isDragging?: boolean;
    dragOverlay?: boolean;
}

export interface DroppableKanbanColumnProps extends KanbanColumnProps {
    isOver?: boolean;
    canDrop?: boolean;
}

/**
 * Configurações de drag & drop
 */
export interface DragDropConfig {
    enabled?: boolean;
    validateTransition?: (fromColumnId: string, toColumnId: string, item: any) => boolean | Promise<boolean>;
    onDragStart?: (event: DragStartEvent) => void;
    onDragEnd?: (event: DragEndEvent) => void;
    onDragOver?: (event: DragOverEvent) => void;
    onMoveCard?: (cardId: string, fromColumnId: string, toColumnId: string, item: any) => Promise<boolean> | Promise<any>;
    /** Slug do workflow para contexto */
    workflowSlug?: string;
    /** Endpoint da API */
    apiEndpoint?: string;
}

/**
 * Dados de movimento de card
 */
export interface CardMoveData {
    cardId: string;
    fromColumnId: string;
    toColumnId: string;
    fromIndex: number;
    toIndex: number;
    item: any;
    /** Tipo de CRUD */
    crudType?: string;
    /** Dados adicionais do workflow */
    workflowData?: Record<string, any>;
}

/**
 * Resposta da API de movimento
 */
export interface MoveCardResponse {
    success: boolean;
    message?: string;
    data?: {
        card_id: string;
        new_step: number;
        from_column: string;
        to_column: string;
        crud_type: string;
        updated_at: string;
    };
    errors?: Record<string, string[]>;
}

// ========================================
// 🎯 TIPOS PARA CRUD GENÉRICO
// ========================================

/** Tipos de CRUD suportados */
export type CrudType = 'tickets' | 'sales' | 'orders' | 'pipeline' | 'generic';

/** Mapeamento de colunas por tipo de CRUD */
export interface CrudColumnMapping {
    [crudType: string]: {
        [columnId: string]: number;
    };
}

/** Mapeamento de steps por tipo de CRUD */
export interface CrudStepMapping {
    [crudType: string]: {
        [step: number]: string;
    };
}

/** Configuração genérica de workflow */
export interface WorkflowConfig {
    crudType: CrudType;
    columnMapping: Record<string, number>;
    stepMapping: Record<number, string>;
    apiEndpoint: string;
    validTransitions?: Record<string, string[]>;
} 
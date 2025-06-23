// Componentes principais do Kanban (refatorados)
export { default as KanbanBoard } from './components/KanbanBoard';
export { default as KanbanColumn } from './components/KanbanColumn';
export { default as KanbanCard } from './components/KanbanCard';

// Renderers do Kanban
export * from './renderers';

// Hooks do Kanban
export { useKanbanData } from './hooks/useKanbanData';

// Tipos e interfaces
export * from './types';


// Principais exports para uso externo
export { default as KanbanRenderer } from './renderers/KanbanRenderer';
export { resolveKanbanRenderer } from './renderers';
export type { KanbanRendererType } from './renderers';

// Tipos e interfaces (para futuras expansões)
export type KanbanColumnConfig = {
    id: string;
    title: string;
    color?: string;
    filter: (item: any) => boolean;
    icon?: string;
    maxItems?: number;
};

export type KanbanConfig = {
    dragAndDrop?: boolean;
    searchable?: boolean;
    filterable?: boolean;
    height?: string;
    columnsPerRow?: number;
};

export type KanbanBoardProps = {
    parentData: any[];
    columns: KanbanColumnConfig[];
    nestedTableClass: string;
    config?: KanbanConfig;
    onDataUpdate?: () => void;
}; 
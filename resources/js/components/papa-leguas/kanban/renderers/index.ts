// Renderers principais do Kanban
export { default as KanbanRenderer } from './KanbanRenderer';
export { default as CardRenderer } from './CardRenderer';
export { default as CompactCardRenderer } from './CompactCardRenderer';

// Mapeamento de renderers por tipo (similar às tabelas)
export const KanbanRenderers = {
    'default': CardRenderer,
    'card': CardRenderer,
    'compact': CompactCardRenderer,
    'kanban': KanbanRenderer
};

// Função para resolver renderer (similar às tabelas)
export function resolveKanbanRenderer(type: string = 'default') {
    return KanbanRenderers[type as keyof typeof KanbanRenderers] || KanbanRenderers.default;
}

// Tipos de renderers disponíveis
export type KanbanRendererType = keyof typeof KanbanRenderers; 
import React from 'react'; 
import TableRenderer from './renderes/table-renderer';
import CardRenderer from './renderes/card-renderer';
import KanbanRenderer from './renderes/kanban-renderer';

// O tipo 'any' aqui é uma solução pragmática para a dependência circular.
// O tipo real é React.FC<RendererProps>, mas EditableCell tem um tipo diferente.
const viewsRenderers: { [key: string]: React.FC<any> } = {
    // Renderers de texto
    list: TableRenderer,
    cards: CardRenderer,
    kanban: KanbanRenderer,
    default: TableRenderer
};

// Armazenamento mutável para permitir a injeção de novos renderers
const mutableViewsRenderers = { ...viewsRenderers };

export function getViewsRenderers() {
    return mutableViewsRenderers;
}

export function addViewsRenderer(type: string, renderer: React.FC<any>): void {
    mutableViewsRenderers[type] = renderer;
}

export function removeViewsRenderer(type: string): void {
    delete mutableViewsRenderers[type];
}

export function hasViewsRenderer(type: string): boolean {
    return type in mutableViewsRenderers;
} 
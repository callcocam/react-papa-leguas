import React from 'react';
import { RendererProps } from '../../types';
import TextRenderer from './TextRenderer';
import BadgeRenderer from './BadgeRenderer';
import EmailRenderer from './EmailRenderer';
import CompoundRenderer from './CompoundRenderer'; 
import EditableSelectRender from './EditableSelectRender'; 
import EditableTextRenderer from './EditableTextRenderer';
import NestedTableRenderer from './NestedTableRenderer';

// O tipo 'any' aqui é uma solução pragmática para a dependência circular.
// O tipo real é React.FC<RendererProps>, mas EditableCell tem um tipo diferente.
const baseRenderers: { [key: string]: React.FC<any> } = {
    // Renderers de texto
    text: TextRenderer,
    textRenderer: TextRenderer,

    // Renderers de data
    date: TextRenderer,
    dateRenderer: TextRenderer,

    // Renderers de número
    number: TextRenderer,
    numberRenderer: TextRenderer,

    // Renderers de boolean
    boolean: TextRenderer,
    booleanRenderer: TextRenderer,
    
    // Renderers de badge/status
    badge: BadgeRenderer,
    badgeRenderer: BadgeRenderer,
    status: BadgeRenderer,

    // Renderers de select
    'editable-select': EditableSelectRender,
    editableSelect: EditableSelectRender,
    
    // Renderers de email
    email: EmailRenderer,
    emailRenderer: EmailRenderer,
    
    // Renderer composto
    compound: CompoundRenderer,
    compoundRenderer: CompoundRenderer,

    // Renderer para edição inline
    'editable-text': EditableTextRenderer,
    editableText: EditableTextRenderer, 
    editable: EditableTextRenderer,
    
    // Renderer para tabelas aninhadas
    'nested-table': NestedTableRenderer,
    nestedTable: NestedTableRenderer,
    nested: NestedTableRenderer,
    
    // Renderer padrão
    default: TextRenderer,
    defaultRenderer: TextRenderer,
};

// Armazenamento mutável para permitir a injeção de novos renderers
const mutableRenderers = { ...baseRenderers };

export function getColumnRenderers() {
    return mutableRenderers;
}

export function addColumnRenderer(type: string, renderer: React.FC<RendererProps>): void {
    mutableRenderers[type] = renderer;
}

export function removeColumnRenderer(type: string): void {
    delete mutableRenderers[type];
}

export function hasColumnRenderer(type: string): boolean {
    return type in mutableRenderers;
} 
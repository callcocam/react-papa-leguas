import React from 'react';
import { RendererProps } from '../../../types'; 

// O tipo 'any' aqui é uma solução pragmática para a dependência circular.
// O tipo real é React.FC<RendererProps>, mas EditableCell tem um tipo diferente.
const baseRenderers: { [key: string]: React.FC<any> } = {
    // Renderers de texto
    // text: TextRenderer,
    // textRenderer: TextRenderer,
 
     
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
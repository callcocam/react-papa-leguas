import React from 'react'; 
import { type RendererProps, type TableColumn } from '../types';
import TextRenderer from './renderers/TextRenderer';
import BadgeRenderer from './renderers/BadgeRenderer';
import EmailRenderer from './renderers/EmailRenderer';
import CompoundRenderer from './CompoundRenderer';
import EditableCell from './EditableCell';

// Mapeamento de tipos de renderização para componentes
const renderers: { [key: string]: React.FC<any> } = {
    // Renderers de texto
    text: TextRenderer,
    textRenderer: TextRenderer,
    
    // Renderers de badge/status
    badge: BadgeRenderer,
    badgeRenderer: BadgeRenderer,
    status: BadgeRenderer,
    
    // Renderers de email
    email: EmailRenderer,
    emailRenderer: EmailRenderer,
    
    // Renderer composto
    compound: CompoundRenderer,
    compoundRenderer: CompoundRenderer,

    // Renderer para edição inline
    'editable-text': EditableCell,

    // Renderer padrão
    default: TextRenderer,
    defaultRenderer: TextRenderer,
};

/**
 * Adiciona ou substitui um renderer de coluna
 * Permite injeção de novos renderers em runtime
 */
export function addColumnRenderer(type: string, renderer: React.FC<RendererProps>): void {
    renderers[type] = renderer;
}

/**
 * Remove um renderer de coluna
 */
export function removeColumnRenderer(type: string): void {
    delete renderers[type];
}

/**
 * Obtém todos os renderers disponíveis
 */
export function getColumnRenderers(): { [key: string]: React.FC<RendererProps> } {
    return { ...renderers };
}

/**
 * Verifica se um renderer existe
 */
export function hasColumnRenderer(type: string): boolean {
    return type in renderers;
}

export interface ColumnRendererProps {
    column: TableColumn;
    item: any;
    value: any;
}

export default function ColumnRenderer({ column, item, value }: ColumnRendererProps) {
    // Verificação de segurança
    if (!column || typeof column !== 'object') {
        console.warn('⚠️ Coluna inválida:', column);
        return null;
    } 

    // Prioridade 1: Usar `renderAs` se definido
    // Prioridade 2: Usar `type` como fallback
    // Prioridade 3: Usar 'text' como padrão
    let type = column.renderAs || column.type; 

    if(!type){
        console.error('❌ ERRO: Renderer NÃO encontrado! Usando renderer padrão.');
        console.log('Renderers disponíveis:', Object.keys(renderers));
        type = 'text';
    }
    
    // Seleciona o renderer apropriado
    const Renderer = renderers[type] || renderers.default;
      

    return <Renderer value={value} item={item} column={column} />;
} 
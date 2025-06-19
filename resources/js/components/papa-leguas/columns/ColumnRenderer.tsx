import React from 'react'; 
import { type RendererProps, type TableColumn } from '../types';
import TextRenderer from './renderers/TextRenderer';
import BadgeRenderer from './renderers/BadgeRenderer';
import EmailRenderer from './renderers/EmailRenderer';

// Mapeamento de tipos de renderização para componentes
const renderers: { [key: string]: React.FC<RendererProps> } = {
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

interface ColumnRendererProps {
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

    // Define 'text' como o tipo padrão se nenhum for especificado
    const type = column.renderAs || 'text';

    // Seleciona o renderer apropriado, ou o texto como fallback
    const Renderer = renderers[type] || renderers.default;

    return <Renderer value={value} item={item} column={column} />;
} 
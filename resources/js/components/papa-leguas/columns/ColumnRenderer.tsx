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

interface ColumnRendererProps {
    column: TableColumn;
    item: any;
    value: any;
}

export default function ColumnRenderer({ column, item, value }: ColumnRendererProps) {
    // Define 'text' como o tipo padrão se nenhum for especificado
    const type = column.renderAs || 'text';

    // Seleciona o renderer apropriado, ou o texto como fallback
    const Renderer = renderers[type] || renderers.default;

    return <Renderer value={value} item={item} column={column} />;
} 
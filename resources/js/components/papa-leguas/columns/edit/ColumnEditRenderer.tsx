import React from 'react';
import { type RendererProps } from '../../types';

// Importa os renderers de exibição específicos para quebrar a dependência circular
import TextRenderer from '../renderers/TextRenderer';
import BadgeRenderer from '../renderers/BadgeRenderer';
import CompoundRenderer from '../renderers/CompoundRenderer';
import EmailRenderer from '../renderers/EmailRenderer';

/**
 * Renderizador de exibição para células editáveis.
 * Este componente atua como um despachante para renderizadores de *exibição*,
 * mas ele intencionalmente **NÃO** conhece os renderizadores de *edição*
 * (`EditableTextRenderer`, etc.) para evitar dependências circulares.
 */
export default function ColumnEditRenderer({ value, item, column }: RendererProps) {
    // Determina o tipo de renderização, com 'text' como padrão.
    const renderType = column.renderAs || column.type || 'text';

    // Despacha para o componente de renderização apropriado.
    switch (renderType) {
        case 'badge':
            return <BadgeRenderer value={value} item={item} column={column} />;
        case 'compound':
            return <CompoundRenderer value={value} item={item} column={column} />;
        case 'email':
            return <EmailRenderer value={value} item={item} column={column} />;
        
        // Renderizadores editáveis são excluídos aqui para evitar o ciclo.
        case 'editable-text':
        case 'editable-select':
        case 'text':
        default:
            return <TextRenderer value={value} item={item} column={column} />;
    }
} 
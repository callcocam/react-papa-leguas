import React from 'react';
import { type ActionRendererProps, type TableAction } from '../types';
import ButtonActionRenderer from './renderers/ButtonActionRenderer';
import LinkActionRenderer from './renderers/LinkActionRenderer';
import DropdownActionRenderer from './renderers/DropdownActionRenderer';
import CallbackActionRenderer from './renderers/CallbackActionRenderer';
import ModalActionRenderer from './renderers/ModalActionRenderer';
import { icons } from 'lucide-react';

const iconMap: { [key: string]: React.ElementType } = icons;

// Mapeamento de tipos de ação para componentes
const renderers: { [key: string]: React.FC<ActionRendererProps> } = {
    // Renderers de botão
    button: ButtonActionRenderer,
    buttonActionRenderer: ButtonActionRenderer,
    
    // Renderers de link
    link: LinkActionRenderer,
    linkActionRenderer: LinkActionRenderer,
    
    // Renderers de dropdown
    dropdown: DropdownActionRenderer,
    dropdownActionRenderer: DropdownActionRenderer,
    
    // Renderers de callback
    callback: CallbackActionRenderer,
    callbackActionRenderer: CallbackActionRenderer,
    custom: CallbackActionRenderer,
    
    // Renderer de Modal
    modal: ModalActionRenderer,
    
    // Renderers para tipos específicos (compatibilidade)
    edit: ButtonActionRenderer,
    delete: ButtonActionRenderer,
    view: ButtonActionRenderer,
    primary: ButtonActionRenderer,
    secondary: ButtonActionRenderer,
    
    // Renderers para tipos do backend
    route: LinkActionRenderer,
    url: LinkActionRenderer,
    
    // Renderer padrão
    default: ButtonActionRenderer,
    defaultActionRenderer: ButtonActionRenderer,
};

/**
 * Adiciona ou substitui um renderer de ação
 * Permite injeção de novos renderers em runtime
 */
export function addActionRenderer(type: string, renderer: React.FC<ActionRendererProps>): void {
    renderers[type] = renderer;
}

/**
 * Remove um renderer de ação
 */
export function removeActionRenderer(type: string): void {
    delete renderers[type];
}

/**
 * Obtém todos os renderers disponíveis
 */
export function getActionRenderers(): { [key: string]: React.FC<ActionRendererProps> } {
    return { ...renderers };
}

/**
 * Verifica se um renderer existe
 */
export function hasActionRenderer(type: string): boolean {
    return type in renderers;
}

interface ActionRendererComponentProps {
    action: TableAction;
    item: any;
}

export default function ActionRenderer({ action, item }: ActionRendererComponentProps) {
    // Verificação de segurança
    if (!action || typeof action !== 'object') {
        console.warn('⚠️ Ação inválida:', action);
        return null;
    }

    // Define o tipo baseado em renderAs, type ou fallback para 'button'
    const type = (action as any).renderAs || action.type || 'button';

    // Seleciona o renderer apropriado, ou o padrão como fallback
    const Renderer = renderers[type] || renderers.default;
    const iconName = action.icon;
    const IconComponent = iconName ? iconMap[iconName as keyof typeof iconMap] : undefined; 
    return <Renderer action={action} item={item} IconComponent={IconComponent} />;
} 
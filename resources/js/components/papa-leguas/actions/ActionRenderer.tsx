import React from 'react';
import { type ActionRendererProps, type TableAction } from '../types';
import ButtonActionRenderer from './renderers/ButtonActionRenderer';
import LinkActionRenderer from './renderers/LinkActionRenderer';
import DropdownActionRenderer from './renderers/DropdownActionRenderer';
import CallbackActionRenderer from './renderers/CallbackActionRenderer';
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

/**
 * Hook para processar ações programaticamente
 */
export const useActionProcessor = () => {
    const executeAction = async (action: TableAction, item: any) => {
        try {
            // Se tem confirmação, solicitar confirmação primeiro
            if (action.confirmMessage) {
                const confirmed = window.confirm(action.confirmMessage);
                if (!confirmed) return;
            }

            // Executar onClick se fornecido
            if (action.onClick) {
                action.onClick(item);
                return;
            }

            // Processar baseado no tipo
            switch (action.type) {
                case 'custom':
                case 'callback':
                    await handleCallbackAction(action, item);
                    break;
                    
                default:
                    console.warn('Tipo de ação não suportado para execução automática:', action.type);
            }
        } catch (error) {
            console.error('Erro ao executar ação:', error);
        }
    };

    const handleCallbackAction = async (action: TableAction, item: any) => {
        // Fazer requisição para executar callback no backend
        const response = await fetch(`/api/actions/${action.key}/execute`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                item_id: item.id,
            }),
        });

        const result = await response.json();
        
        if (result.success) {
            console.log('Ação executada com sucesso:', result.message);
            if (result.reload !== false) {
                window.location.reload();
            }
        } else {
            console.error('Erro na execução da ação:', result.message);
            alert(result.message || 'Erro ao executar ação');
        }
    };

    return {
        executeAction,
    };
}; 
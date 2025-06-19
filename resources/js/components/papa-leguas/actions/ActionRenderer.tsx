import React from 'react';
import { type ActionRendererProps } from '../types';

// Importar os renderers específicos
import ButtonActionRenderer from './renderers/ButtonActionRenderer';
import LinkActionRenderer from './renderers/LinkActionRenderer';
import DropdownActionRenderer from './renderers/DropdownActionRenderer';

/**
 * Factory de Renderers de Ações
 * Seleciona automaticamente o renderer correto baseado no tipo da ação
 */
export default function ActionRenderer(props: ActionRendererProps) {
    const { action } = props;
    
    // Verificação de segurança
    if (!action || typeof action !== 'object') {
        console.warn('⚠️ Ação inválida:', action);
        return null;
    }
    
    try {
        // Verificar se é dropdown (tem sub-ações)
        if ((action as any).actions && Array.isArray((action as any).actions)) {
            return <DropdownActionRenderer {...props} />;
        }
        
        // Verificar por renderAs específico
        if ((action as any).renderAs) {
            switch ((action as any).renderAs) {
                case 'link':
                    return <LinkActionRenderer {...props} />;
                case 'button':
                    return <ButtonActionRenderer {...props} />;
                case 'dropdown':
                    return <DropdownActionRenderer {...props} />;
                default:
                    console.log(`🔄 RenderAs desconhecido: "${(action as any).renderAs}", usando ButtonActionRenderer como fallback`);
                    return <ButtonActionRenderer {...props} />;
            }
        }
        
        // Auto-detectar baseado no tipo
        switch (action.type) {
            case 'link':
                return <LinkActionRenderer {...props} />;
            case 'dropdown':
                return <DropdownActionRenderer {...props} />;
            case 'edit':
            case 'delete':
            case 'view':
            case 'primary':
            case 'secondary':
            case 'custom':
            default:
                // Fallback para botão
                return <ButtonActionRenderer {...props} />;
        }
    } catch (error) {
        console.error('❌ Erro ao renderizar ação:', error);
        // Fallback seguro
        return <ButtonActionRenderer {...props} />;
    }
} 
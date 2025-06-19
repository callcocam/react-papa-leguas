import React from 'react';
import { type FilterRendererProps } from '../types';

// Importar os renderers específicos
import TextFilterRenderer from './renderers/TextFilterRenderer';
import SelectFilterRenderer from './renderers/SelectFilterRenderer';
import BooleanFilterRenderer from './renderers/BooleanFilterRenderer';

/**
 * Factory de Renderers de Filtros
 * Seleciona automaticamente o renderer correto baseado no tipo do filtro
 */
export default function FilterRenderer(props: FilterRendererProps) {
    const { filter } = props;
    
    // Verificação de segurança
    if (!filter || typeof filter !== 'object') {
        console.warn('⚠️ Filtro inválido:', filter);
        return null;
    }
    
    try {
        switch (filter.type) {
            case 'text':
            case 'search':
                return <TextFilterRenderer {...props} />;
                
            case 'select':
                return <SelectFilterRenderer {...props} />;
                
            case 'boolean':
                return <BooleanFilterRenderer {...props} />;
                
            default:
                // Fallback para filtro de texto
                console.log(`🔄 Tipo de filtro desconhecido: "${filter.type}", usando TextFilterRenderer como fallback`);
                return <TextFilterRenderer {...props} />;
        }
    } catch (error) {
        console.error('❌ Erro ao renderizar filtro:', error);
        // Fallback seguro
        return <TextFilterRenderer {...props} />;
    }
} 
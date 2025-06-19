import React from 'react';
import { type FilterRendererProps } from '../types'; 

// Importar os renderers específicos
import TextFilterRenderer from './renderers/TextFilterRenderer';
import SelectFilterRenderer from './renderers/SelectFilterRenderer';
import BooleanFilterRenderer from './renderers/BooleanFilterRenderer';
import DateFilterRenderer from './renderers/DateFilterRenderer';
import NumberFilterRenderer from './renderers/NumberFilterRenderer';

// Mapeamento de tipos de filtros para componentes
const renderers: { [key: string]: React.FC<FilterRendererProps> } = {
    // Renderers de texto
    text: TextFilterRenderer,
    search: TextFilterRenderer,
    textFilterRenderer: TextFilterRenderer,
    
    // Renderers de select
    select: SelectFilterRenderer,
    selectFilterRenderer: SelectFilterRenderer,
    
    // Renderers de boolean
    boolean: BooleanFilterRenderer,
    booleanFilterRenderer: BooleanFilterRenderer,
    
    // Renderers de data
    date: DateFilterRenderer,
    date_range: DateFilterRenderer,
    dateFilterRenderer: DateFilterRenderer,
    
    // Renderers de número
    number: NumberFilterRenderer,
    number_range: NumberFilterRenderer,
    numberFilterRenderer: NumberFilterRenderer,
    
    // Renderer padrão
    default: TextFilterRenderer,
    defaultFilterRenderer: TextFilterRenderer,
    };

/**
 * Adiciona ou substitui um renderer de filtro
 * Permite injeção de novos renderers em runtime
 */
export function addFilterRenderer(type: string, renderer: React.FC<FilterRendererProps>): void {
    renderers[type] = renderer;
}

/**
 * Remove um renderer de filtro
 */
export function removeFilterRenderer(type: string): void {
    delete renderers[type];
}

/**
 * Obtém todos os renderers disponíveis
 */
export function getFilterRenderers(): { [key: string]: React.FC<FilterRendererProps> } {
    return { ...renderers };
}

/**
 * Verifica se um renderer existe
 */
export function hasFilterRenderer(type: string): boolean {
    return type in renderers;
}

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
        // Define 'text' como o tipo padrão se nenhum for especificado
        const type = filter.type || 'text';
        
        // Seleciona o renderer apropriado, ou o texto como fallback
        const Renderer = renderers[type] || renderers.default;
        
        return <Renderer {...props} />;
    } catch (error) {
        console.error('❌ Erro ao renderizar filtro:', error);
        // Fallback seguro
        const FallbackRenderer = renderers.default;
        return <FallbackRenderer {...props} />;
    }
} 
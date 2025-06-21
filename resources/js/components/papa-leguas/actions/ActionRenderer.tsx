import React from 'react';
import { type ActionRendererProps } from '../types';
import ButtonActionRenderer from './renderers/ButtonActionRenderer';
import CallbackActionRenderer from './renderers/CallbackActionRenderer';
import BulkActionRenderer from './renderers/BulkActionRenderer';
import TableActionRenderer from './renderers/TableActionRenderer';
import { Icons } from '../icons';
import { type LucideIcon } from 'lucide-react';

// Mapeamento de tipos de ação para seus componentes de renderização
const rendererMap = {
    'button': ButtonActionRenderer,
    'route': ButtonActionRenderer,
    'url': ButtonActionRenderer,
    'edit': ButtonActionRenderer,
    'delete': ButtonActionRenderer,
    'view': ButtonActionRenderer,
    'callback': TableActionRenderer, // Ações normais da tabela
    'editable': CallbackActionRenderer, // Ações de colunas editáveis
    'bulk': BulkActionRenderer,
    // Adicione outros tipos conforme necessário
};

/**
 * ActionRenderer atua como um despachante (dispatcher).
 * Ele seleciona o componente de renderização correto com base no tipo da ação.
 * 
 * TIPOS DE AÇÃO:
 * - 'callback': Ações normais da tabela (TableActionRenderer)
 * - 'editable': Ações de colunas editáveis (CallbackActionRenderer)
 * - 'button', 'route', 'url', 'edit', 'delete', 'view': Ações de botão (ButtonActionRenderer)
 * - 'bulk': Ações em lote (BulkActionRenderer)
 */
export default function ActionRenderer(props: ActionRendererProps) {
    const { action } = props;

    // Determina o tipo de renderização. Usa 'button' como padrão se o tipo não for especificado.
    const renderType = action.type || 'button';

    // Seleciona o componente de renderização do mapa.
    // Usa ButtonActionRenderer como fallback se o tipo não for encontrado no mapa.
    const RendererComponent = rendererMap[renderType as keyof typeof rendererMap] || ButtonActionRenderer;
    
    // Resolve o nome do ícone (string) para um componente React
    const IconComponent = action.icon && typeof action.icon === 'string'
        ? (Icons[action.icon as keyof typeof Icons] as LucideIcon)
        : undefined;

    // Renderiza o componente selecionado, passando todas as props, incluindo o IconComponent resolvido.
    return <RendererComponent {...props} IconComponent={IconComponent} />;
}

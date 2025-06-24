import React from 'react';
import { ViewConfig } from '../../types';

// 🚀 USANDO PAPA LEGUAS KANBAN DIRETAMENTE
import { KanbanBoard } from '../papa-leguas/kanban';

// Tipo mais flexível para configuração Kanban
interface KanbanConfig extends Record<string, any> {
    searchable?: boolean;
    filterable?: boolean;
    height?: string;
    columnsPerRow?: number;
    dragAndDrop?: boolean;
    workflowBased?: boolean;
    groupBy?: string;
    title?: string;
    description?: string;
    columns?: any[];
    tableColumns?: any[];
}

interface KanbanViewProps {
    data: any[];
    columns: any[];
    config: KanbanConfig;
    actions?: any;
    className?: string;
}

/**
 * 🚀 PAPA LEGUAS KANBAN - CONFIA NO BACKEND
 * 
 * SE CHEGOU AQUI, BACKEND JÁ VALIDOU TUDO
 * 
 * REGRAS:
 * ✅ Backend só envia view=kanban se tudo estiver configurado
 * ✅ Frontend não faz validações, só renderiza
 * ✅ Papa Leguas Kanban direto
 * ✅ Debug para acompanhar dados recebidos
 */
export default function KanbanView({
    data = [],
    columns = [],
    config = {},
    actions = {},
    className = ''
}: KanbanViewProps) {

    console.log(data);

    // 🔧 Preparar colunas Kanban - Backend já validou
    const kanbanColumns = React.useMemo(() => {
        console.log('✅ Colunas do backend:', config.columns);
        return config.columns || [];
    }, [config.columns]);

    // ⚙️ Configuração do Papa Leguas Kanban
    const kanbanConfig = {
        searchable: config.searchable ?? true,
        filterable: config.filterable ?? true,
        height: config.height || '700px',
        columnsPerRow: config.columnsPerRow || 4,
        dragAndDrop: config.dragAndDrop || false,
        ...config
    };

    // 📋 Meta informações
    const meta = {
        title: config.title || 'Quadro Kanban',
        description: config.description || `Visualização em Kanban com ${data.length} itens`,
        key: 'papa-leguas-kanban'
    };

    return (
        <div className={className}>
            <KanbanBoard
                data={data}
                columns={kanbanColumns}
                tableColumns={columns}
                actions={actions}
                config={kanbanConfig}
                meta={meta}
                onAction={(actionId, item, extra) => {
                    console.log('🎯 Papa Leguas Kanban - Ação:', actionId, item, extra);
                    // TODO: Implementar handler de ações
                }}
                onFilter={(filters) => {
                    console.log('🔍 Papa Leguas Kanban - Filtros:', filters);
                    // TODO: Implementar handler de filtros
                }}
                onRefresh={() => {
                    console.log('🔄 Papa Leguas Kanban - Refresh');
                    // TODO: Implementar refresh
                }}
            />
        </div>
    );
}

// ⚠️ FUNÇÃO REMOVIDA: generateFallbackKanbanColumns
// Frontend confia que backend já validou tudo 
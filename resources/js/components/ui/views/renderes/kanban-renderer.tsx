import React from 'react';
import { KanbanColumn, KanbanRendererProps } from '../../../papa-leguas/kanban/types';
import { KanbanBoard } from '../../../papa-leguas/kanban';
import { router } from '@inertiajs/react';
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
    viewConfig?: any;
    meta?: any;
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
export default function KanbanRenderer({
    data = [],
    columns = [],
    config = {},
    actions = [], 
    meta
}: KanbanRendererProps) {

    // Usar configurações que vêm do backend nas views
    const kanbanColumns: KanbanColumn[] = Array.isArray(config?.columns)
        ? config.columns
        : [];
 
    return (
        <KanbanBoard
            data={data}
            columns={kanbanColumns}
            tableColumns={columns}
            actions={actions}
            config={config || {}}
            meta={meta}
            onAction={(actionId, item, extra) => {
                console.log('🎯 Kanban Action:', { actionId, item, extra });
                // TODO: Implementar ações do Kanban
            }}
            onRefresh={() => {
                console.log('🔄 Refreshing Kanban');
                router.reload(); 
            }}
        />
    );
} 
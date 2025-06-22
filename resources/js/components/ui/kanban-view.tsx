import React from 'react';
import { ViewConfig } from '../../types';
import KanbanBoard from './kanban/kanban-board';

interface KanbanViewProps {
    data: any[];
    columns: any[];
    config: ViewConfig['config'];
    actions?: any;
    className?: string;
}

export default function KanbanView({
    data = [],
    columns = [],
    config = {},
    actions = {},
    className = ''
}: KanbanViewProps) {
    // Transformar configuração das views em configuração do KanbanBoard
    const kanbanConfig = {
        searchable: true,
        filterable: true,
        height: '700px',
        columnsPerRow: 4,
        dragAndDrop: false,
        ...config
    };

    // Transformar colunas das views em colunas do Kanban
    const kanbanColumns = Array.isArray(config.columns) ? config.columns : [];

    // Meta informações
    const meta = {
        title: 'Quadro Kanban',
        description: `Visualização em Kanban com ${data.length} itens`
    };

    return (
        <div className={className}>
            <KanbanBoard
                data={data}
                columns={columns}
                config={kanbanConfig}
                actions={actions}
                meta={meta}
                onAction={(actionId, item, extra) => {
                    console.log('Ação Kanban:', actionId, item, extra);
                    // TODO: Implementar handler de ações
                }}
                onFilter={(filters) => {
                    console.log('Filtros Kanban:', filters);
                    // TODO: Implementar handler de filtros
                }}
                onRefresh={() => {
                    console.log('Refresh Kanban');
                    // TODO: Implementar refresh
                }}
            />
        </div>
    );
} 
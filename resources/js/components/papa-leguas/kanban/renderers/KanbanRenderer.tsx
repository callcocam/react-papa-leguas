import React from 'react';
import KanbanBoard from '../components/KanbanBoard';
import type { KanbanRendererProps } from '../types';

/**
 * Renderer principal do Kanban que segue o padrão dos renderers de tabela.
 * 
 * Funcionalidades:
 * - Recebe dados via props (eager loading)
 * - Usa columns, actions e filters do backend
 * - Integra com sistema de renderers existente
 * - Suporte a configurações dinâmicas
 */
export default function KanbanRenderer({
    data,
    columns,
    actions = [],
    filters = [],
    config = {},
    meta = {},
    onAction,
    onFilter,
    onRefresh
}: KanbanRendererProps) {
    // Validações básicas
    if (!data || !Array.isArray(data)) {
        return (
            <div className="kanban-error p-8 text-center">
                <p className="text-gray-500">Dados não fornecidos para o Kanban</p>
            </div>
        );
    }

    if (!columns || !Array.isArray(columns)) {
        return (
            <div className="kanban-error p-8 text-center">
                <p className="text-gray-500">Configuração de colunas não fornecida</p>
            </div>
        );
    }

    // Configurações padrão do Kanban
    const kanbanConfig = {
        searchable: config.searchable ?? true,
        filterable: config.filterable ?? true,
        height: config.height ?? '700px',
        columnsPerRow: config.columnsPerRow ?? 4,
        dragAndDrop: config.dragAndDrop ?? false,
        ...config
    };

    return (
        <div className="kanban-renderer">
            <KanbanBoard
                data={data}
                columns={columns}
                actions={actions}
                filters={filters}
                config={kanbanConfig}
                meta={meta}
                onAction={onAction}
                onFilter={onFilter}
                onRefresh={onRefresh}
            />
        </div>
    );
} 
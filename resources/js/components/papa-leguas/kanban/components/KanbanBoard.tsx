import React, { useState, useMemo } from 'react';
import {
    DndContext,
    DragOverlay,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    useSensor,
    useSensors,
} from '@dnd-kit/core';
import { sortableKeyboardCoordinates } from '@dnd-kit/sortable';
import KanbanColumn from './KanbanColumn';
import KanbanCard from './KanbanCard';
import { useDragDrop } from '../hooks/useDragDrop';
import axios from 'axios';
import type { KanbanBoardProps, DragDropConfig } from '../types';

/**
 * Componente principal do Kanban Board
 * 
 * Sistema simplificado que usa diretamente as configurações vindas do backend.
 * Suporta drag & drop e integração com APIs baseado nas props recebidas.
 */
const KanbanBoard: React.FC<KanbanBoardProps> = ({
    data,
    columns,
    tableColumns = [],
    actions = [],
    config = {},
    meta = {},
    onAction,
    onRefresh
}) => {
    // Estados locais 
    const [localData, setLocalData] = useState(data);

    // Configurações padrão vindas das props
    const {
        height = '700px',
        dragAndDrop = false, // Desabilitado por padrão até implementar no backend
        apiEndpoint = '/api/admin/kanban/move-card',
        workflowSlug,
        validateTransition,
        onMoveCard
    } = config;

    // 🎯 Função simples para mover card (se drag & drop estiver habilitado)
    const handleMoveCard = async (cardId: string, fromColumnId: string, toColumnId: string, item: any): Promise<boolean> => {
        try {
            console.log('🚀 Movendo card:', { cardId, fromColumnId, toColumnId });

            const response = await axios.post(apiEndpoint, {
                card_id: cardId,
                from_column_id: fromColumnId,
                to_column_id: toColumnId,
                workflow_slug: workflowSlug,
                item: item,
            }, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                }
            });

            const result = response.data;

            if (result.success) {
                console.log('✅ Card movido com sucesso:', result.data);
                
                // Atualizar dados locais se necessário
                if (onRefresh) {
                    onRefresh();
                }
                
                return true;
            } else {
                throw new Error(result.message || 'Erro ao mover card');
            }

        } catch (error: any) {
            console.error('❌ Erro ao mover card:', error);
            const errorMessage = error.response?.data?.message || error.message || 'Erro ao mover card';
            alert(`Erro: ${errorMessage}`);
            return false;
        }
    };

    // 🎯 Filtrar dados por coluna usando os filtros definidos no backend
    const filteredDataByColumn = useMemo(() => {
        console.log('🎯 Kanban: Filtrando', localData.length, 'items em', columns.length, 'colunas');

        return columns.reduce((acc, column) => {
            let filtered = [];

            // Usar o filtro da coluna se ele existir (vem do backend)
            if (column.filter && typeof column.filter === 'function') {
                filtered = localData.filter(column.filter);
                console.log(`📊 "${column.title}": ${filtered.length} items`);
            } else {
                console.log(`⚠️ "${column.title}": sem filtro definido`);
                filtered = [];
            }

            acc[column.id] = filtered;
            return acc;
        }, {} as Record<string, any[]>);
    }, [localData, columns]);

    // 🎯 Estatísticas simples por coluna
    const stats = useMemo(() => {
        return columns.reduce((acc, column) => {
            const columnData = filteredDataByColumn[column.id] || [];
            acc[column.id] = {
                total: columnData.length,
                percentage: localData.length > 0 ? Math.round((columnData.length / localData.length) * 100) : 0
            };
            return acc;
        }, {} as Record<string, { total: number; percentage: number }>);
    }, [filteredDataByColumn, columns, localData.length]);

    // 🎯 Configuração do Drag & Drop (apenas se habilitado)
    const dragConfig: DragDropConfig = {
        enabled: dragAndDrop,
        validateTransition: validateTransition || (async () => true),
        onMoveCard: onMoveCard || handleMoveCard,
        workflowSlug: workflowSlug || 'generic',
        apiEndpoint: apiEndpoint,
    };

    // 🎯 Hook de Drag & Drop
    const {
        activeId,
        isDragging,
        draggedItem,
        handleDragStart,
        handleDragEnd,
        handleDragOver
    } = useDragDrop(dragConfig);

    // Sensores para drag & drop
    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
            },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    // Atualizar dados locais quando dados externos mudam
    React.useEffect(() => {
        setLocalData(data);
    }, [data]);

    return (
        <div className="kanban-board h-full">
            <DndContext
                sensors={sensors}
                onDragStart={handleDragStart}
                onDragEnd={handleDragEnd}
                onDragOver={handleDragOver}
            >
                {/* Layout horizontal com rolagem */}
                <div
                    className="flex gap-4 overflow-x-auto pb-4 h-full"
                    style={{ height: height }}
                >
                    {columns.map((column) => {
                        const columnData = filteredDataByColumn[column.id] || [];

                        return (
                            <KanbanColumn
                                key={column.id}
                                column={column}
                                data={columnData}
                                tableColumns={tableColumns}
                                actions={actions}
                                onAction={onAction}
                                dragAndDrop={dragAndDrop}
                                isDragActive={isDragging}
                            />
                        );
                    })}
                </div>

                {/* Drag Overlay - Card sendo arrastado */}
                <DragOverlay>
                    {activeId && draggedItem ? (
                        <KanbanCard
                            item={draggedItem}
                            tableColumns={tableColumns}
                            actions={actions}
                            onAction={onAction}
                            draggable={true}
                            isDragging={true}
                            dragOverlay={true}
                        />
                    ) : null}
                </DragOverlay>
            </DndContext>
        </div>
    );
};

export default KanbanBoard; 
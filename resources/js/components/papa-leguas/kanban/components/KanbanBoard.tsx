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


axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

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

    // 🎯 Filtrar dados por coluna usando configurações do backend
    const filteredDataByColumn = useMemo(() => {

        return columns.reduce((acc, column) => {
            const filtered = localData.filter(function (item: any) {
                // Verificar se o item tem currentWorkflow
                if (!item.currentWorkflow) {
                    console.log('⚠️ Item sem currentWorkflow:', item.id);
                    return false;
                }
                // Método 1: Comparar currentTemplate.slug com column.slug (PRINCIPAL)
                const currentTemplateSlug = item.currentWorkflow.currentTemplate?.slug;
                if (currentTemplateSlug === column.slug) {
                    console.log('✅ Match por template slug:', item.id, 'slug:', currentTemplateSlug, 'coluna:', column.slug);
                    return true;
                }

                // Método 2: Fallback - Comparar current_template_id com column.id (se slug não existir)
                const currentTemplateId = item.currentWorkflow.current_template_id;
                if (currentTemplateId === column.id) {
                    return true;
                }

                // Método 3: Usar current_step se a coluna tiver sort_order
                const currentStep = item.currentWorkflow.current_step;
                if (column.sort_order && currentStep === column.sort_order) {
                    return true;
                }

                // Método 4: Fallback para status (se não houver workflow específico)
                if (item.status === column.id) {
                    return true;
                }

                return false;
            });
            acc[column.id] = filtered;
            return acc;
        }, {} as Record<string, any[]>);
    }, [localData, columns]);


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
        draggedFromColumnId,
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
                collisionDetection={closestCenter}
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
                    {activeId && draggedItem && draggedFromColumnId ? (
                        <KanbanCard
                            item={draggedItem}
                            column={columns.find(c => c.id === draggedFromColumnId) || columns[0]}
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
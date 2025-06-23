import React, { useState, useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input'; 
import { 
    Search,  
    RefreshCw,
    Grid3X3
} from 'lucide-react';
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
import type { KanbanBoardProps, DragDropConfig } from '../types';

/**
 * Componente Kanban Board com Drag & Drop implementado.
 * 
 * Funcionalidades:
 * - Dados via eager loading (não API)
 * - Columns, actions e filters do backend
 * - Drag & Drop entre colunas com @dnd-kit
 * - Validação de transições
 * - Integração com sistema existente
 */
export default function KanbanBoard({
    data,
    columns,
    tableColumns,
    actions = [],
    filters = [],
    config = {},
    meta = {},
    onAction,
    onFilter,
    onRefresh
}: KanbanBoardProps) {  

    // Estado local para os dados (para permitir movimentação imediata)
    const [localData, setLocalData] = useState(data);

    // Atualizar dados locais quando dados externos mudam
    React.useEffect(() => {
        setLocalData(data);
    }, [data]);

    // Configurações padrão
    const { 
        height = '700px', 
        dragAndDrop = true, // Ativado por padrão agora
        validateTransition,
        onMoveCard
    } = config;

    // 🎯 Função para mapear coluna ID para current_step
    const getStepFromColumnId = (columnId: string): number => {
        const stepMap: Record<string, number> = {
            'aberto': 1,
            'em-andamento': 2,
            'aguardando-cliente': 3,
            'resolvido': 4,
            'fechado': 5
        };
        return stepMap[columnId] || 1;
    };

    // 🎯 Configuração do Drag & Drop
    const dragConfig: DragDropConfig = {
        enabled: dragAndDrop,
        validateTransition: validateTransition || ((from, to, item) => {
            // Validação padrão: permitir qualquer transição
            console.log('🔍 Validating transition:', from, '→', to, 'for item:', item?.id);
            return true;
        }),
        onMoveCard: onMoveCard || (async (cardId, fromColumnId, toColumnId, item) => {
            console.log('🎯 Moving card:', { cardId, fromColumnId, toColumnId, item });
            
            // Atualizar dados localmente IMEDIATAMENTE para UX fluida
            const newStep = getStepFromColumnId(toColumnId);
            
            setLocalData(prevData => {
                return prevData.map(dataItem => {
                    if (dataItem.id === cardId) {
                        return {
                            ...dataItem,
                            currentWorkflow: {
                                ...dataItem.currentWorkflow,
                                current_step: newStep,
                                current_template_id: `step-${newStep}-${toColumnId}`
                            }
                        };
                    }
                    return dataItem;
                });
            });
            
            // Chamar API real do backend
            try {
                const response = await fetch('/admin/tickets/kanban/move-card', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        card_id: cardId,
                        from_column_id: fromColumnId,
                        to_column_id: toColumnId,
                        item: item
                    })
                });

                const result = await response.json();
                
                if (result.success) {
                    console.log('✅ Card moved successfully (backend confirmed):', result.data);
                    return true;
                } else {
                    console.error('❌ Backend rejected card movement:', result.message);
                    
                    // Reverter mudança local se backend falhou
                    setLocalData(prevData => {
                        return prevData.map(dataItem => {
                            if (dataItem.id === cardId) {
                                return {
                                    ...dataItem,
                                    currentWorkflow: {
                                        ...dataItem.currentWorkflow,
                                        current_step: getStepFromColumnId(fromColumnId), // Reverter
                                        current_template_id: `step-${getStepFromColumnId(fromColumnId)}-${fromColumnId}`
                                    }
                                };
                            }
                            return dataItem;
                        });
                    });
                    
                    return false;
                }
            } catch (error) {
                console.error('❌ Network error moving card:', error);
                
                // Reverter mudança local em caso de erro de rede
                setLocalData(prevData => {
                    return prevData.map(dataItem => {
                        if (dataItem.id === cardId) {
                            return {
                                ...dataItem,
                                currentWorkflow: {
                                    ...dataItem.currentWorkflow,
                                    current_step: getStepFromColumnId(fromColumnId), // Reverter
                                    current_template_id: `step-${getStepFromColumnId(fromColumnId)}-${fromColumnId}`
                                }
                            };
                        }
                        return dataItem;
                    });
                });
                
                return false;
            }
        })
    };

    // 🎯 Filtrar dados por coluna com base no workflow
    const filteredDataByColumn = useMemo(() => {
        return columns.reduce((acc, column) => {
            const filtered = localData.filter(item => {
                // Se tem workflow, filtrar por current_step
                if (item.currentWorkflow) {
                    const step = item.currentWorkflow.current_step;
                    
                    // Mapear steps para IDs de coluna
                    const stepToColumnMap: Record<number, string> = {
                        1: 'aberto',
                        2: 'em-andamento', 
                        3: 'aguardando-cliente',
                        4: 'resolvido',
                        5: 'fechado'
                    };
                    
                    const expectedColumnId = stepToColumnMap[step];
                    return expectedColumnId === column.id;
                }
                
                // Fallback: filtrar por status se não tem workflow
                return item.status === column.id;
            });
            
            acc[column.id] = filtered;
            return acc;
        }, {} as Record<string, any[]>);
    }, [localData, columns]);

    // 🚀 Estatísticas corrigidas - calcular por coluna com dados filtrados
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
                distance: 8, // Evita drag acidental
            },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    // Handler para ações
    const handleAction = (actionId: string, item: any, extra?: any) => {
        onAction?.(actionId, item, extra);
    };

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
} 
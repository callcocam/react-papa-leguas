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
 * Componente Kanban Board genérico com Drag & Drop implementado.
 * 
 * Funcionalidades:
 * - Dados via eager loading (não API)
 * - Columns, actions e filters do backend
 * - Drag & Drop entre colunas com @dnd-kit
 * - Validação de transições
 * - Suporte a qualquer tipo de CRUD (tickets, sales, orders, pipeline, etc.)
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
        onMoveCard,
        crudType = 'generic', // Tipo do CRUD (tickets, sales, orders, pipeline, etc.)
        apiEndpoint = '/admin/kanban/move-card' // Endpoint genérico
    } = config;

    // 🎯 Função para mapear coluna ID para current_step (genérica)
    const getStepFromColumnId = (columnId: string): number => {
        // Mapeamentos por tipo de CRUD
        const mappings: Record<string, Record<string, number>> = {
            tickets: {
                'aberto': 1,
                'em-andamento': 2,
                'aguardando-cliente': 3,
                'resolvido': 4,
                'fechado': 5
            },
            sales: {
                'lead': 1,
                'contato': 2,
                'proposta': 3,
                'negociacao': 4,
                'fechado': 5
            },
            orders: {
                'pedido': 1,
                'producao': 2,
                'qualidade': 3,
                'entrega': 4,
                'finalizado': 5
            },
            pipeline: {
                'inicio': 1,
                'desenvolvimento': 2,
                'teste': 3,
                'homologacao': 4,
                'producao': 5
            },
            generic: {
                'inicio': 1,
                'andamento': 2,
                'revisao': 3,
                'aprovado': 4,
                'finalizado': 5
            }
        };

        const mapping = mappings[crudType] || mappings.generic;
        return mapping[columnId] || 1;
    };

    // 🎯 Função para mapear current_step para coluna ID (genérica)
    const getColumnIdFromStep = (step: number): string => {
        // Mapeamentos inversos por tipo de CRUD
        const mappings: Record<string, Record<number, string>> = {
            tickets: {
                1: 'aberto',
                2: 'em-andamento',
                3: 'aguardando-cliente',
                4: 'resolvido',
                5: 'fechado'
            },
            sales: {
                1: 'lead',
                2: 'contato',
                3: 'proposta',
                4: 'negociacao',
                5: 'fechado'
            },
            orders: {
                1: 'pedido',
                2: 'producao',
                3: 'qualidade',
                4: 'entrega',
                5: 'finalizado'
            },
            pipeline: {
                1: 'inicio',
                2: 'desenvolvimento',
                3: 'teste',
                4: 'homologacao',
                5: 'producao'
            },
            generic: {
                1: 'inicio',
                2: 'andamento',
                3: 'revisao',
                4: 'aprovado',
                5: 'finalizado'
            }
        };

        const mapping = mappings[crudType] || mappings.generic;
        return mapping[step] || 'inicio';
    };

    // 🎯 Configuração do Drag & Drop
    const dragConfig: DragDropConfig = {
        enabled: dragAndDrop,
        validateTransition: validateTransition || ((from, to, item) => {
            // Validação padrão: permitir qualquer transição
            console.log('🔍 Validating transition:', from, '→', to, 'for item:', item?.id, 'crud_type:', crudType);
            return true;
        }),
        onMoveCard: onMoveCard || (async (cardId, fromColumnId, toColumnId, item) => {
            console.log('🎯 Moving card:', { cardId, fromColumnId, toColumnId, item, crudType });
            
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
            
            // Chamar API genérica do backend
            try {
                const response = await fetch(apiEndpoint, {
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
                        item: item,
                        crud_type: crudType,
                        workflow_data: {
                            // Dados adicionais do workflow se necessário
                            previous_step: getStepFromColumnId(fromColumnId),
                            new_step: newStep,
                        }
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

    // 🎯 Filtrar dados por coluna com base no workflow (genérico)
    const filteredDataByColumn = useMemo(() => {
        return columns.reduce((acc, column) => {
            const filtered = localData.filter(item => {
                // Se tem workflow, usar filtro específico da coluna
                if (item.currentWorkflow && column.filter) {
                    return column.filter(item);
                }
                
                // Se tem workflow mas não tem filtro específico, filtrar por template
                if (item.currentWorkflow) {
                    // Verificar se o template atual corresponde ao ID da coluna
                    const currentTemplate = item.currentWorkflow.currentTemplate;
                    if (currentTemplate) {
                        return currentTemplate.slug === column.id;
                    }
                    
                    // Fallback: verificar por current_template_id
                    return item.currentWorkflow.current_template_id === column.id;
                }
                
                // Fallback: filtrar por status tradicional se não tem workflow
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
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
import { useToast } from '../../../../hooks/use-toast';
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
    const [processingCards, setProcessingCards] = useState<Set<string>>(new Set());

    // Hook para toasts
    const { error, success } = useToast();

    // Configurações padrão vindas das props
    const {
        height = '700px',
        dragAndDrop = false, // Desabilitado por padrão até implementar no backend
        apiEndpoint = '/api/admin/kanban/move-card',
        workflowSlug,
        validateTransition,
        onMoveCard
    } = config;

    // 🎯 Função para mapear columnId para template slug
    const getTemplateSlugFromColumnId = (columnId: string): string | null => {
        const column = columns.find(col => col.id === columnId);
        return column?.slug || null;
    };

    // 🎯 Função para atualizar item localmente (atualização otimista)
    const updateItemLocally = (cardId: string, toColumnSlug: string) => {
        setLocalData(prevData => 
            prevData.map(dataItem => {
                if (dataItem.id === cardId && dataItem.currentWorkflow) {
                    return {
                        ...dataItem,
                        currentWorkflow: {
                            ...dataItem.currentWorkflow,
                            currentTemplate: {
                                ...dataItem.currentWorkflow.currentTemplate,
                                slug: toColumnSlug
                            }
                        }
                    };
                }
                return dataItem;
            })
        );
    };

    // 🎯 Função para reverter mudança local em caso de erro
    const revertItemLocally = (cardId: string, originalColumnSlug: string) => {
        setLocalData(prevData => 
            prevData.map(dataItem => {
                if (dataItem.id === cardId && dataItem.currentWorkflow) {
                    return {
                        ...dataItem,
                        currentWorkflow: {
                            ...dataItem.currentWorkflow,
                            currentTemplate: {
                                ...dataItem.currentWorkflow.currentTemplate,
                                slug: originalColumnSlug
                            }
                        }
                    };
                }
                return dataItem;
            })
        );
    };

    // 🎯 Função com atualização otimista para mover card
    const handleMoveCard = async (cardId: string, fromColumnId: string, toColumnId: string, item: any): Promise<boolean> => {
        const fromColumnSlug = getTemplateSlugFromColumnId(fromColumnId);
        const toColumnSlug = getTemplateSlugFromColumnId(toColumnId);

        if (!fromColumnSlug || !toColumnSlug) {
            console.error('❌ Não foi possível mapear colunas para slugs');
            return false;
        }

        console.log('🚀 Movendo card (otimista):', { cardId, fromColumnSlug, toColumnSlug });

        // 🎯 Marcar card como processando
        setProcessingCards(prev => new Set(prev).add(cardId));

        // 🎯 ATUALIZAÇÃO OTIMISTA: Mover card visualmente primeiro
        updateItemLocally(cardId, toColumnSlug);

        try {
            const response = await axios.post(apiEndpoint, {
                card_id: cardId,
                from_column_id: fromColumnId,
                to_column_id: toColumnId,
                workflow_slug: workflowSlug,
                item: item,
            });

            const result = response.data;

            if (result.success) {
                console.log('✅ Card movido com sucesso (confirmado pelo backend):', result.data);

                // 🎉 Toast de sucesso com mensagem do backend
                const successMessage = result.message || 'Card movido com sucesso';
                success('Movimentação realizada', successMessage);

                // Atualizar dados completos se necessário (para pegar mudanças do backend)
                if (onRefresh) {
                    setTimeout(() => onRefresh(), 100); // Pequeno delay para UX fluida
                }

                return true;
            } else {
                throw new Error(result.message || 'Erro ao mover card');
            }

        } catch (err: any) {
            console.error('❌ Erro ao mover card - revertendo mudança local:', err);
            
            // 🎯 REVERTER MUDANÇA OTIMISTA: Voltar card para coluna original
            revertItemLocally(cardId, fromColumnSlug);
            
            // 🎯 Extrair mensagens específicas do backend
            const response = err.response?.data;
            let title = 'Erro ao mover card';
            let description = 'Ocorreu um erro inesperado';

            if (response) {
                // 🎯 Tratar diferentes códigos de status
                const status = err.response?.status;
                
                switch (status) {
                    case 422: // Validation Error
                        title = 'Movimentação não permitida';
                        break;
                    case 404: // Not Found
                        title = 'Item não encontrado';
                        break;
                    case 403: // Forbidden
                        title = 'Acesso negado';
                        break;
                    case 500: // Server Error
                        title = 'Erro interno do sistema';
                        break;
                    default:
                        // 🔥 Usar mensagem específica do backend como título
                        if (response.message) {
                            title = response.message;
                        }
                }

                // 🔥 Extrair detalhes específicos dos erros de validação
                if (response.errors) {
                    const errorDetails = [];
                    
                    // Verificar erros de transição
                    if (response.errors.transition) {
                        errorDetails.push(...response.errors.transition);
                    }
                    
                    // Verificar erros de workflow
                    if (response.errors.workflow) {
                        errorDetails.push(...response.errors.workflow);
                    }
                    
                    // Verificar outros erros
                    Object.entries(response.errors).forEach(([key, messages]) => {
                        if (key !== 'transition' && key !== 'workflow' && Array.isArray(messages)) {
                            errorDetails.push(...messages);
                        }
                    });

                    // Usar o primeiro erro como descrição detalhada
                    if (errorDetails.length > 0) {
                        description = errorDetails[0];
                    }
                } else if (response.message) {
                    // Se não há erros específicos, usar a mensagem como descrição
                    description = response.message;
                    title = 'Movimentação não permitida';
                }
            } else {
                // Erro de rede ou outro erro genérico
                description = err.message || 'Verifique sua conexão e tente novamente';
                title = 'Erro de conexão';
            }

            // 🚨 Exibir toast com mensagens contextuais
            error(title, description);
            return false;
        } finally {
            // 🎯 Remover card do estado de processamento
            setProcessingCards(prev => {
                const newSet = new Set(prev);
                newSet.delete(cardId);
                return newSet;
            });
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
                                processingCards={processingCards}
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
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
import axios from 'axios';
import type { KanbanBoardProps, DragDropConfig } from '../types';

/**
 * Componente principal do Kanban Board
 * 
 * Sistema dinâmico que funciona com qualquer tipo de CRUD e workflow.
 * Suporta drag & drop, filtros inteligentes e integração com APIs.
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
    const [searchTerm, setSearchTerm] = useState('');
    const [localData, setLocalData] = useState(data);

    // Configurações padrão
    const {
        height = '700px',
        dragAndDrop = true,
        validateTransition,
        onMoveCard,
        workflowSlug = 'generic',
        apiEndpoint = '/api/admin/kanban/move-card'
    } = config;

    // 🎯 Criar instância da API dinâmica
    const kanbanApi = useMemo(() => {
        // Detectar recurso baseado na URL atual
        const pathSegments = window.location.pathname.split('/').filter(Boolean);
        const adminIndex = pathSegments.indexOf('admin');
        
        let resource = 'kanban';
        let detectedWorkflowSlug = workflowSlug;
        
        if (adminIndex !== -1 && pathSegments[adminIndex + 1]) {
            resource = pathSegments[adminIndex + 1];
            
            // Mapear recursos para slugs de workflow
            const resourceToWorkflowSlug: Record<string, string> = {
                tickets: 'suporte-tecnico',
                sales: 'pipeline-vendas',
                orders: 'processamento-pedidos',
                pipeline: 'desenvolvimento',
                projects: 'gestao-projetos',
                leads: 'captacao-leads',
                support: 'atendimento-cliente',
            };
            
            detectedWorkflowSlug = resourceToWorkflowSlug[resource] || resource;
        }

        // Configurar Axios com headers padrão
        const apiInstance = axios.create({
            baseURL: '/api/admin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        // Interceptor para CSRF token
        apiInstance.interceptors.request.use((config) => {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                config.headers['X-CSRF-TOKEN'] = csrfToken;
            }
            return config;
        });

        return {
            resource,
            workflowSlug: detectedWorkflowSlug,
            api: apiInstance,
        };
    }, [workflowSlug]);

    // 🎯 Função para mover card usando Axios
    const handleMoveCard = async (cardId: string, fromColumnId: string, toColumnId: string, item: any): Promise<boolean> => {
        try {
            console.log('🚀 Movendo card via Axios:', {
                cardId,
                fromColumnId,
                toColumnId,
                workflowSlug: kanbanApi.workflowSlug,
                resource: kanbanApi.resource
            });

            const response = await kanbanApi.api.post(`/${kanbanApi.resource}/kanban/move-card`, {
                card_id: cardId,
                from_template_id: fromColumnId,
                to_template_id: toColumnId,
                workflow_slug: kanbanApi.workflowSlug,
                item: item,
                workflow_data: {
                    // Dados adicionais do workflow se necessário
                    moved_at: new Date().toISOString(),
                    user_agent: navigator.userAgent,
                }
            });

            const result = response.data;
            
            if (result.success) {
                console.log('✅ Card movido com sucesso (Axios):', result.data);
                
                // Atualizar dados locais com resposta do backend
                setLocalData(prevData => {
                    return prevData.map(dataItem => {
                        if (dataItem.id === cardId) {
                            return {
                                ...dataItem,
                                currentWorkflow: {
                                    ...dataItem.currentWorkflow,
                                    current_template_id: result.data?.current_template_id || toColumnId,
                                    current_step: result.data?.current_step || getStepFromColumnId(toColumnId),
                                    template_slug: result.data?.template_slug || toColumnId,
                                    updated_at: result.data?.moved_at || new Date().toISOString(),
                                },
                                // Forçar re-render atualizando um timestamp
                                _kanban_updated_at: new Date().toISOString()
                            };
                        }
                        return dataItem;
                    });
                });
                
                return true;
            } else {
                console.error('❌ Backend rejeitou movimento:', result.message);
                throw new Error(result.message || 'Erro ao mover card');
            }

        } catch (error: any) {
            console.error('❌ Erro ao mover card (Axios):', error);
            
            // Reverter mudança local se backend falhou
            setLocalData(prevData => {
                return prevData.map(dataItem => {
                    if (dataItem.id === cardId) {
                        return {
                            ...dataItem,
                            currentWorkflow: {
                                ...dataItem.currentWorkflow,
                                current_template_id: fromColumnId,
                                // Manter dados originais
                            }
                        };
                    }
                    return dataItem;
                });
            });

            // Mostrar erro para o usuário
            const errorMessage = error.response?.data?.message || error.message || 'Erro ao mover card';
            alert(`Erro: ${errorMessage}`);
            
            return false;
        }
    };

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

        const mapping = mappings[workflowSlug] || mappings.generic;
        return mapping[columnId] || 1;
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
                    // 1. Verificar se o template_slug corresponde ao ID da coluna (mais confiável)
                    if (item.currentWorkflow.template_slug) {
                        return item.currentWorkflow.template_slug === column.id;
                    }
                    
                    // 2. Verificar se o template atual corresponde ao ID da coluna
                    const currentTemplate = item.currentWorkflow.currentTemplate;
                    if (currentTemplate && currentTemplate.slug) {
                        return currentTemplate.slug === column.id;
                    }
                    
                    // 3. Fallback: verificar por current_step mapeado para column.id
                    const expectedStep = getStepFromColumnId(column.id);
                    if (item.currentWorkflow.current_step) {
                        return item.currentWorkflow.current_step === expectedStep;
                    }
                    
                    // 4. Último fallback: verificar por current_template_id (ULID)
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

    // 🎯 Configuração do Drag & Drop com Axios
    const dragConfig: DragDropConfig = {
        enabled: dragAndDrop,
        validateTransition: validateTransition || (async (from, to, item) => {
            // Validação padrão: permitir qualquer transição
            console.log('🔍 Validando transição:', from, '→', to, 'para item:', item?.id, 'workflow:', kanbanApi.workflowSlug);
            return true;
        }),
        onMoveCard: onMoveCard || handleMoveCard,
        workflowSlug: kanbanApi.workflowSlug,
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
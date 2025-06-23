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
import { useDragDrop } from '../hooks';
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

    // Configurações padrão
    const { 
        height = '700px', 
        dragAndDrop = true, // Ativado por padrão agora
        validateTransition,
        onMoveCard
    } = config;

    // 🎯 Configuração do Drag & Drop
    const dragConfig: DragDropConfig = {
        enabled: dragAndDrop,
        validateTransition: validateTransition || ((from, to, item) => {
            // Validação padrão: permitir qualquer transição
            // TODO: Implementar validações específicas de workflow
            console.log('🔍 Validating transition:', from, '→', to, 'for item:', item?.id);
            return true;
        }),
        onMoveCard: onMoveCard || (async (cardId, fromColumnId, toColumnId, item) => {
            console.log('🎯 Moving card:', { cardId, fromColumnId, toColumnId, item });
            
            // TODO: Implementar chamada para backend
            // Por enquanto, simular sucesso
            return new Promise(resolve => {
                setTimeout(() => {
                    console.log('✅ Card moved successfully (simulated)');
                    resolve(true);
                }, 500);
            });
        })
    };

    // Hook de drag & drop
    const {
        activeId,
        isDragging,
        draggedItem,
        handleDragStart,
        handleDragOver,
        handleDragEnd,
        handleDragCancel,
        isCardDragging
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

    // 🚀 Sistema de filtro corrigido - aplicar filtro de cada coluna aos dados
    const filteredDataByColumn = useMemo(() => {
        const result = new Map();
        
        columns.forEach(column => {
            let columnData = [];
            
            if (column.filter && typeof column.filter === 'function') {
                // Usar função de filtro personalizada da coluna
                columnData = data.filter(column.filter);
            } else if (column.key) {
                // Fallback: filtrar por key/value se não tiver função personalizada
                // Este caso pode ser usado para filtros simples baseados em propriedades
                columnData = data.filter(item => {
                    const value = item[column.key];
                    return value === column.id || (value && value.value === column.id);
                });
            } else {
                // Se não tiver filtro, não mostrar dados (evita duplicação)
                columnData = [];
            }
            
            result.set(column.id, columnData);
        });
        
        return result;
    }, [data, columns]);

    // 🚀 Estatísticas corrigidas - calcular por coluna com dados filtrados
    const stats = useMemo(() => {
        const total = data.length;
        const byColumn = columns.map(column => ({
            id: column.id,
            title: column.title,
            count: filteredDataByColumn.get(column.id)?.length || 0
        }));

        return { total, byColumn };
    }, [data, columns, filteredDataByColumn]);
  

    // Handler para ações
    const handleAction = (actionId: string, item: any, extra?: any) => {
        onAction?.(actionId, item, extra);
    };

    return (
        <DndContext
            sensors={sensors}
            collisionDetection={closestCenter}
            onDragStart={handleDragStart}
            onDragOver={handleDragOver}
            onDragEnd={handleDragEnd}
            onDragCancel={handleDragCancel}
        >
            <div className="kanban-board space-y-4">
                {/* Header do Board - Estilo da segunda imagem */}
                <div className="flex items-center justify-between bg-white p-4 border-b">
                    <div className="flex items-center gap-6">
                        <div>
                            <h1 className="text-2xl font-semibold text-gray-900">
                                {meta.title || 'Quadro Kanban'}
                            </h1>
                            <p className="text-sm text-gray-500 mt-1">
                                {meta.description || `Visualização em Kanban com ${stats.total} itens`}
                                {dragAndDrop && ' • Arraste cards entre colunas'}
                            </p>
                        </div>

                        {/* Estatísticas por coluna - Estilo badges inline */}
                        <div className="flex items-center gap-4 text-sm">
                            {stats.byColumn.map((stat, index) => {
                                const column = columns[index];
                                return (
                                    <div key={stat.id} className="flex items-center gap-2">
                                        <span 
                                            className="text-sm font-medium"
                                            style={{ color: column?.color || '#6b7280' }}
                                        >
                                            {stat.title}: {stat.count}
                                        </span>
                                    </div>
                                );
                            })}
                        </div>
                    </div>

                    <div className="flex items-center gap-2"> 
                        {/* Indicador de drag ativo */}
                        {isDragging && (
                            <div className="flex items-center gap-2 text-sm text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
                                <div className="w-2 h-2 bg-blue-500 rounded-full animate-pulse" />
                                Movendo card...
                            </div>
                        )}
                        
                        {/* Refresh */}
                        <Button 
                            variant="outline" 
                            size="sm"
                            onClick={onRefresh}
                            disabled={isDragging}
                        >
                            <RefreshCw className="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                {/* Board Content - Layout Horizontal com Scroll */}
                <div 
                    className="flex gap-4 overflow-x-auto pb-4"
                    style={{ height: height }}
                >
                    {columns.map(column => (
                        <KanbanColumn
                            key={column.id}
                            column={column}
                            data={filteredDataByColumn.get(column.id) || []}
                            tableColumns={tableColumns}
                            actions={actions}
                            onAction={handleAction}
                            dragAndDrop={dragAndDrop}
                            isDragActive={isDragging}
                        />
                    ))}
                </div>

                {/* Empty State */}
                {data.length === 0 && (
                    <div className="flex flex-col items-center justify-center py-12 text-gray-400">
                        <div className="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                            <Grid3X3 className="h-8 w-8" />
                        </div>
                        <p className="text-lg font-medium">Nenhum dado disponível</p>
                        <p className="text-sm">Adicione alguns itens para visualizar no Kanban</p>
                    </div>
                )}
            </div>

            {/* Drag Overlay - Card sendo arrastado */}
            <DragOverlay>
                {activeId && draggedItem ? (
                    <div className="rotate-6 opacity-90 shadow-2xl">
                        <KanbanCard
                            item={draggedItem}
                            tableColumns={tableColumns || []}
                            actions={actions}
                            onAction={handleAction}
                            isDragging={true}
                            dragOverlay={true}
                        />
                    </div>
                ) : null}
            </DragOverlay>
        </DndContext>
    );
} 
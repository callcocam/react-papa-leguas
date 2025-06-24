import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Plus, 
    MoreVertical
} from 'lucide-react';
import { useDroppable } from '@dnd-kit/core';
import { Icons } from '../../icons';
import KanbanCard from './KanbanCard';
import type { KanbanColumnProps } from '../types';

/**
 * Componente que representa uma coluna individual do Kanban com suporte a Drag & Drop.
 * 
 * Sistema simplificado que usa diretamente as cores e configurações vindas do backend.
 */
export default function KanbanColumn({
    column,
    data,
    tableColumns = [],
    actions = [],
    onAction,
    onDrop,
    dragAndDrop = false,
    isDragActive = false
}: KanbanColumnProps) { 
    // Setup do droppable para esta coluna
    const {
        isOver,
        setNodeRef,
    } = useDroppable({
        id: column.id,
        data: {
            type: 'column',
            columnId: column.id,
            accepts: ['card']
        },
        disabled: !dragAndDrop
    });
     
    const IconComponent = column.icon && typeof column.icon === 'string'
        ? (Icons[column.icon as keyof typeof Icons] as any)
        : null;

    const isOverLimit = column.maxItems && data.length > column.maxItems;
    const limitWarning = isOverLimit ? `Limite excedido (${data.length}/${column.maxItems})` : null;

    // 🎨 Sistema simples - usar cor direta do backend
    const columnColor = column.color || '#6b7280';

    // 🎯 Classes simples baseadas no estado de drag
    const getColumnClasses = () => {
        let classes = `kanban-column flex flex-col h-full min-w-[300px] flex-shrink-0 rounded-lg bg-white shadow-sm border transition-all duration-200 overflow-hidden`;
        
        if (dragAndDrop) {
            if (isOver) {
                // Quando um card está sendo arrastado sobre esta coluna
                classes += ` border-2 bg-gray-50 shadow-lg scale-105`;
            } else if (isDragActive) {
                // Quando há um drag ativo mas não está sobre esta coluna
                classes += ` border-2 border-dashed opacity-80`;
            } else {
                // Estado normal com drag habilitado
                classes += ` border-dashed hover:border-solid`;
            }
        } else {
            // Estado normal sem drag
            classes += ` border-gray-200`;
        }
        
        return classes;
    };

    return (
        <div 
            ref={setNodeRef}
            className={getColumnClasses()}
        >
            {/* Header da Coluna */}
            <div 
                className="p-3 bg-white rounded-t-lg border-b"
                style={{ borderBottomColor: columnColor }}
            >
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        {/* Número da ordem */}
                        {(column.order || column.sort_order) && (
                            <div 
                                className="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold text-white" 
                                style={{ backgroundColor: columnColor }}
                            >
                                {column.order || column.sort_order}
                            </div>
                        )}
                        
                        {/* Ícone da coluna */}
                        {IconComponent && (
                            <IconComponent 
                                className="h-4 w-4" 
                                style={{ color: columnColor }}
                            />
                        )}
                        
                        <h3 
                            className="text-sm font-semibold"
                            style={{ color: columnColor }}
                        >
                            {column.title}
                        </h3>
                        
                        <span 
                            className="px-2 py-1 text-xs font-medium rounded-full text-white"
                            style={{ backgroundColor: columnColor }}
                        >
                            {data.length}
                        </span>
                        
                        {/* Indicador de drop ativo */}
                        {isOver && (
                            <div className="flex items-center gap-1 text-xs text-blue-600">
                                <div className="w-2 h-2 bg-blue-500 rounded-full animate-pulse" />
                                Soltar aqui
                            </div>
                        )}
                    </div>

                    <div className="flex items-center gap-1">
                        {/* Botão Adicionar */}
                        <Button 
                            variant="ghost" 
                            size="sm" 
                            className="h-7 w-7 p-0 hover:bg-gray-50"
                            style={{ color: columnColor }}
                        >
                            <Plus className="h-4 w-4" />
                        </Button>

                        {/* Menu da Coluna */}
                        <Button 
                            variant="ghost" 
                            size="sm" 
                            className="h-7 w-7 p-0 hover:bg-gray-50"
                            style={{ color: columnColor }}
                        >
                            <MoreVertical className="h-4 w-4" />
                        </Button>
                    </div>
                </div>

                {/* Warning de limite */}
                {limitWarning && (
                    <div className="text-xs text-red-600 mt-2">
                        {limitWarning}
                    </div>
                )}
            </div>

            {/* Lista de Cards */}
            <div className="flex-1 space-y-3 overflow-y-auto p-4 overflow-x-hidden">
                {data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-16 text-gray-400">
                        <div className="text-center">
                            <p className="text-sm font-medium mb-1">
                                {isOver ? 'Solte o card aqui' : 'Vazio'}
                            </p>
                            <p className="text-xs">
                                {dragAndDrop ? 'Arraste cards aqui' : 'Nenhum item'}
                            </p>
                        </div>
                    </div>
                ) : (
                    data.map((item, index) => (
                        <KanbanCard
                            key={item.id || index}
                            item={item}
                            tableColumns={tableColumns}
                            column={column}
                            actions={actions}
                            onAction={onAction}
                            draggable={dragAndDrop}
                        />
                    ))
                )}
            </div>

            {/* Footer da Coluna */}
            {data.length > 0 && (
                <div className="p-3 border-t border-gray-100">
                    <div className="flex items-center justify-between text-xs text-gray-500">
                        <span>
                            {data.length} {data.length === 1 ? 'item' : 'itens'}
                        </span>
                        {column.maxItems && (
                            <span>
                                Limite: {column.maxItems}
                            </span>
                        )}
                    </div>
                </div>
            )}
        </div>
    );
} 
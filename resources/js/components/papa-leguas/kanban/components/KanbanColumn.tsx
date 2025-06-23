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
 * Funcionalidades:
 * - Header com título, ícone e contador
 * - Lista de cards da coluna
 * - Ações do backend integradas
 * - Suporte a drag and drop com @dnd-kit
 * - Limite máximo de itens
 * - Feedback visual durante drag
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

    // 🎨 Sistema simples e direto - funciona 100%
    const columnColor = column.color || '#6b7280';
    
// 🎨 Sistema de cores com classes Tailwind completas
const WORKFLOW_COLORS = [
    { 
        value: '#3b82f6', 
        label: 'Azul', 
        border: 'border-blue-500',
        borderDashed: 'border-blue-500 border-dashed',
        text: 'text-blue-600',
        textLight: 'text-blue-500',
        bgLight: 'bg-blue-50',
        bgBadge: 'bg-blue-100',
        hoverBg: 'hover:bg-blue-50'
    },
    { 
        value: '#ef4444', 
        label: 'Vermelho', 
        border: 'border-red-500',
        borderDashed: 'border-red-500 border-dashed',
        text: 'text-red-600',
        textLight: 'text-red-500',
        bgLight: 'bg-red-50',
        bgBadge: 'bg-red-100',
        hoverBg: 'hover:bg-red-50'
    },
    { 
        value: '#10b981', 
        label: 'Verde', 
        border: 'border-green-500',
        borderDashed: 'border-green-500 border-dashed',
        text: 'text-green-600',
        textLight: 'text-green-500',
        bgLight: 'bg-green-50',
        bgBadge: 'bg-green-100',
        hoverBg: 'hover:bg-green-50'
    },
    { 
        value: '#f59e0b', 
        label: 'Amarelo', 
        border: 'border-yellow-500',
        borderDashed: 'border-yellow-500 border-dashed',
        text: 'text-yellow-600',
        textLight: 'text-yellow-500',
        bgLight: 'bg-yellow-50',
        bgBadge: 'bg-yellow-100',
        hoverBg: 'hover:bg-yellow-50'
    },
    { 
        value: '#8b5cf6', 
        label: 'Roxo', 
        border: 'border-purple-500',
        borderDashed: 'border-purple-500 border-dashed',
        text: 'text-purple-600',
        textLight: 'text-purple-500',
        bgLight: 'bg-purple-50',
        bgBadge: 'bg-purple-100',
        hoverBg: 'hover:bg-purple-50'
    },
    { 
        value: '#06b6d4', 
        label: 'Ciano', 
        border: 'border-cyan-500',
        borderDashed: 'border-cyan-500 border-dashed',
        text: 'text-cyan-600',
        textLight: 'text-cyan-500',
        bgLight: 'bg-cyan-50',
        bgBadge: 'bg-cyan-100',
        hoverBg: 'hover:bg-cyan-50'
    },
    { 
        value: '#dc2626', 
        label: 'Vermelho Escuro', 
        border: 'border-red-600',
        borderDashed: 'border-red-600 border-dashed',
        text: 'text-red-700',
        textLight: 'text-red-600',
        bgLight: 'bg-red-50',
        bgBadge: 'bg-red-100',
        hoverBg: 'hover:bg-red-50'
    },
    { 
        value: '#059669', 
        label: 'Verde Escuro', 
        border: 'border-green-600',
        borderDashed: 'border-green-600 border-dashed',
        text: 'text-green-700',
        textLight: 'text-green-600',
        bgLight: 'bg-green-50',
        bgBadge: 'bg-green-100',
        hoverBg: 'hover:bg-green-50'
    },
    { 
        value: '#6b7280', 
        label: 'Cinza', 
        border: 'border-gray-500',
        borderDashed: 'border-gray-500 border-dashed',
        text: 'text-gray-600',
        textLight: 'text-gray-500',
        bgLight: 'bg-gray-50',
        bgBadge: 'bg-gray-100',
        hoverBg: 'hover:bg-gray-50'
    },
];

// Encontrar cor correspondente ou usar fallback
const colorClasses = WORKFLOW_COLORS.find(color => color.value === columnColor) || {
    border: 'border-gray-500',
    borderDashed: 'border-gray-500 border-dashed',
    text: 'text-gray-600',
    textLight: 'text-gray-500',
    bgLight: 'bg-gray-50',
    bgBadge: 'bg-gray-100',
    hoverBg: 'hover:bg-gray-50'
};

    // Classes dinâmicas baseadas no estado de drag
    const getColumnClasses = () => {
        let classes = `kanban-column flex flex-col h-full min-w-[300px] flex-shrink-0 rounded-lg bg-white shadow-sm border transition-all duration-200`;
        
        if (dragAndDrop) {
            if (isOver) {
                // Quando um card está sendo arrastado sobre esta coluna
                classes += ` ${colorClasses.border} border-2 ${colorClasses.bgLight} shadow-lg scale-105`;
            } else if (isDragActive) {
                // Quando há um drag ativo mas não está sobre esta coluna
                classes += ` ${colorClasses.borderDashed} border-2 opacity-80`;
            } else {
                // Estado normal com drag habilitado
                classes += ` ${colorClasses.borderDashed} hover:${colorClasses.border}`;
            }
        } else {
            // Estado normal sem drag
            classes += ` ${colorClasses.borderDashed}`;
        }
        
        return classes;
    };

    return (
        <div 
            ref={setNodeRef}
            className={getColumnClasses()}
        >
            {/* Header da Coluna - Branco separado da borda */}
            <div className={`p-3 bg-white rounded-t-lg border-b ${colorClasses.border}`}>
                <div className="flex items-center justify-between">
                    <div className="flex items-center gap-2">
                        {/* Número da ordem */}
                        {(column.order || column.sort_order) && (
                            <div className={`flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold text-white`} 
                                 style={{ backgroundColor: columnColor }}>
                                {column.order || column.sort_order}
                            </div>
                        )}
                        
                        <h3 className={`text-sm font-semibold ${colorClasses.text}`}>
                            {column.title}
                        </h3>
                        
                        <span className={`px-2 py-1 text-xs font-medium rounded-full ${colorClasses.bgBadge} ${colorClasses.text}`}>
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
                            className={`h-7 w-7 p-0 ${colorClasses.hoverBg} ${colorClasses.textLight}`}
                        >
                            <Plus className="h-4 w-4" />
                        </Button>

                        {/* Menu da Coluna */}
                        <Button 
                            variant="ghost" 
                            size="sm" 
                            className={`h-7 w-7 p-0 ${colorClasses.hoverBg} ${colorClasses.textLight}`}
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
            <div className="flex-1 space-y-3 overflow-y-auto p-4">
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
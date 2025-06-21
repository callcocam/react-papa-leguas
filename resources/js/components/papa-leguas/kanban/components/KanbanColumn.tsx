import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Plus, 
    MoreVertical
} from 'lucide-react';
import { Icons } from '../../icons';
import KanbanCard from './KanbanCard';
import type { KanbanColumnProps } from '../types';

/**
 * Componente que representa uma coluna individual do Kanban.
 * 
 * Funcionalidades:
 * - Header com título, ícone e contador
 * - Lista de cards da coluna
 * - Ações do backend integradas
 * - Suporte a drag and drop (futuro)
 * - Limite máximo de itens
 */
export default function KanbanColumn({
    column,
    data,
    actions = [],
    onAction,
    onDrop
}: KanbanColumnProps) {
    const IconComponent = column.icon && typeof column.icon === 'string'
        ? (Icons[column.icon as keyof typeof Icons] as any)
        : null;

    const isOverLimit = column.maxItems && data.length > column.maxItems;
    const limitWarning = isOverLimit ? `Limite excedido (${data.length}/${column.maxItems})` : null;

    return (
        <div className="kanban-column flex flex-col h-full">
            {/* Header da Coluna */}
            <Card className="mb-4 shadow-sm">
                <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            {IconComponent && (
                                <IconComponent className="h-4 w-4 text-gray-600" />
                            )}
                            <CardTitle className="text-sm font-medium text-gray-700">
                                {column.title}
                            </CardTitle>
                            <Badge 
                                variant="secondary" 
                                className={`text-xs ${isOverLimit ? 'bg-red-100 text-red-700' : ''}`}
                            >
                                {data.length}
                                {column.maxItems && ` / ${column.maxItems}`}
                            </Badge>
                        </div>

                        <div className="flex items-center gap-1">
                            {/* Botão Adicionar */}
                            <Button variant="ghost" size="sm" className="h-6 w-6 p-0">
                                <Plus className="h-3 w-3" />
                            </Button>

                            {/* Menu da Coluna */}
                            <Button variant="ghost" size="sm" className="h-6 w-6 p-0">
                                <MoreVertical className="h-3 w-3" />
                            </Button>
                        </div>
                    </div>

                    {/* Warning de limite */}
                    {limitWarning && (
                        <div className="text-xs text-red-600 mt-1">
                            {limitWarning}
                        </div>
                    )}
                </CardHeader>
            </Card>

            {/* Lista de Cards */}
            <div className="flex-1 space-y-3 overflow-y-auto">
                {data.length === 0 ? (
                    <div className="flex flex-col items-center justify-center py-8 text-gray-400">
                        <div className="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-2">
                            {IconComponent ? (
                                <IconComponent className="h-6 w-6" />
                            ) : (
                                <Plus className="h-6 w-6" />
                            )}
                        </div>
                        <p className="text-sm text-center">
                            Nenhum item nesta coluna
                        </p>
                        <Button variant="ghost" size="sm" className="mt-2 text-xs">
                            Adicionar item
                        </Button>
                    </div>
                ) : (
                    data.map((item, index) => (
                        <KanbanCard
                            key={item.id || index}
                            item={item}
                            column={column}
                            actions={actions}
                            onAction={onAction}
                            draggable={!!onDrop}
                            onDragStart={onDrop ? (draggedItem) => {
                                // Implementar drag start no futuro
                                console.log('Drag start:', draggedItem);
                            } : undefined}
                        />
                    ))
                )}
            </div>

            {/* Footer da Coluna */}
            {data.length > 0 && (
                <div className="mt-4 pt-3 border-t border-gray-200">
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
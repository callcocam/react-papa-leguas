import React, { useState } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    ChevronDown,
    ChevronRight,
    MoreVertical,
    Calendar,
    User,
    Tag,
    Clock,
    AlertCircle
} from 'lucide-react';
import { useDraggable } from '@dnd-kit/core';
import { Icons } from '../../icons';
import type { KanbanCardProps } from '../types';

/**
 * Componente que representa um card individual no Kanban com suporte a Drag & Drop.
 * 
 * Sistema simplificado que usa diretamente os dados formatados vindos do backend.
 */
export default function KanbanCard({
    item,
    column,
    tableColumns = [],
    actions = [],
    onAction,
    draggable = false,
    isDragging = false,
    dragOverlay = false
}: KanbanCardProps) {
    const [showActions, setShowActions] = useState(false);

    // Setup do draggable para este card
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        isDragging: isDraggingFromHook,
    } = useDraggable({
        id: item.id,
        data: {
            type: 'card',
            item: item,
            // Usar o ID da coluna atual onde o card está (com fallback)
            columnId: column?.id || 'unknown'
        },
        disabled: !draggable
    });
    // 🎯 Usar dados formatados do kanban_data (já processados pelo backend)
    const kanbanData = item.currentWorkflow?.kanban_data || {};
    
    // Dados principais do card (usar kanban_data)
    const ticketNumber = kanbanData.ticket_number || item.ticket_number || item.id?.slice(-8) || 'N/A';
    const title = kanbanData.title || item.title || 'Sem título';
    const description = item.description || '';

    // Dados do workflow (usar kanban_data formatado)
    const workflowStatus = kanbanData.status || item.currentWorkflow?.status || 'Ativo';
    
    // Prioridade (usar kanban_data)
    const priority = kanbanData.priority || item.priority?.name || 'Normal';
    const priorityColor = kanbanData.priority_color || item.priority?.color || '#6b7280';

    // Categoria (usar kanban_data)
    const category = kanbanData.category || item.category?.name || 'Geral';

    // Responsável (usar kanban_data)
    const assignedTo = kanbanData.assigned_to || item.assignee?.name || 'Não atribuído';

    // Datas e prazos (usar kanban_data)
    const dueAt = kanbanData.due_at_raw || item.due_date || item.due_at || null;
    const timeSpent = kanbanData.time_spent || item.time_spent || null;

    // Progresso (usar kanban_data)
    const currentStep = kanbanData.current_step || item.currentWorkflow?.current_step || 1;
    const totalSteps = kanbanData.total_steps || item.currentWorkflow?.total_steps || 4;
    const progressPercentage = kanbanData.progress_percentage || item.currentWorkflow?.progress_percentage || 0;

    // Flags calculadas simples
    const isOverdue = dueAt ? new Date(dueAt) < new Date() : false;

    // Cor da coluna (usar cor da coluna passada nas props como prioridade)
    const columnColor = column?.color || kanbanData.status_color || item.currentWorkflow?.currentTemplate?.color || priorityColor || '#6b7280';

    // Determinar se está sendo arrastado
    const isBeingDragged = isDragging || isDraggingFromHook;

    // Estilos de transformação para drag
    const style = transform ? {
        transform: `translate3d(${transform.x}px, ${transform.y}px, 0)`,
    } : undefined;

    // Classes dinâmicas baseadas no estado
    const getCardClasses = () => {
        let classes = "kanban-card transition-all duration-200 bg-white border border-gray-200 rounded-lg";

        if (dragOverlay) {
            classes += " shadow-2xl rotate-6 opacity-90";
        } else if (isBeingDragged) {
            classes += " opacity-50 scale-95 shadow-lg";
        } else if (draggable) {
            classes += " hover:shadow-md cursor-grab active:cursor-grabbing";
        } else {
            classes += " hover:shadow-md cursor-pointer";
        }

        return classes;
    };

    return (
        <Card
            ref={setNodeRef}
            style={style}
            className={getCardClasses()}
            {...listeners}
            {...attributes}
        >
            <CardHeader className="p-3 pb-2">
                {/* Header com número do ticket e título */}
                <div className="flex items-start gap-2">
                    <div
                        className="flex-shrink-0 w-7 h-7 rounded text-xs font-bold flex items-center justify-center text-white"
                        style={{ backgroundColor: columnColor }}
                    >
                        #{ticketNumber.length >= 2 ? ticketNumber.slice(-2) : ticketNumber}
                    </div>
                    <div className="flex-1 min-w-0">
                        <h3 className="font-medium text-sm text-gray-900 leading-tight line-clamp-2">
                            {title}
                        </h3>
                        <p className="text-xs text-gray-500 mt-1">
                            #{ticketNumber}
                        </p>
                    </div>
                    {isOverdue && (
                        <AlertCircle className="w-4 h-4 text-red-500 flex-shrink-0" />
                    )}
                </div>
            </CardHeader>

            <CardContent className="p-3 pt-0 space-y-3">
                {/* Status e Prioridade */}
                <div className="flex items-center gap-2 flex-wrap">
                    <Badge
                        className="text-xs px-2 py-1"
                        style={{
                            backgroundColor: columnColor + '20',
                            color: columnColor,
                            border: `1px solid ${columnColor}40`
                        }}
                    >
                        {workflowStatus}
                    </Badge>
                    <Badge
                        className="text-xs px-2 py-1"
                        style={{
                            backgroundColor: priorityColor + '20',
                            color: priorityColor,
                            border: `1px solid ${priorityColor}40`
                        }}
                    >
                        {priority}
                    </Badge>
                </div>

                {/* Informações principais */}
                <div className="space-y-2 text-xs">
                    {/* Responsável */}
                    <div className="flex items-center gap-2">
                        <User className="w-3 h-3 text-gray-400" />
                        <span className="text-gray-600 flex-1 truncate">{assignedTo}</span>
                    </div>

                    {/* Categoria */}
                    <div className="flex items-center gap-2">
                        <Tag className="w-3 h-3 text-gray-400" />
                        <span className="text-gray-600 flex-1 truncate">{category}</span>
                    </div>

                    {/* Prazo */}
                    {dueAt && (
                        <div className="flex items-center gap-2">
                            <Calendar className="w-3 h-3 text-gray-400" />
                            <span className={`text-xs flex-1 truncate ${isOverdue ? 'text-red-600 font-medium' : 'text-gray-600'}`}>
                                {new Date(dueAt).toLocaleDateString('pt-BR')}
                            </span>
                        </div>
                    )}

                    {/* Tempo gasto */}
                    {timeSpent && (
                        <div className="flex items-center gap-2">
                            <Clock className="w-3 h-3 text-gray-400" />
                            <span className="text-gray-600 flex-1">
                                {timeSpent}
                            </span>
                        </div>
                    )}
                </div>

                {/* Progresso do Workflow */}
                {progressPercentage > 0 && (
                    <div className="space-y-2">
                        <div className="flex items-center justify-between text-xs">
                            <span className="text-gray-600">
                                Progresso ({currentStep}/{totalSteps})
                            </span>
                            <span
                                className="font-medium"
                                style={{ color: columnColor }}
                            >
                                {Math.round(progressPercentage)}%
                            </span>
                        </div>
                        <div className="w-full bg-gray-200 rounded-full h-2">
                            <div
                                className="h-2 rounded-full transition-all duration-300"
                                style={{
                                    width: `${Math.min(progressPercentage, 100)}%`,
                                    backgroundColor: columnColor
                                }}
                            />
                        </div>
                    </div>
                )}

                {/* Menu de ações */}
                {actions && actions.length > 0 && !isBeingDragged && (
                    <div className="pt-2 border-t border-gray-100">
                        <Button
                            variant="ghost"
                            size="sm"
                            className="w-full h-7 text-xs text-gray-500 hover:text-gray-700"
                            onClick={(e) => {
                                e.stopPropagation();
                                setShowActions(!showActions);
                            }}
                        >
                            <MoreVertical className="w-3 h-3 mr-1" />
                            Ações
                            {showActions ? (
                                <ChevronDown className="w-3 h-3 ml-1" />
                            ) : (
                                <ChevronRight className="w-3 h-3 ml-1" />
                            )}
                        </Button>

                        {showActions && (
                            <div className="mt-2 space-y-1">
                                {actions.map((action, index) => (
                                    <Button
                                        key={index}
                                        variant="ghost"
                                        size="sm"
                                        className="w-full h-6 text-xs justify-start text-gray-600 hover:text-gray-800"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            onAction?.(action.id, item);
                                        }}
                                    >
                                        {action.icon && (
                                            <span className="w-3 h-3 mr-2">
                                                {/* Ícone da ação */}
                                            </span>
                                        )}
                                        {action.label}
                                    </Button>
                                ))}
                            </div>
                        )}
                    </div>
                )}
            </CardContent>
        </Card>
    );
} 
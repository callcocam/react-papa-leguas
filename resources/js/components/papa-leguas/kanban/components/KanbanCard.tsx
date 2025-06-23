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
import { Icons } from '../../icons';
import ColumnRenderer from '../columns/ColumnRenderer';
import type { KanbanCardProps } from '../types';

/**
 * Componente que representa um card individual no Kanban.
 * 
 * Funcionalidades:
 * - Exibe informações principais do item (ticket)
 * - Integração com dados do currentWorkflow
 * - Progresso visual baseado em workflow
 * - Ações do backend integradas
 * - Suporte a drag and drop (futuro)
 */
export default function KanbanCard({
    item,
    column,
    tableColumns = [],
    actions = [],
    isExpanded = false,
    onToggleExpansion,
    onAction,
    onDragStart,
    draggable = false
}: KanbanCardProps) {
    const [showActions, setShowActions] = useState(false);

    // 🔍 Debug - dados do workflow
    console.log('KanbanCard currentWorkflow:', item.currentWorkflow);
    console.log('KanbanCard kanban_data:', item.currentWorkflow?.kanban_data);

    // 🎯 Usar dados já formatados do backend (Workflowable.php)
    const currentWorkflow = item.currentWorkflow || {};
    const kanbanData = currentWorkflow.kanban_data || {};
    
    // Dados já formatados e prontos para uso
    const workflowTitle = kanbanData.title || 'Sem título';
    const ticketNumber = kanbanData.ticket_number || '';
    const workflowStatus = kanbanData.status || 'Ativo';
    const workflowStatusRaw = kanbanData.status_raw || 'active';
    const priority = kanbanData.priority || 'Normal';
    const category = kanbanData.category || 'Sem categoria';
    const assignedTo = kanbanData.assigned_to || 'Não atribuído';
    
    // Progresso
    const progressPercentage = kanbanData.progress_percentage || 0;
    const currentStep = kanbanData.current_step || 1;
    const totalSteps = kanbanData.total_steps || 1;
    const progressText = kanbanData.progress_text || `${currentStep}/${totalSteps}`;
    
    // Timing
    const timeSpent = kanbanData.time_spent || '';
    const dueAt = kanbanData.due_at || null;
    const dueAtRaw = kanbanData.due_at_raw || null;
    
    // Flags
    const isOverdue = kanbanData.is_overdue || false;
    const isUrgent = kanbanData.is_urgent || false;
    const isInternal = kanbanData.is_internal || false;
    
    // Cores (já calculadas no backend)
    const statusColor = kanbanData.status_color || '#3b82f6';
    const priorityColor = kanbanData.priority_color || '#6b7280';
    
    // 🔍 Debug dados formatados do backend
    console.log('✅ Dados formatados do Backend:');
    console.log('- workflowTitle:', workflowTitle);
    console.log('- workflowStatus:', workflowStatus);
    console.log('- priority:', priority);
    console.log('- assignedTo:', assignedTo);
    console.log('- progressPercentage:', progressPercentage);
    console.log('- isOverdue:', isOverdue);

    // Dados já vêm formatados do backend - não precisamos mais dessas funções!

    // Handler para drag start
    const handleDragStart = (e: React.DragEvent) => {
        if (!draggable || !onDragStart) return;
        
        e.dataTransfer.setData('application/json', JSON.stringify(item));
        e.dataTransfer.effectAllowed = 'move';
        onDragStart(item);
    };

    return (
        <Card 
            className="kanban-card transition-all duration-200 hover:shadow-md cursor-pointer bg-white border border-gray-200 rounded-lg"
            draggable={draggable}
            onDragStart={handleDragStart}
        >
            <CardHeader className="p-3 pb-2">
                {/* Header com número do ticket e título */}
                <div className="flex items-start gap-2">
                    <div 
                        className="flex-shrink-0 w-7 h-7 rounded text-xs font-bold flex items-center justify-center text-white"
                        style={{ backgroundColor: column.color }}
                    >
                        #{ticketNumber.slice(-2)}
                    </div>
                    <div className="flex-1 min-w-0">
                        <h3 className="font-medium text-sm text-gray-900 leading-tight line-clamp-2">
                            {workflowTitle}
                        </h3>
                        <p className="text-xs text-gray-500 mt-1">
                            {ticketNumber}
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
                            backgroundColor: statusColor + '20',
                            color: statusColor,
                            border: `1px solid ${statusColor}40`
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
                <div className="space-y-2">
                    <div className="flex items-center justify-between text-xs">
                        <span className="text-gray-600">
                            Progresso ({currentStep}/{totalSteps})
                        </span>
                        <span 
                            className="font-medium"
                            style={{ color: column.color }}
                        >
                            {Math.round(progressPercentage)}%
                        </span>
                    </div>
                    <div className="w-full bg-gray-200 rounded-full h-2">
                        <div
                            className="h-2 rounded-full transition-all duration-300"
                            style={{
                                width: `${Math.min(progressPercentage, 100)}%`,
                                backgroundColor: column.color
                            }}
                        />
                    </div>
                </div>

                {/* Menu de ações */}
                {actions && actions.length > 0 && (
                    <div className="pt-2 border-t border-gray-100">
                        <Button
                            variant="ghost"
                            size="sm"
                            className="w-full h-7 text-xs text-gray-500 hover:text-gray-700"
                            onClick={() => setShowActions(!showActions)}
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
                                        onClick={() => onAction?.(action.id, item)}
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
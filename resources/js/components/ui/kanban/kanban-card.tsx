import React from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    MoreVertical,
    Calendar,
    User,
    Clock,
    AlertTriangle
} from 'lucide-react';
import ColumnRenderer from '../../papa-leguas/table/columns/ColumnRenderer';
import ActionRenderer from '../../papa-leguas/table/actions/ActionRenderer';

interface KanbanCardProps {
    item: any;
    columns: any[];
    column: any; // Coluna do Kanban
    actions?: any[];
    className?: string;
    onAction?: (actionId: string, item: any) => void;
    draggable?: boolean;
    onDragStart?: (item: any) => void;
}

export default function KanbanCard({
    item,
    columns = [],
    column,
    actions = [],
    className = '',
    onAction,
    draggable = false,
    onDragStart
}: KanbanCardProps) {
    // Selecionar colunas principais para exibir no card (máximo 4)
    const primaryColumns = columns.slice(0, 4);
    
    // Handler para drag start
    const handleDragStart = (e: React.DragEvent) => {
        if (!draggable || !onDragStart) return;
        
        e.dataTransfer.setData('application/json', JSON.stringify(item));
        e.dataTransfer.effectAllowed = 'move';
        onDragStart(item);
    };

    // Renderizar campo usando o sistema de renderização das Tables
    const renderField = (column: any, isTitle = false) => {
        const value = item[column.key];
        
        return (
            <div className={`${isTitle ? 'mb-2' : 'mb-1'}`}>
                {!isTitle && (
                    <span className="text-xs text-gray-500 dark:text-gray-400 font-medium">
                        {column.label}:
                    </span>
                )}
                <div className={`${isTitle ? 'font-medium text-sm text-gray-900 dark:text-gray-100' : 'text-xs'}`}>
                    <ColumnRenderer
                        item={item}
                        column={column}
                        value={value}
                    />
                </div>
            </div>
        );
    };

    // Renderizar ações do item
    const renderActions = () => {
        const itemActions = item._actions || actions;
        if (!itemActions || itemActions.length === 0) return null;

        const visibleActions = itemActions.slice(0, 3); // Máximo 3 ações visíveis

        return (
            <div className="flex items-center gap-1">
                {visibleActions.map((action: any, index: number) => (
                    <ActionRenderer
                        key={action.key || index}
                        action={action}
                        item={item}
                    />
                ))}
                {itemActions.length > 3 && (
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-6 w-6 p-0"
                        title="Mais ações"
                    >
                        <MoreVertical className="h-3 w-3" />
                    </Button>
                )}
            </div>
        );
    };

    // Renderizar informações do workflow (se existir)
    const renderWorkflowInfo = () => {
        const workflowable = item.workflowables?.[0];
        if (!workflowable) return null;

        return (
            <div className="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                <div className="flex items-center justify-between text-xs text-gray-500">
                    {/* Progresso */}
                    {workflowable.progress_percentage && (
                        <div className="flex items-center gap-1">
                            <Clock className="h-3 w-3" />
                            <span>{workflowable.progress_percentage}%</span>
                        </div>
                    )}
                    
                    {/* Responsável */}
                    {workflowable.assigned_user && (
                        <div className="flex items-center gap-1">
                            <User className="h-3 w-3" />
                            <span>{workflowable.assigned_user.name}</span>
                        </div>
                    )}
                    
                    {/* SLA vencendo */}
                    {workflowable.due_at && new Date(workflowable.due_at) < new Date() && (
                        <div className="flex items-center gap-1 text-red-500">
                            <AlertTriangle className="h-3 w-3" />
                            <span>Vencido</span>
                        </div>
                    )}
                </div>
            </div>
        );
    };

    return (
        <Card 
            className={`kanban-card transition-all duration-200 hover:shadow-md cursor-pointer ${
                draggable ? 'cursor-move' : ''
            } ${className}`}
            style={{
                borderLeftColor: column.color || '#e5e7eb',
                borderLeftWidth: '3px'
            }}
            draggable={draggable}
            onDragStart={handleDragStart}
        >
            <CardHeader className="pb-2">
                <div className="flex items-start justify-between">
                    <div className="flex-1 min-w-0">
                        {/* Título principal (primeira coluna) */}
                        {primaryColumns[0] && renderField(primaryColumns[0], true)}
                        
                        {/* Campos secundários */}
                        <div className="space-y-1">
                            {primaryColumns.slice(1).map((col, index) => (
                                <div key={col.key || index}>
                                    {renderField(col)}
                                </div>
                            ))}
                        </div>
                    </div>

                    {/* Ações */}
                    <div className="ml-2">
                        {renderActions()}
                    </div>
                </div>
            </CardHeader>

            {/* Informações do workflow */}
            <CardContent className="pt-0">
                {renderWorkflowInfo()}
            </CardContent>
        </Card>
    );
} 
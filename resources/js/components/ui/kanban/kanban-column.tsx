import React from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Plus,
    MoreVertical,
    Info
} from 'lucide-react';
import KanbanCard from './kanban-card';

interface KanbanColumnProps {
    column: any;
    items: any[];
    tableColumns: any[];
    actions?: any[];
    onAddItem?: (columnId: string) => void;
    onItemAction?: (actionId: string, item: any) => void;
    onDrop?: (item: any, targetColumnId: string) => void;
    allowDrop?: boolean;
    className?: string;
}

export default function KanbanColumn({
    column,
    items = [],
    tableColumns = [],
    actions = [],
    onAddItem,
    onItemAction,
    onDrop,
    allowDrop = false,
    className = ''
}: KanbanColumnProps) {
    const [dragOver, setDragOver] = React.useState(false);

    // Handler para drop
    const handleDrop = (e: React.DragEvent) => {
        e.preventDefault();
        setDragOver(false);
        
        if (!onDrop) return;
        
        try {
            const itemData = JSON.parse(e.dataTransfer.getData('application/json'));
            onDrop(itemData, column.id);
        } catch (error) {
            console.error('Erro ao processar drop:', error);
        }
    };

    // Handler para drag over
    const handleDragOver = (e: React.DragEvent) => {
        if (!allowDrop) return;
        
        e.preventDefault();
        setDragOver(true);
    };

    // Handler para drag leave
    const handleDragLeave = (e: React.DragEvent) => {
        e.preventDefault();
        setDragOver(false);
    };

    // Verificar se está no limite
    const isAtLimit = column.limit && items.length >= column.limit;
    const isOverLimit = column.limit && items.length > column.limit;

    return (
        <div 
            className={`kanban-column flex-shrink-0 w-80 ${className}`}
            onDrop={handleDrop}
            onDragOver={handleDragOver}
            onDragLeave={handleDragLeave}
        >
            <Card 
                className={`h-full min-h-[400px] ${
                    dragOver ? 'ring-2 ring-blue-300 bg-blue-50' : ''
                } ${isOverLimit ? 'border-red-300' : ''}`}
            >
                {/* Header da coluna */}
                <CardHeader className="pb-3">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                            {/* Ícone da coluna */}
                            {column.icon && (
                                <div 
                                    className="w-4 h-4 rounded-full flex items-center justify-center"
                                    style={{ backgroundColor: column.color || '#6b7280' }}
                                >
                                    <span className="text-white text-xs">●</span>
                                </div>
                            )}
                            
                            <CardTitle className="text-sm font-medium">
                                {column.title}
                            </CardTitle>
                        </div>

                        <div className="flex items-center gap-1">
                            {/* Badge com contador */}
                            <Badge 
                                variant={isOverLimit ? 'destructive' : isAtLimit ? 'secondary' : 'outline'}
                                className="text-xs"
                            >
                                {items.length}
                                {column.limit && ` / ${column.limit}`}
                            </Badge>

                            {/* Informações da coluna */}
                            {(column.description || column.instructions) && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    className="h-6 w-6 p-0"
                                    title={column.description || column.instructions}
                                >
                                    <Info className="h-3 w-3" />
                                </Button>
                            )}

                            {/* Menu de opções */}
                            <Button
                                variant="ghost"
                                size="sm"
                                className="h-6 w-6 p-0"
                            >
                                <MoreVertical className="h-3 w-3" />
                            </Button>
                        </div>
                    </div>

                    {/* Descrição da coluna (se houver) */}
                    {column.description && (
                        <p className="text-xs text-gray-500 mt-1">
                            {column.description}
                        </p>
                    )}
                </CardHeader>

                {/* Conteúdo da coluna */}
                <CardContent className="pt-0 space-y-3 flex-1">
                    {/* Lista de cards */}
                    <div className="space-y-2 min-h-[200px]">
                        {items.length === 0 ? (
                            <div className="flex flex-col items-center justify-center py-8 text-center">
                                <div 
                                    className="rounded-full p-3 mb-2"
                                    style={{ backgroundColor: `${column.color}20` }}
                                >
                                    <div 
                                        className="w-4 h-4 rounded-full"
                                        style={{ backgroundColor: column.color || '#6b7280' }}
                                    />
                                </div>
                                <p className="text-xs text-gray-500">
                                    Nenhum item nesta coluna
                                </p>
                                {column.instructions && (
                                    <p className="text-xs text-gray-400 mt-1">
                                        {column.instructions}
                                    </p>
                                )}
                            </div>
                        ) : (
                            items.map((item, index) => (
                                <KanbanCard
                                    key={item.id || index}
                                    item={item}
                                    columns={tableColumns}
                                    column={column}
                                    actions={actions}
                                    onAction={onItemAction}
                                    draggable={allowDrop}
                                    onDragStart={(draggedItem) => {
                                        console.log('Drag iniciado:', draggedItem);
                                    }}
                                />
                            ))
                        )}
                    </div>

                    {/* Botão adicionar item */}
                    {onAddItem && !isAtLimit && (
                        <div className="pt-2 border-t border-gray-100">
                            <Button
                                variant="ghost"
                                size="sm"
                                className="w-full h-8 text-xs text-gray-500 hover:text-gray-700"
                                onClick={() => onAddItem(column.id)}
                            >
                                <Plus className="h-3 w-3 mr-1" />
                                Adicionar item
                            </Button>
                        </div>
                    )}

                    {/* Aviso de limite */}
                    {isAtLimit && (
                        <div className="pt-2 border-t border-gray-100">
                            <p className="text-xs text-center text-gray-500">
                                {isOverLimit ? 'Limite excedido!' : 'Limite atingido'}
                            </p>
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
} 
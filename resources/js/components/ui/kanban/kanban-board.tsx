import React, { useState, useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
    Search, 
    Filter, 
    RefreshCw,
    Settings,
    MoreVertical
} from 'lucide-react';
import KanbanColumn from './kanban-column';

interface KanbanBoardProps {
    data: any[];
    columns: any[]; // Colunas da tabela (para renderização)
    config: any;
    actions?: any[];
    meta?: any;
    onAction?: (actionId: string, item: any, extra?: any) => void;
    onFilter?: (filters: any) => void;
    onRefresh?: () => void;
    className?: string;
}

export default function KanbanBoard({
    data = [],
    columns = [],
    config = {},
    actions = [],
    meta = {},
    onAction,
    onFilter,
    onRefresh,
    className = ''
}: KanbanBoardProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [draggedItem, setDraggedItem] = useState<any>(null);

    // Configurações do Kanban
    const {
        groupBy = 'status',
        columns: kanbanColumns = [],
        allowDragDrop = false,
        showCardCount = true,
        cardSize = 'compact',
        workflowBased = false
    } = config;

    // Filtrar dados por busca
    const filteredData = useMemo(() => {
        if (!searchTerm) return data;
        
        return data.filter(item => {
            // Busca em campos principais do item
            const searchableFields = ['ticket_number', 'title', 'description', 'requester_name', 'requester_email'];
            return searchableFields.some(field => {
                const value = item[field];
                return value && String(value).toLowerCase().includes(searchTerm.toLowerCase());
            });
        });
    }, [data, searchTerm]);

    // Agrupar dados por colunas do Kanban
    const groupedData = useMemo(() => {
        const groups: { [key: string]: any[] } = {};
        
        // Inicializar grupos vazios
        kanbanColumns.forEach((column: any) => {
            groups[column.id] = [];
        });

        // Distribuir itens pelas colunas
        filteredData.forEach(item => {
            kanbanColumns.forEach((column: any) => {
                let matches = false;
                
                if (column.filter && typeof column.filter === 'function') {
                    // Usar filtro personalizado da coluna
                    matches = column.filter(item);
                } else if (workflowBased) {
                    // Lógica baseada em workflow
                    const workflowable = item.workflowables?.[0];
                    if (workflowable?.currentTemplate) {
                        matches = workflowable.currentTemplate.slug === column.id;
                    } else {
                        // Fallback para status se não tem workflow
                        matches = item.status === column.id;
                    }
                } else {
                    // Agrupamento simples por campo
                    matches = item[groupBy] === column.id;
                }
                
                if (matches) {
                    groups[column.id].push(item);
                }
            });
        });

        return groups;
    }, [filteredData, kanbanColumns, groupBy, workflowBased]);

    // Calcular estatísticas
    const stats = useMemo(() => {
        const total = filteredData.length;
        const byColumn = kanbanColumns.map((column: any) => ({
            id: column.id,
            title: column.title,
            count: groupedData[column.id]?.length || 0,
            color: column.color
        }));

        return { total, byColumn };
    }, [filteredData, kanbanColumns, groupedData]);

    // Handler para drop de item
    const handleDrop = (item: any, targetColumnId: string) => {
        console.log('Drop:', item, 'para coluna:', targetColumnId);
        
        // TODO: Implementar lógica de movimentação
        // Se workflowBased, mover entre templates
        // Se não, atualizar campo groupBy
        
        if (onAction) {
            onAction('move_item', item, { targetColumn: targetColumnId });
        }
    };

    // Handler para adicionar item
    const handleAddItem = (columnId: string) => {
        console.log('Adicionar item na coluna:', columnId);
        
        if (onAction) {
            onAction('add_item', null, { targetColumn: columnId });
        }
    };

    // Handler para ação em item
    const handleItemAction = (actionId: string, item: any) => {
        console.log('Ação em item:', actionId, item);
        
        if (onAction) {
            onAction(actionId, item);
        }
    };

    return (
        <div className={`kanban-board space-y-4 ${className}`}>
            {/* Header do Board */}
            <Card>
                <CardHeader className="pb-4">
                    <div className="flex items-center justify-between">
                        <div className="flex items-center gap-4">
                            <div>
                                <CardTitle className="text-lg">
                                    {meta.title || 'Quadro Kanban'}
                                </CardTitle>
                                <p className="text-sm text-gray-500 mt-1">
                                    {meta.description || `${stats.total} ${stats.total === 1 ? 'item' : 'itens'} total`}
                                </p>
                            </div>

                            {/* Estatísticas por coluna */}
                            {showCardCount && (
                                <div className="flex items-center gap-2">
                                    {stats.byColumn.map((stat: any) => (
                                        <Badge 
                                            key={stat.id} 
                                            variant="secondary" 
                                            className="text-xs"
                                            style={{ 
                                                backgroundColor: `${stat.color}20`,
                                                color: stat.color,
                                                borderColor: `${stat.color}40`
                                            }}
                                        >
                                            {stat.title}: {stat.count}
                                        </Badge>
                                    ))}
                                </div>
                            )}
                        </div>

                        <div className="flex items-center gap-2">
                            {/* Busca */}
                            <div className="relative">
                                <Search className="absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                <Input
                                    placeholder="Buscar itens..."
                                    value={searchTerm}
                                    onChange={(e) => setSearchTerm(e.target.value)}
                                    className="pl-8 w-64"
                                />
                            </div>

                            {/* Botões de ação */}
                            <Button variant="outline" size="sm">
                                <Filter className="h-4 w-4 mr-1" />
                                Filtros
                            </Button>

                            {onRefresh && (
                                <Button variant="outline" size="sm" onClick={onRefresh}>
                                    <RefreshCw className="h-4 w-4 mr-1" />
                                    Atualizar
                                </Button>
                            )}

                            <Button variant="outline" size="sm">
                                <Settings className="h-4 w-4 mr-1" />
                                Configurar
                            </Button>
                        </div>
                    </div>
                </CardHeader>
            </Card>

            {/* Board de colunas */}
            <div className="kanban-columns-container">
                <div className="flex gap-4 overflow-x-auto pb-4">
                    {kanbanColumns.map((column: any) => (
                        <KanbanColumn
                            key={column.id}
                            column={column}
                            items={groupedData[column.id] || []}
                            tableColumns={columns}
                            actions={actions}
                            onAddItem={handleAddItem}
                            onItemAction={handleItemAction}
                            onDrop={handleDrop}
                            allowDrop={allowDragDrop}
                        />
                    ))}
                </div>
            </div>

            {/* Estado vazio */}
            {kanbanColumns.length === 0 && (
                <Card>
                    <CardContent className="py-12">
                        <div className="text-center">
                            <div className="rounded-full bg-gray-100 p-3 w-12 h-12 mx-auto mb-4">
                                <Settings className="h-6 w-6 text-gray-400" />
                            </div>
                            <h3 className="text-lg font-medium text-gray-900 mb-2">
                                Nenhuma coluna configurada
                            </h3>
                            <p className="text-gray-500 mb-4">
                                Configure as colunas do Kanban para começar a organizar seus itens.
                            </p>
                            <Button>
                                <Settings className="h-4 w-4 mr-1" />
                                Configurar colunas
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            )}
        </div>
    );
} 
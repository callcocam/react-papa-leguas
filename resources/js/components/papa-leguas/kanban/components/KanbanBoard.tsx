import React, { useState, useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
    Search, 
    Filter, 
    Plus, 
    RefreshCw,
    Grid3X3,
    List
} from 'lucide-react';
import KanbanColumn from './KanbanColumn';
import type { KanbanBoardProps } from '../types';

/**
 * Componente Kanban Board refatorado para usar dados do backend.
 * 
 * Funcionalidades:
 * - Dados via eager loading (não API)
 * - Columns, actions e filters do backend
 * - Segue padrão dos renderers das tabelas
 * - Integração com sistema existente
 */
export default function KanbanBoard({
    data,
    columns,
    actions = [],
    filters = [],
    config = {},
    meta = {},
    onAction,
    onFilter,
    onRefresh
}: KanbanBoardProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [activeFilters, setActiveFilters] = useState<Record<string, any>>({});
    const [viewMode, setViewMode] = useState<'kanban' | 'list'>('kanban');
    const [expandedCards, setExpandedCards] = useState<Set<string>>(new Set());

    // Configurações padrão
    const {
        searchable = true,
        filterable = true,
        height = '700px',
        columnsPerRow = 4,
        dragAndDrop = false
    } = config;

    // Filtra dados por busca
    const filteredData = useMemo(() => {
        if (!searchTerm) return data;
        
        return data.filter(item => {
            // Busca em campos principais do item
            const searchableFields = ['name', 'title', 'description', 'email', 'slug'];
            return searchableFields.some(field => {
                const value = item[field];
                return value && String(value).toLowerCase().includes(searchTerm.toLowerCase());
            });
        });
    }, [data, searchTerm]);

    // Aplica filtros ativos
    const filteredAndSearchedData = useMemo(() => {
        let result = filteredData;

        // Aplica cada filtro ativo
        Object.entries(activeFilters).forEach(([filterId, filterValue]) => {
            if (filterValue === null || filterValue === undefined || filterValue === '') {
                return;
            }

            const filter = filters.find(f => f.id === filterId);
            if (!filter) return;

            result = result.filter(item => {
                const itemValue = item[filter.id];
                
                if (filter.type === 'select') {
                    return filter.multiple 
                        ? Array.isArray(filterValue) && filterValue.includes(itemValue)
                        : itemValue === filterValue;
                } else if (filter.type === 'text') {
                    return String(itemValue).toLowerCase().includes(String(filterValue).toLowerCase());
                } else if (filter.type === 'boolean') {
                    return Boolean(itemValue) === Boolean(filterValue);
                }
                
                return true;
            });
        });

        return result;
    }, [filteredData, activeFilters, filters]);

    // Agrupa dados por coluna usando os filtros das colunas
    const groupedData = useMemo(() => {
        const groups: { [key: string]: any[] } = {};
        
        // Inicializa grupos vazios
        columns.forEach(column => {
            groups[column.id] = [];
        });

        // Distribui itens pelas colunas
        filteredAndSearchedData.forEach(item => {
            columns.forEach(column => {
                // Usa filtro personalizado se existir, senão usa campo key
                const matches = column.filter 
                    ? column.filter(item)
                    : item[column.key] === column.id;
                    
                if (matches) {
                    groups[column.id].push(item);
                }
            });
        });

        return groups;
    }, [filteredAndSearchedData, columns]);

    // Calcula estatísticas
    const stats = useMemo(() => {
        const total = filteredAndSearchedData.length;
        const byColumn = columns.map(column => ({
            id: column.id,
            title: column.title,
            count: groupedData[column.id]?.length || 0
        }));

        return { total, byColumn };
    }, [filteredAndSearchedData, columns, groupedData]);

    // Toggle expansão de card
    const toggleCardExpansion = (cardId: string) => {
        const newExpanded = new Set(expandedCards);
        if (newExpanded.has(cardId)) {
            newExpanded.delete(cardId);
        } else {
            newExpanded.add(cardId);
        }
        setExpandedCards(newExpanded);
    };

    // Handler para filtros
    const handleFilterChange = (filterId: string, value: any) => {
        const newFilters = { ...activeFilters };
        
        if (value === null || value === undefined || value === '') {
            delete newFilters[filterId];
        } else {
            newFilters[filterId] = value;
        }
        
        setActiveFilters(newFilters);
        onFilter?.(newFilters);
    };

    // Handler para ações
    const handleAction = (actionId: string, item: any, extra?: any) => {
        onAction?.(actionId, item, extra);
    };

    return (
        <div className="kanban-board space-y-4">
            {/* Header do Board */}
            <div className="flex items-center justify-between bg-white p-4 rounded-lg border shadow-sm">
                <div className="flex items-center gap-4">
                    <div>
                        <h2 className="text-xl font-semibold text-gray-900">
                            {meta.title || 'Board Kanban'}
                        </h2>
                        <p className="text-sm text-gray-500">
                            {meta.description || `${stats.total} ${stats.total === 1 ? 'item' : 'itens'} total`}
                        </p>
                    </div>

                    {/* Estatísticas por coluna */}
                    <div className="flex items-center gap-2">
                        {stats.byColumn.map(stat => (
                            <Badge key={stat.id} variant="secondary" className="text-xs">
                                {stat.title}: {stat.count}
                            </Badge>
                        ))}
                    </div>
                </div>

                <div className="flex items-center gap-2">
                    {/* Busca */}
                    {searchable && (
                        <div className="relative">
                            <Search className="absolute left-2 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                            <Input
                                placeholder="Buscar..."
                                value={searchTerm}
                                onChange={(e) => setSearchTerm(e.target.value)}
                                className="pl-8 w-64"
                            />
                        </div>
                    )}

                    {/* Filtros */}
                    {filterable && filters.length > 0 && (
                        <div className="flex items-center gap-2">
                            {filters.map(filter => (
                                <div key={filter.id} className="flex items-center gap-1">
                                    {filter.type === 'select' && (
                                        <select
                                            value={activeFilters[filter.id] || ''}
                                            onChange={(e) => handleFilterChange(filter.id, e.target.value)}
                                            className="text-sm border rounded px-2 py-1"
                                        >
                                            <option value="">{filter.label}</option>
                                            {filter.options?.map(option => (
                                                <option key={option.value} value={option.value}>
                                                    {option.label}
                                                </option>
                                            ))}
                                        </select>
                                    )}
                                    
                                    {filter.type === 'text' && (
                                        <Input
                                            placeholder={filter.label}
                                            value={activeFilters[filter.id] || ''}
                                            onChange={(e) => handleFilterChange(filter.id, e.target.value)}
                                            className="w-32 text-sm"
                                        />
                                    )}
                                </div>
                            ))}
                        </div>
                    )}

                    {/* Toggle View Mode */}
                    <div className="flex items-center border rounded-md">
                        <Button
                            variant={viewMode === 'kanban' ? 'default' : 'ghost'}
                            size="sm"
                            onClick={() => setViewMode('kanban')}
                            className="rounded-r-none"
                        >
                            <Grid3X3 className="h-4 w-4" />
                        </Button>
                        <Button
                            variant={viewMode === 'list' ? 'default' : 'ghost'}
                            size="sm"
                            onClick={() => setViewMode('list')}
                            className="rounded-l-none"
                        >
                            <List className="h-4 w-4" />
                        </Button>
                    </div>

                    {/* Refresh */}
                    <Button 
                        variant="outline" 
                        size="sm"
                        onClick={onRefresh}
                    >
                        <RefreshCw className="h-4 w-4" />
                    </Button>
                </div>
            </div>

            {/* Board Content */}
            {viewMode === 'kanban' ? (
                <div 
                    className="grid gap-4"
                    style={{ 
                        gridTemplateColumns: `repeat(${Math.min(columns.length, columnsPerRow)}, 1fr)`,
                        height: height 
                    }}
                >
                    {columns.map(column => (
                        <KanbanColumn
                            key={column.id}
                            column={column}
                            data={groupedData[column.id] || []}
                            actions={actions}
                            onAction={handleAction}
                            onDrop={dragAndDrop ? (item, fromColumn, toColumn) => {
                                // Implementar drag and drop no futuro
                                console.log('Drop:', item, fromColumn, toColumn);
                            } : undefined}
                        />
                    ))}
                </div>
            ) : (
                <div className="bg-white rounded-lg border">
                    <div className="p-4">
                        <p className="text-center text-gray-500">
                            Visualização em lista será implementada em breve
                        </p>
                    </div>
                </div>
            )}

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
    );
} 
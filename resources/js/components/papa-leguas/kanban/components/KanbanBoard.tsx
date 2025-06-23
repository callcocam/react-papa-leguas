import React, { useState, useMemo } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input'; 
import { 
    Search,  
    RefreshCw,
    Grid3X3
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
        dragAndDrop = false
    } = config;

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
                        onDrop={dragAndDrop ? (item, fromColumn, toColumn) => {
                            // Implementar drag and drop no futuro
                            console.log('Drop:', item, fromColumn, toColumn);
                        } : undefined}
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
    );
} 
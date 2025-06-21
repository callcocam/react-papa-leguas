import React, { useState, useCallback } from 'react';
import { Button } from '@/components/ui/button';
import { ChevronDown, ChevronRight, Loader2, Database } from 'lucide-react';
import { cn } from '@/lib/utils';
import { type ColumnRendererProps } from '../../types';
import MiniDataTable from './MiniDataTable';
import { useNestedTableData } from '../../hooks/useNestedTableData';

/**
 * Renderizador para colunas de tabelas aninhadas/hierárquicas.
 * 
 * Funcionalidades:
 * - Expandir/recolher sub-tabela
 * - Lazy loading de dados
 * - Resumo quando recolhida
 * - Loading states elegantes
 * - Integração com API
 */
export default function NestedTableRenderer({ 
    column, 
    item, 
    value 
}: ColumnRendererProps) {
    const [isExpanded, setIsExpanded] = useState(false);
    
    // Hook customizado para gerenciar dados da sub-tabela
    const {
        data: nestedData,
        loading,
        error,
        pagination,
        config,
        columns: nestedColumns,
        actions: nestedActions,
        loadData,
        refetch
    } = useNestedTableData({
        parentId: item.id,
        nestedTableClass: column.nested_table_class,
        loadOnExpand: column.load_on_expand,
        enabled: isExpanded || !column.load_on_expand
    });

    // Toggle expansão/recolhimento
    const handleToggle = useCallback(async () => {
        const newExpanded = !isExpanded;
        setIsExpanded(newExpanded);
        
        // Se está expandindo e deve carregar dados
        if (newExpanded && column.load_on_expand && !nestedData) {
            await loadData();
        }
    }, [isExpanded, column.load_on_expand, nestedData, loadData]);

    // Ícones baseados no estado
    const getIcon = () => {
        if (loading) {
            return <Loader2 className="h-4 w-4 animate-spin" />;
        }
        
        if (isExpanded) {
            return <ChevronDown className="h-4 w-4" />;
        }
        
        return <ChevronRight className="h-4 w-4" />;
    };

    // Resumo quando recolhida
    const getSummary = () => {
        if (column.summary) {
            return column.summary;
        }
        
        // Resumo baseado nos dados
        if (nestedData && Array.isArray(nestedData)) {
            const count = nestedData.length;
            return count === 0 ? 'Nenhum item' : `${count} ${count === 1 ? 'item' : 'itens'}`;
        }
        
        // Resumo baseado no valor da coluna
        if (value && Array.isArray(value)) {
            const count = value.length;
            return count === 0 ? 'Nenhum item' : `${count} ${count === 1 ? 'item' : 'itens'}`;
        }
        
        return 'Expandir';
    };

    return (
        <div className="nested-table-container">
            {/* Botão de expansão/recolhimento */}
            <Button
                variant="ghost"
                size="sm"
                onClick={handleToggle}
                disabled={loading}
                className={cn(
                    "flex items-center gap-2 h-8 px-2 text-xs",
                    "hover:bg-accent hover:text-accent-foreground",
                    "transition-colors duration-200"
                )}
            >
                {getIcon()}
                <Database className="h-3 w-3 opacity-60" />
                <span className="font-medium">{getSummary()}</span>
            </Button>

            {/* Conteúdo da sub-tabela (quando expandida) */}
            {isExpanded && (
                <div className={cn(
                    "nested-table-content mt-3 ml-6",
                    "border-l-2 border-border/40 pl-4",
                    "animate-in slide-in-from-top-2 duration-200"
                )}>
                    {/* Estado de loading */}
                    {loading && (
                        <div className="flex items-center justify-center py-8 text-muted-foreground">
                            <Loader2 className="h-5 w-5 animate-spin mr-2" />
                            <span className="text-sm">Carregando dados...</span>
                        </div>
                    )}

                    {/* Estado de erro */}
                    {error && (
                        <div className="rounded-md bg-destructive/10 border border-destructive/20 p-3">
                            <div className="flex items-center gap-2 text-destructive text-sm">
                                <span className="font-medium">Erro ao carregar dados:</span>
                                <span>{error}</span>
                            </div>
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={refetch}
                                className="mt-2 h-7 text-xs"
                            >
                                Tentar novamente
                            </Button>
                        </div>
                    )}

                    {/* Sub-tabela com dados */}
                    {!loading && !error && nestedData && (
                        <MiniDataTable
                            data={nestedData}
                            columns={nestedColumns || []}
                            actions={nestedActions || []}
                            pagination={pagination}
                            config={config}
                            parentId={item.id}
                            onRefresh={refetch}
                        />
                    )}

                    {/* Estado vazio */}
                    {!loading && !error && nestedData && Array.isArray(nestedData) && nestedData.length === 0 && (
                        <div className="text-center py-6 text-muted-foreground">
                            <Database className="h-8 w-8 mx-auto mb-2 opacity-40" />
                            <p className="text-sm">Nenhum item encontrado</p>
                        </div>
                    )}
                </div>
            )}
        </div>
    );
} 
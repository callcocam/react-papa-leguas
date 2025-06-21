import React, { useState, useEffect } from 'react';
import { ChevronRight, ChevronDown, Loader2, Database } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { RendererProps } from '../../types';
import { useNestedTableData } from '../../hooks/useNestedTableData';
import MiniDataTable from './MiniDataTable';

/**
 * Renderizador para colunas de tabelas aninhadas/hierárquicas
 * 
 * Este componente permite que uma linha da tabela principal
 * expanda para mostrar uma sub-tabela configurável com seus próprios
 * dados, colunas, ações e funcionalidades.
 * 
 * Funcionalidades:
 * - Expansão/recolhimento suave
 * - Lazy loading de dados
 * - Cache inteligente
 * - Estados visuais (loading, error, vazio)
 * - Resumo quando recolhida
 * - Sub-tabela completa quando expandida
 */
export default function NestedTableRenderer({ value, item, column }: RendererProps) {
    const [isExpanded, setIsExpanded] = useState(false);
    
    // Configurações da coluna aninhada - acessar diretamente as propriedades
    const nested_table_class = (column as any).nested_table_class;
    const relationship = (column as any).relationship;
    const load_on_expand = (column as any).load_on_expand ?? true;
    const config = (column as any).nested_config || {};
    const icons = (column as any).icons || {};
    
    const {
        summary_text,
        expanded_icon = icons.expanded || 'ChevronDown',
        collapsed_icon = icons.collapsed || 'ChevronRight',
        loading_icon = icons.loading || 'Loader2'
    } = config;

    // Hook para gerenciar dados da sub-tabela
    const hookParams = {
        nestedTableClass: nested_table_class,
        parentId: item?.id,
        enabled: isExpanded && load_on_expand,
        loadOnExpand: load_on_expand
    };
    
    const {
        data,
        loading,
        error,
        pagination,
        config: tableConfig,
        columns,
        actions,
        loadData,
        refetch
    } = useNestedTableData(hookParams);
    
    // Carrega dados quando expandir (se load_on_expand for true)
    useEffect(() => {
        if (isExpanded && load_on_expand && nested_table_class && item?.id) {
            loadData();
        }
    }, [isExpanded, load_on_expand, nested_table_class, item?.id, loadData]);
    // Função para alternar expansão
    const toggleExpanded = () => {
        setIsExpanded(!isExpanded);
    };

    // Renderizar resumo quando recolhida
    const renderSummary = () => {
        if (summary_text) {
            return String(summary_text);
        }

        // Resumo padrão baseado no valor ou dados carregados  
        if (data && Array.isArray(data) && data.length > 0) {
            const count = data.length;
            return `${count} ${count === 1 ? 'item' : 'itens'}`;
        }

        if (value !== undefined && value !== null) {
            if (typeof value === 'number') {
                return `${value} ${value === 1 ? 'item' : 'itens'}`;
            }
            if (typeof value === 'string') {
                return String(value);
            }
        }

        return 'Ver detalhes';
    };

    // Ícones dinâmicos
    const IconComponent = isExpanded 
        ? (expanded_icon === 'ChevronDown' ? ChevronDown : ChevronDown)
        : (collapsed_icon === 'ChevronRight' ? ChevronRight : ChevronRight);
    
    const LoadingIcon = loading_icon === 'Loader2' ? Loader2 : Loader2;

    return (
        <div className="relative">
            {/* Botão de expansão/recolhimento */}
            <Button
                variant="ghost"
                size="sm"
                onClick={toggleExpanded}
                className="flex items-center gap-2 h-8 px-2 text-sm"
                disabled={loading}
            >
                {loading ? (
                    <LoadingIcon className="h-4 w-4 animate-spin" />
                ) : (
                    <IconComponent className="h-4 w-4" />
                )}
                
                <span className="text-left">
                    {renderSummary()}
                </span>
            </Button>

            {/* Sub-tabela expandida - Posicionada absolutamente para "escapar" da célula */}
            {isExpanded && (
                <div className="absolute top-full left-0 right-0 z-50 mt-1">
                    <div 
                        className="bg-white border border-slate-300 rounded-lg shadow-lg animate-in slide-in-from-top-2 duration-200"
                        style={{
                            minWidth: '800px',
                            marginLeft: '-200px', // Ajuste para centralizar melhor
                        }}
                    >
                        <div className="p-4">
                            {loading && (
                                <div className="flex items-center justify-center py-8 text-sm text-muted-foreground">
                                    <Loader2 className="mr-2 h-4 w-4 animate-spin" />
                                    Carregando dados...
                                </div>
                            )}

                            {error && (
                                <div className="flex flex-col items-center justify-center py-8 text-sm">
                                    <div className="text-destructive mb-2">
                                        Erro ao carregar dados
                                    </div>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={refetch}
                                        className="text-xs"
                                    >
                                        Tentar novamente
                                    </Button>
                                </div>
                            )}

                            {!loading && !error && data && Array.isArray(data) && data.length > 0 && (
                                <>
                                    <div className="flex items-center justify-between mb-4 pb-3 border-b border-slate-200">
                                        <div className="flex items-center gap-2">
                                            <Database className="h-4 w-4 text-slate-600" />
                                            <div className="text-sm font-semibold text-slate-700">
                                                Posts do usuário
                                            </div>
                                            <span className="text-xs text-slate-500 bg-slate-200 px-2 py-1 rounded-full">
                                                {data.length} itens
                                            </span>
                                        </div>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            onClick={toggleExpanded}
                                            className="h-6 w-6 p-0 text-slate-400 hover:text-slate-600"
                                        >
                                            ✕
                                        </Button>
                                    </div>
                                    
                                    <MiniDataTable
                                        data={data}
                                        columns={columns || []}
                                        actions={actions || []}
                                        pagination={pagination}
                                        config={tableConfig}
                                        parentId={String(item?.id || '')}
                                        onRefresh={refetch}
                                    />
                                </>
                            )}

                            {!loading && !error && (!data || data.length === 0) && (
                                <div className="flex flex-col items-center justify-center py-12 text-sm text-slate-500">
                                    <Database className="h-12 w-12 mb-3 opacity-40 text-slate-400" />
                                    <div className="font-medium">Nenhum post encontrado</div>
                                    <div className="text-xs text-slate-400 mt-1">
                                        Usuário não possui posts publicados
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
} 
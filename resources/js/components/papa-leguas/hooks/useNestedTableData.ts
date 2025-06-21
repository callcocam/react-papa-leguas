import { useState, useEffect, useCallback, useRef } from 'react';
import { router } from '@inertiajs/react';

interface UseNestedTableDataProps {
    parentId: string | number;
    nestedTableClass: string;
    loadOnExpand?: boolean;
    enabled?: boolean;
}

interface NestedTableData {
    data: any[] | null;
    loading: boolean;
    error: string | null;
    pagination: any;
    config: any;
    columns: any[];
    actions: any[];
    loadData: () => Promise<void>;
    refetch: () => Promise<void>;
}

/**
 * Hook para gerenciar dados de tabelas aninhadas/hierárquicas.
 * 
 * Funcionalidades:
 * - Lazy loading configurável
 * - Cache de dados
 * - Estados de loading/error
 * - Refetch automático
 * - Integração com API do Laravel
 */
export function useNestedTableData({
    parentId,
    nestedTableClass,
    loadOnExpand = true,
    enabled = true
}: UseNestedTableDataProps): NestedTableData {
    const [data, setData] = useState<any[] | null>(null);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const [pagination, setPagination] = useState(null);
    const [config, setConfig] = useState(null);
    const [columns, setColumns] = useState<any[]>([]);
    const [actions, setActions] = useState<any[]>([]);
    
    // Cache para evitar requisições desnecessárias
    const cacheRef = useRef<Map<string, any>>(new Map());
    const abortControllerRef = useRef<AbortController | null>(null);

    // Gera chave única para cache
    const getCacheKey = useCallback((params: any = {}) => {
        return `${nestedTableClass}-${parentId}-${JSON.stringify(params)}`;
    }, [nestedTableClass, parentId]);

    // Carrega dados da API
    const loadData = useCallback(async (params: any = {}) => {
        if (!enabled || !parentId || !nestedTableClass) {
            return;
        }

        const cacheKey = getCacheKey(params);
        
        // Verifica cache primeiro
        if (cacheRef.current.has(cacheKey)) {
            const cachedData = cacheRef.current.get(cacheKey);
            setData(cachedData.data);
            setPagination(cachedData.pagination);
            setConfig(cachedData.config);
            setColumns(cachedData.columns);
            setActions(cachedData.actions);
            return;
        }

        // Cancela requisição anterior se existir
        if (abortControllerRef.current) {
            abortControllerRef.current.abort();
        }

        abortControllerRef.current = new AbortController();
        setLoading(true);
        setError(null);

        try {
            const response = await fetch('/api/nested-tables/data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    nested_table_class: nestedTableClass,
                    parent_id: parentId,
                    ...params
                }),
                signal: abortControllerRef.current.signal
            });

            if (!response.ok) {
                throw new Error(`Erro ${response.status}: ${response.statusText}`);
            }

            const result = await response.json();

            if (result.success) {
                const responseData = {
                    data: result.data || [],
                    pagination: result.pagination || null,
                    config: result.config || null,
                    columns: result.columns || [],
                    actions: result.actions || []
                };

                // Atualiza estado
                setData(responseData.data);
                setPagination(responseData.pagination);
                setConfig(responseData.config);
                setColumns(responseData.columns);
                setActions(responseData.actions);

                // Salva no cache
                cacheRef.current.set(cacheKey, responseData);
            } else {
                throw new Error(result.message || 'Erro desconhecido');
            }
        } catch (err: any) {
            if (err.name !== 'AbortError') {
                console.error('Erro ao carregar dados da sub-tabela:', err);
                setError(err.message || 'Erro ao carregar dados');
            }
        } finally {
            setLoading(false);
            abortControllerRef.current = null;
        }
    }, [enabled, parentId, nestedTableClass, getCacheKey]);

    // Refetch - limpa cache e recarrega
    const refetch = useCallback(async (params: any = {}) => {
        const cacheKey = getCacheKey(params);
        cacheRef.current.delete(cacheKey);
        await loadData(params);
    }, [loadData, getCacheKey]);

    // Carrega dados iniciais se não for lazy loading
    useEffect(() => {
        if (enabled && !loadOnExpand) {
            loadData();
        }
    }, [enabled, loadOnExpand, loadData]);

    // Cleanup ao desmontar
    useEffect(() => {
        return () => {
            if (abortControllerRef.current) {
                abortControllerRef.current.abort();
            }
        };
    }, []);

    return {
        data,
        loading,
        error,
        pagination,
        config,
        columns,
        actions,
        loadData,
        refetch
    };
} 
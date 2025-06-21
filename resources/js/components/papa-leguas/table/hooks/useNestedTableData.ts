import { useState, useEffect, useCallback, useRef } from 'react';
import { router } from '@inertiajs/react';
import axios from 'axios';

// Configuração global do axios para Laravel
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true; 
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
        console.log('🔍 useNestedTableData loadData:', {
            enabled,
            parentId,
            nestedTableClass,
            params,
            conditions: {
                enabled: !!enabled,
                parentId: !!parentId,
                nestedTableClass: !!nestedTableClass
            }
        });
        
        if (!enabled || !parentId || !nestedTableClass) {
            console.log('❌ useNestedTableData: Condições não atendidas', {
                enabled,
                parentId,
                nestedTableClass
            });
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
            const response = await axios.post('/api/nested-tables/data', {
                nested_table_class: nestedTableClass,
                parent_id: parentId,
                ...params
            }, {
                signal: abortControllerRef.current.signal,
                timeout: 10000, // 10 segundos de timeout
            });

            const result = response.data;

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
            if (axios.isCancel(err)) {
                console.log('Requisição cancelada');
                return;
            }
            
            console.error('Erro ao carregar dados da sub-tabela:', err);
            
            // Tratamento específico para erros do axios
            if (err.response) {
                // Servidor respondeu com status de erro
                const status = err.response.status;
                const message = err.response.data?.message || `Erro ${status}`;
                setError(`${message} (${status})`);
            } else if (err.request) {
                // Requisição foi feita mas não houve resposta
                setError('Erro de conexão - servidor não respondeu');
            } else {
                // Erro na configuração da requisição
                setError(err.message || 'Erro ao configurar requisição');
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
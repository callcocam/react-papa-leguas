import { useState, useCallback, useRef } from 'react';
import axios from 'axios';

interface UseKanbanDataProps {
    /** Classe da tabela aninhada para buscar dados filhos */
    nestedTableClass: string;
    /** Dados pai para cache */
    parentData: any[];
}

interface UseKanbanDataReturn {
    /** Função para buscar dados filhos de um item pai */
    getChildData: (parentId: string | number) => Promise<any[]>;
    /** Estado de loading global */
    loading: boolean;
    /** Erro global */
    error: string | null;
    /** Função para refresh geral */
    refreshData: () => void;
}

/**
 * Hook para gerenciar dados do Kanban.
 * 
 * Funcionalidades:
 * - Reutiliza a infraestrutura de nested tables
 * - Cache inteligente de dados filhos
 * - Lazy loading sob demanda
 * - Tratamento de erros
 * - Estados de loading por item
 * 
 * Integração:
 * - Usa as mesmas rotas e controllers das tabelas
 * - Mantém compatibilidade com sistema existente
 * - Suporte a diferentes tipos de dados hierárquicos
 */
export function useKanbanData({
    nestedTableClass,
    parentData
}: UseKanbanDataProps): UseKanbanDataReturn {
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    
    // Cache de dados filhos por parentId
    const childDataCache = useRef<Map<string | number, any[]>>(new Map());
    
    // Cache de promises para evitar requests duplicados
    const loadingPromises = useRef<Map<string | number, Promise<any[]>>>(new Map());

    /**
     * Busca dados filhos de um item pai específico.
     * Implementa cache e evita requests duplicados.
     */
    const getChildData = useCallback(async (parentId: string | number): Promise<any[]> => {
        // Verifica cache primeiro
        if (childDataCache.current.has(parentId)) {
            return childDataCache.current.get(parentId) || [];
        }

        // Verifica se já existe uma promise em andamento
        if (loadingPromises.current.has(parentId)) {
            return loadingPromises.current.get(parentId) || Promise.resolve([]);
        }

        // Cria nova promise para buscar dados
        const loadPromise = fetchChildData(parentId);
        loadingPromises.current.set(parentId, loadPromise);

        try {
            const data = await loadPromise;
            
            // Salva no cache
            childDataCache.current.set(parentId, data);
            
            // Remove da lista de promises em andamento
            loadingPromises.current.delete(parentId);
            
            return data;
        } catch (err) {
            // Remove da lista de promises em andamento em caso de erro
            loadingPromises.current.delete(parentId);
            throw err;
        }
    }, [nestedTableClass]);

    /**
     * Faz o request real para buscar dados filhos.
     */
    const fetchChildData = async (parentId: string | number): Promise<any[]> => {
        try {
            setError(null);

            const response = await axios.post('/nested-table-data', {
                nested_table_class: nestedTableClass,
                parent_id: parentId,
                // Configurações padrão para Kanban
                per_page: 50, // Mais itens para Kanban
                page: 1,
                search: '',
                sort_by: 'created_at',
                sort_direction: 'desc'
            }, {
                timeout: 10000,
                withCredentials: true,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            if (response.data?.success && response.data?.data) {
                return response.data.data;
            } else {
                console.warn('Resposta inesperada da API:', response.data);
                return [];
            }
        } catch (err: any) {
            const errorMessage = err.response?.data?.message || err.message || 'Erro ao carregar dados';
            console.error(`Erro ao buscar dados filhos para parent ${parentId}:`, err);
            
            // Define erro global apenas se for um erro crítico
            if (err.response?.status >= 500) {
                setError(errorMessage);
            }
            
            throw new Error(errorMessage);
        }
    };

    /**
     * Limpa cache e força refresh de todos os dados.
     */
    const refreshData = useCallback(() => {
        // Limpa caches
        childDataCache.current.clear();
        loadingPromises.current.clear();
        
        // Limpa estados
        setError(null);
        setLoading(false);
        
        console.log('Cache de dados Kanban limpo');
    }, []);

    return {
        getChildData,
        loading,
        error,
        refreshData
    };
} 
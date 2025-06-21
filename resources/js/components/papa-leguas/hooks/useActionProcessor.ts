import { useState } from 'react';
import axios from 'axios';
import { useToast } from '../../../hooks/use-toast';
import { type TableAction } from '../types';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

interface ProcessActionPayload {
    table: string;
    actionKey: string;
    actionType?: TableAction['type'];
    item: any; // Pode ser um objeto completo ou objeto com selectedIds
    data?: Record<string, any>;
}

export const useActionProcessor = () => {
    const [isLoading, setIsLoading] = useState(false);
    const { success, error, warning, info } = useToast();

    const processAction = async (payload: ProcessActionPayload) => {
        setIsLoading(true);

        const isBulk = payload.actionType === 'bulk';
        const endpoint = isBulk
            ? `/api/${payload.table}/actions/${payload.actionKey}/bulk-execute`
            : `/api/${payload.table}/actions/${payload.actionKey}/execute`;

        const body = isBulk
            ? { item_ids: payload.item.selectedIds, data: payload.data }
            : { item_id: payload.item?.id || payload.item, data: payload.data };

        // Debug log para desenvolvimento (pode ser removido em produção)
        if (process.env.NODE_ENV === 'development') {
            console.log('🔧 useActionProcessor Debug:', {
                endpoint,
                body,
                payload: {
                    table: payload.table,
                    actionKey: payload.actionKey,
                    actionType: payload.actionType,
                    itemType: typeof payload.item,
                    itemId: payload.item?.id,
                }
            });
        }

        try {
            const response = await axios.post(endpoint, body, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            });
            
            const result = response.data;
            
            // Mostrar feedback visual baseado na resposta
            if (result.success) {
                success(
                    result.message || 'Ação executada com sucesso!',
                    result.description
                );
            } else if (result.warning) {
                warning(
                    result.message || 'Atenção!',
                    result.description
                );
            } else if (result.info) {
                info(
                    result.message || 'Informação',
                    result.description
                );
            }
            
            return result;
        } catch (err: any) {
            console.error('Erro ao processar a ação:', err);
            const message = err.response?.data?.message || err.message || 'Falha ao processar a ação.';
            
            // Mostrar toast de erro
            error(
                'Erro ao processar ação',
                message
            );
            
            return { success: false, message };
        } finally {
            setIsLoading(false);
        }
    };

    return { processAction, isLoading };
}; 
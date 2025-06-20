import { useState } from 'react';
import axios from 'axios';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

interface UseActionProcessorPayload {
    table: string;
    actionKey: string;
    item: { id: any };
    data?: Record<string, any>;
}

export const useActionProcessor = () => {
    const [isLoading, setIsLoading] = useState(false);


    const processAction = async (payload: UseActionProcessorPayload) => {
        setIsLoading(true);
        console.log('payload:', payload.table);
        try {
            const response = await axios.post(
                `/api/${payload.table}/actions/${payload.actionKey}/execute`,
                {
                    item_id: payload.item.id,
                    data: payload.data,
                },
                {
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                }
            );

            return response.data;

        } catch (error: any) {
            console.error('Erro ao processar a ação:', error);
            const message = error.response?.data?.message || error.message || 'Falha ao processar a ação.';
            return {
                success: false,
                message,
            };
        } finally {
            setIsLoading(false);
        }
    };

    return { processAction, isLoading };
}; 
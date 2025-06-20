import { useState } from 'react';
import { TableAction, PapaLeguasTableProps } from '../types';

interface ActionPayload {
    actionKey: string;
    item: any;
    data?: Record<string, any>;
}

export const useActionProcessor = () => {
    const [isLoading, setIsLoading] = useState(false);
    const [error, setError] = useState<string | null>(null);

    const processAction = async (payload: ActionPayload) => {
        setIsLoading(true);
        setError(null);

        try {
            const response = await fetch(`/api/actions/${payload.actionKey}/execute`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: JSON.stringify({
                    item_id: payload.item.id,
                    data: payload.data,
                }),
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Falha ao processar a ação.');
            }
            
            return { success: true, ...result };

        } catch (err: any) {
            setError(err.message);
            return { success: false, message: err.message };
        } finally {
            setIsLoading(false);
        }
    };

    return { processAction, isLoading, error };
}; 
import React, { useContext } from 'react';
import { Button } from '@/components/ui/button';
import { type ActionRendererProps } from '../../types';
import { useActionProcessor } from '../../hooks/useActionProcessor';
import { TableContext } from '../../contexts/TableContext';
import { router } from '@inertiajs/react';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

/**
 * Renderizador de Ação Callback
 * Usado para ações customizadas que executam callbacks no backend
 */
export default function CallbackActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    const context = useContext(TableContext);
    const { processAction, isLoading } = useActionProcessor();

    const handleClick = async (e: React.MouseEvent) => {
        e.stopPropagation();
        try {
            // Confirmação se necessária
            if (action.confirmation) {
                if (!confirm(action.confirmation.message)) {
                    return;
                }
            }

            // Executar callback customizado do frontend se fornecido
            if (action.onClick) {
                action.onClick(item);
                return;
            }

            // Garantir que temos as informações necessárias
            if (!context.meta?.key) {
                console.error("❌ A chave da tabela (meta.key) não foi encontrada no contexto.");
                alert("Erro de configuração: Chave da tabela ausente.");
                return;
            }

            // Usar o hook para processar a ação no backend
            const result = await processAction({
                table: context.meta.key,
                actionKey: action.key,
                item: item,
                data: action.data || {},
            });

            if (result && result.success) {
                // Ao sucesso, simplesmente recarrega os dados da página via Inertia
                router.visit(window.location.href, { 
                    preserveScroll: true,
                    onSuccess: () => {
                        // Opcional: mostrar notificação de sucesso
                    }
                });
                
            } else {
                // Erro - mostrar mensagem de erro
                const errorMessage = result?.message || 'Erro ao executar ação';
                console.error('❌ Erro na execução da ação:', errorMessage);
                alert(errorMessage);
            }
        } catch (error) {
            console.error('❌ Erro ao executar callback:', error);
            alert('Erro interno ao executar ação');
        }
    };

    return (
        <TooltipProvider>
            <Tooltip>
                <TooltipTrigger asChild>
                    <Button
                        variant={action.variant || 'ghost'}
                        size="icon"
                        onClick={handleClick}
                        disabled={action.disabled || isLoading}
                        className={action.className}
                    >
                        {IconComponent && <IconComponent className="h-4 w-4" />}
                        <span className="sr-only">{action.label}</span>
                    </Button>
                </TooltipTrigger>
                <TooltipContent>
                    <p>{action.tooltip || action.label}</p>
                </TooltipContent>
            </Tooltip>
        </TooltipProvider>
    );
} 
import React, { useContext } from 'react';
import { Button } from '@/components/ui/button';
import { type ActionRendererProps } from '../../types';
import { useActionProcessor } from '../../hooks/useActionProcessor';
import { TableContext } from '../../contexts/TableContext';

/**
 * Renderizador de Ação Callback
 * Usado para ações customizadas que executam callbacks no backend
 */
export default function CallbackActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    const context = useContext(TableContext);
    const { processAction, isLoading } = useActionProcessor();

    const handleClick = async () => {
        try {
            // Confirmação se necessária
            if (action.confirmMessage) {
                if (!confirm(action.confirmMessage)) {
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
                // Sucesso - pode mostrar notificação ou recarregar página
                console.log('✅ Ação executada com sucesso:', result.message, action.key);

                // Se o backend mandar o item atualizado, atualizamos o estado
                if (result.item && context.setTableData) {
                    context.setTableData(prevData =>
                        prevData.map(row =>
                            row.id === result.item.id ? result.item : row
                        )
                    );
                }
                // Se o backend explicitamente pedir reload, fazemos
                else if (result.reload === true) {
                    window.location.reload();
                }
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

    // Definir variante baseada no tipo de ação
    let variant: "default" | "destructive" | "outline" | "secondary" | "ghost" | "link" = 'outline';
    
    if (action.variant) {
        variant = action.variant;
    } else if (action.type === 'delete') {
        variant = 'destructive';
    } else if (action.type === 'primary') {
        variant = 'default';
    }

    // Mapear tamanhos válidos para o Button
    const buttonSize = action.size === 'md' ? 'sm' : (action.size || 'sm');

    return (
        <Button
            variant={variant}
            size={buttonSize}
            onClick={handleClick}
            disabled={action.disabled || isLoading}
            className={action.className}
            title={action.tooltip || action.label}
        >
            {IconComponent && <IconComponent className="mr-1" />}
            <span>{action.label}</span>
        </Button>
    );
} 
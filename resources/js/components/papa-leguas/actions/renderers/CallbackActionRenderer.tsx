import React from 'react';
import { Button } from '@/components/ui/button';
import { type ActionRendererProps } from '../../types';

/**
 * Renderizador de Ação Callback
 * Usado para ações customizadas que executam callbacks no backend
 */
export default function CallbackActionRenderer({ action, item, IconComponent }: ActionRendererProps) {
    const handleClick = async () => {
        try {
            // Confirmação se necessária
            if (action.confirmMessage) {
                const confirmed = confirm(action.confirmMessage);
                if (!confirmed) return;
            }

            // Executar callback customizado se fornecido
            if (action.onClick) {
                action.onClick(item);
                return;
            }

            // Fazer requisição para executar callback no backend
            const response = await fetch(`/api/actions/${action.key}/execute`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },
                body: JSON.stringify({
                    item_id: item.id,
                }),
            });

            const result = await response.json();
            
            if (result.success) {
                // Sucesso - pode mostrar notificação ou recarregar página
                console.log('✅ Ação executada com sucesso:', result.message);
                
                // Recarregar página para refletir mudanças
                if (result.reload !== false) {
                    window.location.reload();
                }
            } else {
                // Erro - mostrar mensagem de erro
                console.error('❌ Erro na execução da ação:', result.message);
                alert(result.message || 'Erro ao executar ação');
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
            disabled={action.disabled}
            className={action.className}
            title={action.tooltip || action.label}
        >
            {IconComponent && <IconComponent className="mr-1" />}
            <span>{action.label}</span>
        </Button>
    );
} 
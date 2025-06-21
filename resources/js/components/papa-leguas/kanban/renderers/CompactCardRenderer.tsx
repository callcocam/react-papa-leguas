import React from 'react';
import { Card } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { MoreVertical } from 'lucide-react';
import { Icons } from '../../icons';

interface CompactCardRendererProps {
    /** Item de dados */
    item: any;
    /** Configuração da coluna */
    column: any;
    /** Ações disponíveis */
    actions?: any[];
    /** Callback para ações */
    onAction?: (actionId: string, item: any) => void;
    /** Configurações do renderer */
    config?: {
        titleField?: string;
        statusField?: string;
        showId?: boolean;
        showActions?: boolean;
    };
}

/**
 * Renderer compacto para cards do Kanban.
 * 
 * Ideal para:
 * - Boards com muitos itens
 * - Visualização rápida
 * - Economia de espaço
 * - Foco em informações essenciais
 */
export default function CompactCardRenderer({
    item,
    column,
    actions = [],
    onAction,
    config = {}
}: CompactCardRendererProps) {
    const {
        titleField = 'name',
        statusField = 'status',
        showId = false,
        showActions = true
    } = config;

    const title = item[titleField] || item.title || `Item ${item.id}`;
    const status = item[statusField] || item.email_verified_at;

    return (
        <Card 
            className="kanban-card-compact group p-3 transition-all duration-200 hover:shadow-sm cursor-pointer"
            style={{
                borderLeftColor: column.color || '#e5e7eb',
                borderLeftWidth: '2px'
            }}
        >
            <div className="flex items-center justify-between">
                <div className="flex-1 min-w-0">
                    {/* ID (se habilitado) */}
                    {showId && (
                        <span className="text-xs text-gray-400 font-mono">
                            #{item.id}
                        </span>
                    )}

                    {/* Título */}
                    <p className="text-sm font-medium text-gray-900 line-clamp-1">
                        {title}
                    </p>

                    {/* Status badge */}
                    {status && (
                        <Badge variant="secondary" className="text-xs mt-1">
                            {typeof status === 'string' ? status : 'Ativo'}
                        </Badge>
                    )}
                </div>

                {/* Ações compactas */}
                {showActions && actions.length > 0 && (
                    <div className="opacity-0 group-hover:opacity-100 transition-opacity">
                        <Button
                            variant="ghost"
                            size="sm"
                            className="h-6 w-6 p-0"
                            onClick={() => {
                                // Implementar menu de ações
                                console.log('Actions for:', item);
                            }}
                        >
                            <MoreVertical className="h-3 w-3" />
                        </Button>
                    </div>
                )}
            </div>
        </Card>
    );
} 
import React from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Calendar, User, Tag, MoreVertical } from 'lucide-react';
import { Icons } from '../../icons';

interface CardRendererProps {
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
        showAvatar?: boolean;
        showDate?: boolean;
        showBadges?: boolean;
        maxBadges?: number;
        dateFormat?: 'short' | 'long';
        avatarField?: string;
        titleField?: string;
        subtitleField?: string;
        statusField?: string;
    };
}

/**
 * Renderer de card genérico para o Kanban.
 * 
 * Funcionalidades:
 * - Renderização configurável de campos
 * - Suporte a avatars, badges, datas
 * - Ações integradas do backend
 * - Formatação inteligente de dados
 * - Temas por coluna
 */
export default function CardRenderer({
    item,
    column,
    actions = [],
    onAction,
    config = {}
}: CardRendererProps) {
    const {
        showAvatar = true,
        showDate = true,
        showBadges = true,
        maxBadges = 3,
        dateFormat = 'short',
        avatarField = 'avatar',
        titleField = 'name',
        subtitleField = 'email',
        statusField = 'status'
    } = config;

    // Extrai dados do item
    const title = item[titleField] || item.title || item.description || `Item ${item.id}`;
    const subtitle = item[subtitleField] || item.slug || '';
    const status = item[statusField] || item.email_verified_at;
    const avatar = item[avatarField];
    const date = item.created_at || item.updated_at;

    // Renderiza avatar/inicial
    const renderAvatar = () => {
        if (!showAvatar) return null;

        if (avatar) {
            return (
                <img
                    src={avatar}
                    alt={title}
                    className="w-8 h-8 rounded-full object-cover"
                />
            );
        }

        // Inicial do nome
        const initial = title.charAt(0).toUpperCase();
        return (
            <div className="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-sm font-medium text-gray-600">
                {initial}
            </div>
        );
    };

    // Renderiza badges inteligentes
    const renderBadges = () => {
        if (!showBadges) return null;

        const badges = [];

        // Badge de status
        if (status) {
            badges.push({
                key: 'status',
                label: typeof status === 'string' ? status : 'Ativo',
                variant: 'secondary' as const
            });
        }

        // Badge de contagem (posts, produtos, etc.)
        Object.keys(item).forEach(key => {
            if (key.endsWith('_count') && typeof item[key] === 'number') {
                const label = key.replace('_count', '');
                badges.push({
                    key,
                    label: `${item[key]} ${label}`,
                    variant: 'outline' as const
                });
            }
        });

        // Badge de categoria/relacionamento
        if (item.category) {
            badges.push({
                key: 'category',
                label: item.category.name,
                variant: 'outline' as const
            });
        }

        return badges.slice(0, maxBadges).map(badge => (
            <Badge key={badge.key} variant={badge.variant} className="text-xs">
                {badge.label}
            </Badge>
        ));
    };

    // Renderiza data formatada
    const renderDate = () => {
        if (!showDate || !date) return null;

        const dateObj = new Date(date);
        const formatted = dateFormat === 'long' 
            ? dateObj.toLocaleDateString('pt-BR', { 
                day: '2-digit', 
                month: 'short', 
                year: 'numeric' 
            })
            : dateObj.toLocaleDateString('pt-BR');

        return (
            <div className="flex items-center gap-1 text-xs text-gray-400">
                <Calendar className="h-3 w-3" />
                <span>{formatted}</span>
            </div>
        );
    };

    // Renderiza ações do item
    const renderActions = () => {
        if (!actions.length) return null;

        const visibleActions = actions.filter(action => {
            if (typeof action.visible === 'function') {
                return action.visible(item);
            }
            return action.visible !== false;
        });

        return (
            <div className="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                {visibleActions.slice(0, 2).map(action => {
                    const IconComponent = action.icon && typeof action.icon === 'string'
                        ? (Icons[action.icon as keyof typeof Icons] as any)
                        : null;

                    return (
                        <Button
                            key={action.id}
                            variant="ghost"
                            size="sm"
                            className="h-6 w-6 p-0"
                            onClick={() => onAction?.(action.id, item)}
                            title={action.tooltip || action.label}
                        >
                            {IconComponent ? (
                                <IconComponent className="h-3 w-3" />
                            ) : (
                                <span className="text-xs">{action.label.charAt(0)}</span>
                            )}
                        </Button>
                    );
                })}

                {visibleActions.length > 2 && (
                    <Button
                        variant="ghost"
                        size="sm"
                        className="h-6 w-6 p-0"
                        onClick={() => {
                            // Implementar menu dropdown no futuro
                            console.log('More actions for:', item);
                        }}
                    >
                        <MoreVertical className="h-3 w-3" />
                    </Button>
                )}
            </div>
        );
    };

    return (
        <Card 
            className="kanban-card group transition-all duration-200 hover:shadow-md cursor-pointer"
            style={{
                borderLeftColor: column.color || '#e5e7eb',
                borderLeftWidth: '3px'
            }}
        >
            <CardHeader className="pb-2">
                <div className="flex items-start gap-3">
                    {/* Avatar */}
                    {renderAvatar()}

                    {/* Conteúdo principal */}
                    <div className="flex-1 min-w-0">
                        <div className="flex items-start justify-between">
                            <div className="flex-1 min-w-0">
                                {/* Título */}
                                <h3 className="font-medium text-sm text-gray-900 line-clamp-2 mb-1">
                                    {title}
                                </h3>

                                {/* Subtítulo */}
                                {subtitle && (
                                    <p className="text-xs text-gray-500 line-clamp-1 mb-2">
                                        {subtitle}
                                    </p>
                                )}

                                {/* Badges */}
                                <div className="flex items-center gap-1 flex-wrap mb-2">
                                    {renderBadges()}
                                </div>

                                {/* Data */}
                                {renderDate()}
                            </div>

                            {/* Ações */}
                            {renderActions()}
                        </div>
                    </div>
                </div>
            </CardHeader>
        </Card>
    );
} 
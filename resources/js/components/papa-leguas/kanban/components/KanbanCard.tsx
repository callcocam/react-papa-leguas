import React, { useState } from 'react';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { 
    ChevronDown, 
    ChevronRight, 
    MoreVertical,
    Calendar,
    User,
    Tag
} from 'lucide-react';
import { Icons } from '../../icons';
import type { KanbanCardProps } from '../types';

/**
 * Componente que representa um card individual no Kanban.
 * 
 * Funcionalidades:
 * - Exibe informações principais do item
 * - Ações do backend integradas
 * - Suporte a drag and drop (futuro)
 * - Estados visuais para expansão (se necessário)
 * - Renderização inteligente de dados
 */
export default function KanbanCard({
    item,
    column,
    actions = [],
    isExpanded = false,
    onToggleExpansion,
    onAction,
    onDragStart,
    draggable = false
}: KanbanCardProps) {
    const [showActions, setShowActions] = useState(false);

    // Renderiza informações principais do item
    const renderMainInfo = () => {
        const title = item.name || item.title || item.description || `Item ${item.id}`;
        const subtitle = item.email || item.slug || item.category?.name || '';
        const date = item.created_at || item.updated_at;
        const status = item.status || item.email_verified_at;

        return (
            <div className="space-y-2">
                {/* Título principal */}
                <h3 className="font-medium text-sm text-gray-900 line-clamp-2">
                    {title}
                </h3>

                {/* Subtítulo/Info secundária */}
                {subtitle && (
                    <p className="text-xs text-gray-500 line-clamp-1">
                        {subtitle}
                    </p>
                )}

                {/* Badges de status */}
                <div className="flex items-center gap-1 flex-wrap">
                    {status && (
                        <Badge variant="secondary" className="text-xs">
                            {typeof status === 'string' ? status : 'Ativo'}
                        </Badge>
                    )}
                    
                    {item.posts_count !== undefined && (
                        <Badge variant="outline" className="text-xs">
                            {item.posts_count} posts
                        </Badge>
                    )}

                    {item.category && (
                        <Badge variant="outline" className="text-xs">
                            {item.category.name}
                        </Badge>
                    )}
                </div>

                {/* Data */}
                {date && (
                    <div className="flex items-center gap-1 text-xs text-gray-400">
                        <Calendar className="h-3 w-3" />
                        <span>
                            {new Date(date).toLocaleDateString('pt-BR')}
                        </span>
                    </div>
                )}
            </div>
        );
    };

    // Renderiza ações disponíveis para este item
    const renderActions = () => {
        if (!actions.length) return null;

        const visibleActions = actions.filter(action => {
            if (typeof action.visible === 'function') {
                return action.visible(item);
            }
            return action.visible !== false;
        });

        if (!visibleActions.length) return null;

        return (
            <div className="flex items-center gap-1">
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
                        onClick={() => setShowActions(!showActions)}
                    >
                        <MoreVertical className="h-3 w-3" />
                    </Button>
                )}
            </div>
        );
    };

    // Handler para drag start
    const handleDragStart = (e: React.DragEvent) => {
        if (!draggable || !onDragStart) return;
        
        e.dataTransfer.setData('application/json', JSON.stringify(item));
        e.dataTransfer.effectAllowed = 'move';
        onDragStart(item);
    };

    return (
        <Card 
            className={`kanban-card transition-all duration-200 hover:shadow-md cursor-pointer ${
                isExpanded ? 'ring-2 ring-blue-200' : ''
            } ${draggable ? 'cursor-move' : ''}`}
            style={{
                borderLeftColor: column.color || '#e5e7eb',
                borderLeftWidth: '3px'
            }}
            draggable={draggable}
            onDragStart={handleDragStart}
        >
            <CardHeader className="pb-2">
                <div className="flex items-start justify-between">
                    <div className="flex-1 min-w-0">
                        {renderMainInfo()}
                    </div>

                    <div className="flex items-center gap-1 ml-2">
                        {/* Ações do item */}
                        {renderActions()}

                        {/* Botão de expansão (se callback fornecido) */}
                        {onToggleExpansion && (
                            <Button
                                variant="ghost"
                                size="sm"
                                onClick={onToggleExpansion}
                                className="h-6 w-6 p-0"
                            >
                                {isExpanded ? (
                                    <ChevronDown className="h-3 w-3" />
                                ) : (
                                    <ChevronRight className="h-3 w-3" />
                                )}
                            </Button>
                        )}
                    </div>
                </div>
            </CardHeader>

            {/* Conteúdo expandido (se necessário) */}
            {isExpanded && onToggleExpansion && (
                <CardContent className="pt-0">
                    <div className="border-t border-gray-100 pt-3">
                        <div className="text-sm text-gray-600">
                            <p>Detalhes adicionais do item serão exibidos aqui.</p>
                            <p className="text-xs text-gray-400 mt-1">
                                ID: {item.id} | Coluna: {column.title}
                            </p>
                        </div>
                    </div>
                </CardContent>
            )}

            {/* Menu de ações expandido */}
            {showActions && actions.length > 2 && (
                <CardContent className="pt-0">
                    <div className="border-t border-gray-100 pt-2">
                        <div className="flex flex-wrap gap-1">
                            {actions.slice(2).map(action => {
                                const visible = typeof action.visible === 'function' 
                                    ? action.visible(item)
                                    : action.visible !== false;

                                if (!visible) return null;

                                const IconComponent = action.icon && typeof action.icon === 'string'
                                    ? (Icons[action.icon as keyof typeof Icons] as any)
                                    : null;

                                return (
                                    <Button
                                        key={action.id}
                                        variant={action.variant || 'outline'}
                                        size="sm"
                                        className="text-xs"
                                        onClick={() => {
                                            onAction?.(action.id, item);
                                            setShowActions(false);
                                        }}
                                    >
                                        {IconComponent && (
                                            <IconComponent className="h-3 w-3 mr-1" />
                                        )}
                                        {action.label}
                                    </Button>
                                );
                            })}
                        </div>
                    </div>
                </CardContent>
            )}
        </Card>
    );
} 
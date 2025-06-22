import React from 'react';
import { router } from '@inertiajs/react';
import { cn } from '../../lib/utils';
import { Button } from './button';
import { 
    List, 
    LayoutGrid, 
    KanbanSquare,
    LucideIcon
} from 'lucide-react';
import { ViewConfig, ViewsConfig } from '../../types';

// Mapeamento de ícones
const iconMap: Record<string, LucideIcon> = {
    'list': List,
    'layout-grid': LayoutGrid,
    'kanban-square': KanbanSquare,
};

interface ViewSelectorProps {
    views: ViewConfig[];
    activeView: string;
    config?: ViewsConfig;
    className?: string;
    // Parâmetros para preservar na URL
    preserveParams?: boolean;
}

export default function ViewSelector({
    views = [],
    activeView,
    config = {},
    className = '',
    preserveParams = true
}: ViewSelectorProps) {
    // Se não há views configuradas, não renderizar nada
    if (!views || views.length === 0) {
        return null;
    }

    // Configurações padrão
    const {
        showViewSelector = true,
        variant = 'buttons',
        size = 'sm',
        showLabels = true,
        showIcons = true,
    } = config;

    // Se o seletor está desabilitado, não renderizar
    if (!showViewSelector) {
        return null;
    }

    // Função para obter ícone
    const getIcon = (iconName?: string): LucideIcon => {
        if (!iconName) return List;
        return iconMap[iconName] || List;
    };

    // Função para trocar de view
    const handleViewChange = (viewId: string) => {
        if (viewId === activeView) return;

        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);
        
        // Definir o parâmetro da view
        searchParams.set('view', viewId);
        
        // Construir nova URL
        const newUrl = preserveParams 
            ? `${currentUrl.pathname}?${searchParams.toString()}`
            : `${currentUrl.pathname}?view=${viewId}`;
        
        // Navegar via Inertia (recarrega a página)
        router.get(newUrl, {}, {
            preserveState: false, // Recarregar estado
            preserveScroll: false, // Não preservar scroll
            replace: false, // Criar nova entrada no histórico
        });
    };

    // Classes CSS baseadas na configuração
    const containerClasses = cn(
        'flex items-center gap-1',
        {
            'gap-0.5': size === 'sm',
            'gap-1': size === 'md',
            'gap-2': size === 'lg',
        },
        className
    );

    const buttonClasses = (view: ViewConfig, isActive: boolean) => cn(
        'transition-all duration-200',
        {
            // Tamanhos
            'h-8 px-2 text-xs': size === 'sm',
            'h-9 px-3 text-sm': size === 'md',
            'h-10 px-4 text-base': size === 'lg',
            
            // Estados ativos/inativos
            'bg-primary text-primary-foreground shadow-sm': isActive,
            'bg-transparent hover:bg-accent hover:text-accent-foreground': !isActive,
        }
    );

    // Renderizar botões
    const renderButtons = () => (
        <div className={containerClasses}>
            {views.map((view) => {
                const Icon = getIcon(view.icon);
                const isActive = view.id === activeView;

                return (
                    <Button
                        key={view.id}
                        variant={isActive ? 'default' : 'ghost'}
                        size={size === 'md' ? 'default' : size}
                        onClick={() => handleViewChange(view.id)}
                        className={buttonClasses(view, isActive)}
                        title={view.description || view.label}
                    >
                        {/* Ícone */}
                        {showIcons && (
                            <Icon className={cn(
                                'h-4 w-4',
                                showLabels && 'mr-1.5'
                            )} />
                        )}
                        
                        {/* Label */}
                        {showLabels && (
                            <span className="whitespace-nowrap">
                                {view.label}
                            </span>
                        )}
                    </Button>
                );
            })}
        </div>
    );

    // Por enquanto, implementar apenas variant 'buttons'
    // Dropdown e tabs podem ser implementados depois se necessário
    switch (variant) {
        case 'buttons':
        default:
            return renderButtons();
    }
} 
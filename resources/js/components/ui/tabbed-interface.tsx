import React, { useState, useEffect, useMemo } from 'react';
import { router } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import { TabConfig, TabsConfig } from '../../types';
import { LucideIcon, List, Eye, EyeOff } from 'lucide-react';
import * as LucideIcons from 'lucide-react';
import { Badge } from './badge';

interface TabbedInterfaceProps {
    tabs: TabConfig[];
    config?: TabsConfig;
    defaultContent?: React.ReactNode; // Fallback quando não há tabs
    className?: string;
    onTabChange?: (tabId: string, previousTabId?: string) => void;
    children?: (activeTab: TabConfig, tabContent: any) => React.ReactNode;
    // Configurações de URL
    urlParam?: string; // Nome do parâmetro na URL (padrão: 'tab')
    preserveUrl?: boolean; // Se deve preservar outros parâmetros da URL
}

export default function TabbedInterface({
    tabs = [],
    config = {},
    defaultContent,
    className = '',
    onTabChange,
    children,
    urlParam = 'tab',
    preserveUrl = true
}: TabbedInterfaceProps) {
    // Se não há tabs configuradas, retorna o conteúdo padrão
    if (!tabs || tabs.length === 0) {
        return <>{defaultContent}</>;
    }

    // Filtrar tabs visíveis
    const visibleTabs = useMemo(() => 
        tabs.filter(tab => !tab.hidden), 
        [tabs]
    );

    // Obter tab ativa da URL ou usar padrão
    const getActiveTabFromUrl = (): string => {
        const urlParams = new URLSearchParams(window.location.search);
        const tabFromUrl = urlParams.get(urlParam);
        
        // Verificar se a tab da URL existe nas tabs visíveis
        if (tabFromUrl && visibleTabs.some(tab => tab.id === tabFromUrl)) {
            return tabFromUrl;
        }
        
        // Fallback para tab padrão
        return config.defaultTab || visibleTabs[0]?.id || 'lista';
    };

    // Estado da tab ativa (prioridade: backend.active > URL > defaultTab)
    const [activeTabId, setActiveTabId] = useState<string>(() => {
        // 1. Verificar se há tab marcada como ativa pelo backend
        const backendActiveTab = visibleTabs.find(tab => tab.active);
        if (backendActiveTab) {
            return backendActiveTab.id;
        }
        
        // 2. Usar valor da URL
        return getActiveTabFromUrl();
    });

    // Estado do conteúdo carregado (para lazy loading)
    const [loadedContent, setLoadedContent] = useState<Record<string, any>>({});
    const [loadingTabs, setLoadingTabs] = useState<Set<string>>(new Set());

    // Tab ativa atual
    const activeTab = useMemo(() => 
        visibleTabs.find(tab => tab.id === activeTabId) || visibleTabs[0],
        [visibleTabs, activeTabId]
    );

    // Função para atualizar URL
    const updateUrl = (tabId: string) => {
        const currentUrl = new URL(window.location.href);
        const searchParams = new URLSearchParams(currentUrl.search);
        
        // Definir o parâmetro da tab
        searchParams.set(urlParam, tabId);
        
        // Preservar outros parâmetros se configurado
        const newUrl = preserveUrl 
            ? `${currentUrl.pathname}?${searchParams.toString()}`
            : `${currentUrl.pathname}?${urlParam}=${tabId}`;
        
        // Usar Inertia para navegação sem reload
        router.get(newUrl, {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true, // Não criar nova entrada no histórico
        });
    };

    // Escutar mudanças na URL (botão voltar/avançar do navegador)
    useEffect(() => {
        const handlePopState = () => {
            const newActiveTab = getActiveTabFromUrl();
            if (newActiveTab !== activeTabId) {
                setActiveTabId(newActiveTab);
            }
        };

        window.addEventListener('popstate', handlePopState);
        return () => window.removeEventListener('popstate', handlePopState);
    }, [activeTabId, urlParam, visibleTabs]);

    // Função para obter ícone do Lucide
    const getIcon = (iconName?: string): LucideIcon => {
        if (!iconName) return List;
        
        // Converter para PascalCase se necessário
        const iconKey = iconName.charAt(0).toUpperCase() + iconName.slice(1).replace(/-([a-z])/g, (g) => g[1].toUpperCase());
        const IconComponent = (LucideIcons as any)[iconKey];
        
        return IconComponent || List;
    };

    // Função para carregar conteúdo da tab
    const loadTabContent = async (tab: TabConfig) => {
        if (!config.lazy || loadedContent[tab.id] || !tab.loadContent) {
            return;
        }

        setLoadingTabs(prev => new Set(prev).add(tab.id));

        try {
            const content = await tab.loadContent();
            setLoadedContent(prev => ({
                ...prev,
                [tab.id]: content
            }));
            
            // Callback de carregamento
            config.onTabLoad?.(tab.id);
        } catch (error) {
            console.error(`Erro ao carregar conteúdo da tab ${tab.id}:`, error);
        } finally {
            setLoadingTabs(prev => {
                const newSet = new Set(prev);
                newSet.delete(tab.id);
                return newSet;
            });
        }
    };

    // Função para trocar de tab
    const handleTabChange = (newTabId: string) => {
        const previousTabId = activeTabId;
        setActiveTabId(newTabId);
        
        // Atualizar URL
        updateUrl(newTabId);
        
        // Carregar conteúdo se necessário
        const newTab = visibleTabs.find(tab => tab.id === newTabId);
        if (newTab) {
            loadTabContent(newTab);
        }

        // Callbacks
        onTabChange?.(newTabId, previousTabId);
        config.onTabChange?.(newTabId, previousTabId);
    };

    // Carregar conteúdo da tab ativa inicialmente
    useEffect(() => {
        if (activeTab) {
            loadTabContent(activeTab);
        }
    }, [activeTab?.id]);

    // Configurações de estilo
    const {
        variant = 'default',
        size = 'md',
        position = 'top',
        fullWidth = false,
        scrollable = true,
        showBadges = true,
        showIcons = true
    } = config;

    // Classes CSS baseadas na configuração
    const tabsClasses = cn(
        'flex border-b border-gray-200 dark:border-gray-700',
        {
            // Variantes
            'bg-neutral-100 p-1 rounded-lg dark:bg-neutral-800': variant === 'pills',
            'border-b-0': variant === 'enclosed',
            
            // Tamanhos
            'text-sm': size === 'sm',
            'text-base': size === 'lg',
            
            // Layout
            'w-full': fullWidth,
            'overflow-x-auto': scrollable,
        },
        className
    );

    const tabClasses = (tab: TabConfig, isActive: boolean) => cn(
        'flex items-center px-4 py-2 font-medium text-sm transition-colors whitespace-nowrap',
        {
            // Estados ativos/inativos por variante
            'text-blue-600 border-b-2 border-blue-600 dark:text-blue-400': 
                variant === 'default' && isActive,
            'text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100': 
                variant === 'default' && !isActive,
                
            'bg-white shadow-sm rounded-md text-gray-900 dark:bg-neutral-700 dark:text-white': 
                variant === 'pills' && isActive,
            'text-gray-600 hover:bg-neutral-200/60 hover:text-gray-900 rounded-md dark:text-gray-400 dark:hover:bg-neutral-700/60': 
                variant === 'pills' && !isActive,
                
            'bg-white border border-gray-200 border-b-white rounded-t-lg dark:bg-gray-800 dark:border-gray-700': 
                variant === 'enclosed' && isActive,
            'border-b border-gray-200 hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700/50': 
                variant === 'enclosed' && !isActive,
            
            // Estados desabilitados
            'opacity-50 cursor-not-allowed': tab.disabled,
            'cursor-pointer': !tab.disabled,
            
            // Tamanhos
            'px-3 py-1.5 text-xs': size === 'sm',
            'px-6 py-3 text-base': size === 'lg',
        }
    );

    // Obter conteúdo da tab ativa
    const getTabContent = () => {
        if (!activeTab) return null;

        // Se há children (render prop), usar ele
        if (children) {
            const content = config.lazy ? loadedContent[activeTab.id] : activeTab.content;
            return children(activeTab, content);
        }

        // Conteúdo lazy loading
        if (config.lazy && !loadedContent[activeTab.id] && loadingTabs.has(activeTab.id)) {
            return (
                <div className="flex items-center justify-center py-8">
                    <div className="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                    <span className="ml-2 text-sm text-gray-600 dark:text-gray-400">
                        Carregando conteúdo...
                    </span>
                </div>
            );
        }

        // Conteúdo da tab
        const content = config.lazy ? loadedContent[activeTab.id] : activeTab.content;
        
        if (!content) {
            return (
                <div className="flex items-center justify-center py-8 text-gray-500 dark:text-gray-400">
                    <EyeOff className="h-5 w-5 mr-2" />
                    Nenhum conteúdo disponível
                </div>
            );
        }

        return content;
    };

    return (
        <div className="w-full">
            {/* Header das Tabs */}
            <div className={tabsClasses}>
                {visibleTabs.map((tab) => {
                    const Icon = getIcon(tab.icon);
                    const isActive = tab.id === activeTabId;
                    const isLoading = loadingTabs.has(tab.id);

                    return (
                        <button
                            key={tab.id}
                            onClick={() => !tab.disabled && handleTabChange(tab.id)}
                            className={tabClasses(tab, isActive)}
                            disabled={tab.disabled}
                            role="tab"
                            aria-selected={isActive}
                            aria-controls={`tabpanel-${tab.id}`}
                            id={`tab-${tab.id}`}
                        >
                            {/* Ícone */}
                            {showIcons && (
                                <Icon className={cn(
                                    'h-4 w-4',
                                    tab.label && 'mr-2',
                                    isLoading && 'animate-spin'
                                )} />
                            )}
                            
                            {/* Label */}
                            <span>{tab.label}</span>
                            
                            {/* Badge */}
                            {showBadges && tab.badge && (
                                <Badge 
                                    variant={
                                        // Se a tab está ativa, usar cor mais vibrante
                                        isActive ? (
                                            tab.color === 'destructive' ? 'destructive' :
                                            tab.color === 'secondary' ? 'secondary' :
                                            'default'
                                        ) : (
                                            // Se não está ativa, usar versão mais sutil
                                            'outline'
                                        )
                                    }
                                    className={cn(
                                        "ml-2 h-6 min-w-10 text-xs transition-all duration-200",
                                        {
                                            // Badge da tab ativa com destaque e cores específicas
                                            'ring-2 shadow-sm': isActive,
                                            'ring-blue-200 dark:ring-blue-800': isActive && tab.color === 'primary',
                                            'ring-green-200 dark:ring-green-800': isActive && tab.color === 'success',
                                            'ring-yellow-200 dark:ring-yellow-800': isActive && tab.color === 'warning',
                                            'ring-red-200 dark:ring-red-800': isActive && tab.color === 'destructive',
                                            'ring-gray-200 dark:ring-gray-800': isActive && tab.color === 'secondary',
                                            // Badge das tabs inativas mais sutil
                                            'opacity-70': !isActive
                                        }
                                    )}
                                >
                                    {tab.badge}
                                </Badge>
                            )}
                        </button>
                    );
                })}
            </div>

            {/* Conteúdo da Tab Ativa */}
            <div
                role="tabpanel"
                id={`tabpanel-${activeTab?.id}`}
                aria-labelledby={`tab-${activeTab?.id}`}
                className="mt-4"
            >
                {getTabContent()}
            </div>
        </div>
    );
} 
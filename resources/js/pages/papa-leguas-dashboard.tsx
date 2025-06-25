import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import ReactAppLayout from '../layouts/react-app-layout';
import type { BreadcrumbItem } from '../types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard() {
    return (
        <ReactAppLayout 
            breadcrumbs={breadcrumbs}
            title="Dashboard"
        >
            <Head title="Dashboard" />
            
            {/* Cards de métricas */}
            <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card">
                    <div className="p-6">
                        <h3 className="text-lg font-semibold text-foreground">Total de Usuários</h3>
                        <p className="text-3xl font-bold text-primary mt-2">1,234</p>
                        <p className="text-sm text-muted-foreground mt-1">+12% desde o mês passado</p>
                    </div>
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/10 dark:stroke-neutral-100/10 -z-10" />
                </div>
                
                <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card">
                    <div className="p-6">
                        <h3 className="text-lg font-semibold text-foreground">Tickets Abertos</h3>
                        <p className="text-3xl font-bold text-destructive mt-2">42</p>
                        <p className="text-sm text-muted-foreground mt-1">-5% desde ontem</p>
                    </div>
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/10 dark:stroke-neutral-100/10 -z-10" />
                </div>
                
                <div className="relative aspect-video overflow-hidden rounded-xl border border-sidebar-border/70 dark:border-sidebar-border bg-card">
                    <div className="p-6">
                        <h3 className="text-lg font-semibold text-foreground">Produtos Ativos</h3>
                        <p className="text-3xl font-bold text-success mt-2">156</p>
                        <p className="text-sm text-muted-foreground mt-1">+8 novos produtos</p>
                    </div>
                    <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/10 dark:stroke-neutral-100/10 -z-10" />
                </div>
            </div>
            
            {/* Área principal */}
            <div className="relative min-h-[60vh] flex-1 overflow-hidden rounded-xl border border-sidebar-border/70 md:min-h-min dark:border-sidebar-border bg-card">
                <div className="p-6">
                    <h2 className="text-xl font-semibold text-foreground mb-4">Bem-vindo ao Papa Leguas</h2>
                    <div className="space-y-4">
                        <p className="text-muted-foreground">
                            Sistema de gerenciamento com navegação dinâmica, tabelas avançadas e permissões integradas.
                        </p>
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
                            <div className="p-4 rounded-lg bg-muted/50">
                                <h3 className="font-medium text-foreground mb-2">🎯 Funcionalidades</h3>
                                <ul className="text-sm text-muted-foreground space-y-1">
                                    <li>• Navegação dinâmica com permissões</li>
                                    <li>• Sistema de tickets com Kanban</li>
                                    <li>• Tabelas avançadas Papa Leguas</li>
                                    <li>• Dark mode integrado</li>
                                </ul>
                            </div>
                            
                            <div className="p-4 rounded-lg bg-muted/50">
                                <h3 className="font-medium text-foreground mb-2">🚀 Acesso Rápido</h3>
                                <ul className="text-sm text-muted-foreground space-y-1">
                                    <li>• Administração → Categorias</li>
                                    <li>• Administração → Produtos</li>
                                    <li>• Administração → Tickets</li>
                                    <li>• Testes → Exemplo Kanban</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/5 dark:stroke-neutral-100/5 -z-10" />
            </div>
        </ReactAppLayout>
    );
}

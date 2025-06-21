import React from 'react';
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/react';

interface PaginationData {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    per_page: number;
    prev_page_url?: string;
    next_page_url?: string;
    links?: Array<{
        url?: string;
        label: string;
        active: boolean;
    }>;
}

interface PaginationProps {
    pagination?: PaginationData;
    onPageChange?: (page: number) => void;
}

export default function Pagination({ pagination, onPageChange }: PaginationProps) {
    if (!pagination || pagination.last_page <= 1) {
        return null;
    }

    const handlePageChange = (page: number) => {
        if (onPageChange) {
            onPageChange(page);
        } else {
            // Navegação padrão via URL
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('page', page.toString());
            router.visit(currentUrl.toString(), {
                preserveState: true,
                preserveScroll: true
            });
        }
    };

    const handlePrevious = () => {
        if (pagination.current_page > 1) {
            handlePageChange(pagination.current_page - 1);
        }
    };

    const handleNext = () => {
        if (pagination.current_page < pagination.last_page) {
            handlePageChange(pagination.current_page + 1);
        }
    };

    return (
        <div className="flex items-center justify-between px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {/* Info de registros */}
            <div className="text-sm text-gray-600 dark:text-gray-400">
                Mostrando {pagination.from || 0} a {pagination.to || 0} de {pagination.total || 0} registros
            </div>
            
            {/* Controles de navegação */}
            <div className="flex items-center gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    disabled={pagination.current_page <= 1}
                    onClick={handlePrevious}
                >
                    Anterior
                </Button>
                
                {/* Páginas numeradas */}
                <div className="flex items-center gap-1">
                    {pagination.links ? (
                        // Usar links do Laravel se disponível
                        pagination.links
                            .filter(link => link.label !== 'Anterior' && link.label !== 'Próximo')
                            .map((link, index) => (
                                <Button
                                    key={index}
                                    variant={link.active ? "default" : "outline"}
                                    size="sm"
                                    disabled={!link.url}
                                    onClick={() => {
                                        if (link.url && !link.active) {
                                            const url = new URL(link.url);
                                            const page = url.searchParams.get('page');
                                            if (page) {
                                                handlePageChange(parseInt(page));
                                            }
                                        }
                                    }}
                                    className="min-w-[2rem]"
                                >
                                    {link.label}
                                </Button>
                            ))
                    ) : (
                        // Navegação simples se não houver links
                        <>
                            {pagination.current_page > 2 && (
                                <>
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handlePageChange(1)}
                                        className="min-w-[2rem]"
                                    >
                                        1
                                    </Button>
                                    {pagination.current_page > 3 && (
                                        <span className="px-2 text-gray-400">...</span>
                                    )}
                                </>
                            )}
                            
                            {pagination.current_page > 1 && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => handlePageChange(pagination.current_page - 1)}
                                    className="min-w-[2rem]"
                                >
                                    {pagination.current_page - 1}
                                </Button>
                            )}
                            
                            <Button
                                variant="default"
                                size="sm"
                                className="min-w-[2rem]"
                            >
                                {pagination.current_page}
                            </Button>
                            
                            {pagination.current_page < pagination.last_page && (
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={() => handlePageChange(pagination.current_page + 1)}
                                    className="min-w-[2rem]"
                                >
                                    {pagination.current_page + 1}
                                </Button>
                            )}
                            
                            {pagination.current_page < pagination.last_page - 1 && (
                                <>
                                    {pagination.current_page < pagination.last_page - 2 && (
                                        <span className="px-2 text-gray-400">...</span>
                                    )}
                                    <Button
                                        variant="outline"
                                        size="sm"
                                        onClick={() => handlePageChange(pagination.last_page)}
                                        className="min-w-[2rem]"
                                    >
                                        {pagination.last_page}
                                    </Button>
                                </>
                            )}
                        </>
                    )}
                </div>
                
                <Button
                    variant="outline"
                    size="sm"
                    disabled={pagination.current_page >= pagination.last_page}
                    onClick={handleNext}
                >
                    Próximo
                </Button>
            </div>
        </div>
    );
} 
import React from 'react';
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight } from 'lucide-react';
import { TablePaginationProps } from '../types';

export function TablePagination({ pagination, onPageChange, loading }: TablePaginationProps) {
    const {
        currentPage,
        lastPage,
        perPage,
        total,
        from,
        to,
        hasPages,
        onFirstPage,
        onLastPage
    } = pagination;

    if (!hasPages || total === 0) {
        return null;
    }

    const handlePageChange = (page: number) => {
        if (onPageChange && !loading && page !== currentPage && page >= 1 && page <= lastPage) {
            onPageChange(page);
        }
    };

    const getVisiblePages = () => {
        const delta = 2;
        const range = [];
        const rangeWithDots = [];

        for (let i = Math.max(2, currentPage - delta); 
             i <= Math.min(lastPage - 1, currentPage + delta); 
             i++) {
            range.push(i);
        }

        if (currentPage - delta > 2) {
            rangeWithDots.push(1, '...');
        } else {
            rangeWithDots.push(1);
        }

        rangeWithDots.push(...range);

        if (currentPage + delta < lastPage - 1) {
            rangeWithDots.push('...', lastPage);
        } else if (lastPage > 1) {
            rangeWithDots.push(lastPage);
        }

        return rangeWithDots;
    };

    return (
        <div className="flex items-center justify-between px-2">
            {/* Informações da paginação */}
            <div className="flex-1 text-sm text-muted-foreground">
                Mostrando {from} a {to} de {total} registros
            </div>

            {/* Controles de paginação */}
            <div className="flex items-center space-x-2">
                {/* Primeira página */}
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handlePageChange(1)}
                    disabled={loading || onFirstPage}
                    className="h-8 w-8 p-0"
                >
                    <ChevronsLeft className="h-4 w-4" />
                    <span className="sr-only">Primeira página</span>
                </Button>

                {/* Página anterior */}
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handlePageChange(currentPage - 1)}
                    disabled={loading || onFirstPage}
                    className="h-8 w-8 p-0"
                >
                    <ChevronLeft className="h-4 w-4" />
                    <span className="sr-only">Página anterior</span>
                </Button>

                {/* Números das páginas */}
                <div className="flex items-center space-x-1">
                    {getVisiblePages().map((page, index) => {
                        if (page === '...') {
                            return (
                                <span key={`dots-${index}`} className="px-2 py-1 text-sm text-muted-foreground">
                                    ...
                                </span>
                            );
                        }

                        const pageNumber = page as number;
                        const isCurrentPage = pageNumber === currentPage;

                        return (
                            <Button
                                key={pageNumber}
                                variant={isCurrentPage ? 'default' : 'outline'}
                                size="sm"
                                onClick={() => handlePageChange(pageNumber)}
                                disabled={loading || isCurrentPage}
                                className="h-8 w-8 p-0"
                            >
                                {pageNumber}
                            </Button>
                        );
                    })}
                </div>

                {/* Próxima página */}
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handlePageChange(currentPage + 1)}
                    disabled={loading || onLastPage}
                    className="h-8 w-8 p-0"
                >
                    <ChevronRight className="h-4 w-4" />
                    <span className="sr-only">Próxima página</span>
                </Button>

                {/* Última página */}
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => handlePageChange(lastPage)}
                    disabled={loading || onLastPage}
                    className="h-8 w-8 p-0"
                >
                    <ChevronsRight className="h-4 w-4" />
                    <span className="sr-only">Última página</span>
                </Button>
            </div>
        </div>
    );
}
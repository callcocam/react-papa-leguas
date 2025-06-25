import React from 'react';
import { router } from '@inertiajs/react';
import Pagination from './table/components/Pagination';

interface PaginationWrapperProps {
    pagination: any;
    onPageChange?: (page: number) => void;
    preserveState?: boolean;
    preserveScroll?: boolean;
    className?: string;
}

export default function PaginationWrapper({
    pagination,
    onPageChange,
    preserveState = true,
    preserveScroll = true,
    className = ""
}: PaginationWrapperProps) {
    
    const handlePageChange = (page: number) => {
        if (onPageChange) {
            onPageChange(page);
            return;
        }

        // Comportamento padrão com Inertia.js
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('page', page.toString());
        
        router.visit(currentUrl.toString(), {
            preserveState,
            preserveScroll
        });
    };

    if (!pagination) {
        return null;
    }

    return (
        <div className={className}>
            <Pagination
                pagination={pagination}
                onPageChange={handlePageChange}
            />
        </div>
    );
}

// Hook para uso com Inertia.js (opcional)
export function usePagination() {
    const handlePageChange = (page: number) => {
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('page', page.toString());
        
        router.visit(currentUrl.toString(), {
            preserveState: true,
            preserveScroll: true
        });
    };

    return {
        handlePageChange
    };
} 
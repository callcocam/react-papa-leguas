import React, { useState } from 'react';
import { Table } from '../../../papa-leguas';
import { router } from '@inertiajs/react';

interface TableRendererProps {
    data: any[];
    columns: any[];
    config: any;
    actions: any;
    className?: string;
    loading?: boolean;
}

/**
 * 🚀 PAPA LEGUAS KANBAN - CONFIA NO BACKEND
 * 
 * SE CHEGOU AQUI, BACKEND JÁ VALIDOU TUDO
 * 
 * REGRAS:
 * ✅ Backend só envia view=kanban se tudo estiver configurado
 * ✅ Frontend não faz validações, só renderiza
 * ✅ Papa Leguas Kanban direto
 * ✅ Debug para acompanhar dados recebidos
 */
export default function TableRenderer({
    data = [],
    columns = [],
    config = {},
    actions = {},
    className = '',
    loading = false
}: TableRendererProps) {

    const [sortColumn, setSortColumn] = useState<string | undefined>(undefined);
    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');
 

    const handleSort = (column: string, direction: 'asc' | 'desc') => {
        setSortColumn(column);
        setSortDirection(direction);

        // Aplicar ordenação via URL usando padrão sort_column e sort_direction
        const currentUrl = new URL(window.location.href);
        currentUrl.searchParams.set('sort_column', column);
        currentUrl.searchParams.set('sort_direction', direction);

        router.visit(currentUrl.toString(), {
            preserveState: true,
            preserveScroll: true
        });
    };

    return (
        <Table
            columns={columns}
            actions={actions}
            loading={loading}
            onSort={handleSort}
            sortColumn={sortColumn}
            sortDirection={sortDirection}
        />
    );
}

// ⚠️ FUNÇÃO REMOVIDA: generateFallbackKanbanColumns
// Frontend confia que backend já validou tudo 
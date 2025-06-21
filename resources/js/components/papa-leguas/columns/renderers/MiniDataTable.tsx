import React, { useState } from 'react';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { 
    ChevronLeft, 
    ChevronRight, 
    Search, 
    RotateCcw,
    ArrowUpDown,
    ArrowUp,
    ArrowDown
} from 'lucide-react';
import { cn } from '@/lib/utils';
import ColumnRenderer from '../ColumnRenderer';
import ActionRenderer from '../../actions/ActionRenderer';

interface MiniDataTableProps {
    data: any[];
    columns: any[];
    actions: any[];
    pagination?: any;
    config?: any;
    parentId: string | number;
    onRefresh?: () => void;
}

/**
 * Tabela compacta para exibir dados de sub-tabelas aninhadas.
 * 
 * Funcionalidades:
 * - Layout compacto e responsivo
 * - Busca local
 * - Ordenação por coluna
 * - Paginação simples
 * - Ações por linha
 * - Estados visuais elegantes
 */
export default function MiniDataTable({
    data,
    columns,
    actions,
    pagination,
    config,
    parentId,
    onRefresh
}: MiniDataTableProps) {
    const [searchTerm, setSearchTerm] = useState('');
    const [sortColumn, setSortColumn] = useState<string | null>(null);
    const [sortDirection, setSortDirection] = useState<'asc' | 'desc'>('asc');
    const [currentPage, setCurrentPage] = useState(1);

    // Configurações da tabela
    const itemsPerPage = config?.per_page || 5;
    const showSearch = config?.searchable !== false && columns.some(col => col.searchable);
    const showPagination = data.length > itemsPerPage;

    // Filtra dados por busca
    const filteredData = React.useMemo(() => {
        if (!searchTerm) return data;
        
        return data.filter(item => {
            return columns.some(column => {
                if (!column.searchable) return false;
                
                const value = item[column.key];
                if (value == null) return false;
                
                return String(value)
                    .toLowerCase()
                    .includes(searchTerm.toLowerCase());
            });
        });
    }, [data, searchTerm, columns]);

    // Ordena dados
    const sortedData = React.useMemo(() => {
        if (!sortColumn) return filteredData;
        
        return [...filteredData].sort((a, b) => {
            const aValue = a[sortColumn];
            const bValue = b[sortColumn];
            
            if (aValue == null && bValue == null) return 0;
            if (aValue == null) return sortDirection === 'asc' ? 1 : -1;
            if (bValue == null) return sortDirection === 'asc' ? -1 : 1;
            
            let comparison = 0;
            if (typeof aValue === 'string' && typeof bValue === 'string') {
                comparison = aValue.localeCompare(bValue);
            } else {
                comparison = aValue < bValue ? -1 : aValue > bValue ? 1 : 0;
            }
            
            return sortDirection === 'asc' ? comparison : -comparison;
        });
    }, [filteredData, sortColumn, sortDirection]);

    // Pagina dados
    const paginatedData = React.useMemo(() => {
        if (!showPagination) return sortedData;
        
        const startIndex = (currentPage - 1) * itemsPerPage;
        return sortedData.slice(startIndex, startIndex + itemsPerPage);
    }, [sortedData, currentPage, itemsPerPage, showPagination]);

    // Manipula ordenação
    const handleSort = (columnKey: string) => {
        if (sortColumn === columnKey) {
            setSortDirection(sortDirection === 'asc' ? 'desc' : 'asc');
        } else {
            setSortColumn(columnKey);
            setSortDirection('asc');
        }
    };

    // Ícone de ordenação
    const getSortIcon = (columnKey: string) => {
        if (sortColumn !== columnKey) {
            return <ArrowUpDown className="h-3 w-3 opacity-40" />;
        }
        
        return sortDirection === 'asc' 
            ? <ArrowUp className="h-3 w-3" />
            : <ArrowDown className="h-3 w-3" />;
    };

    // Calcula informações de paginação
    const totalPages = Math.ceil(sortedData.length / itemsPerPage);
    const startItem = (currentPage - 1) * itemsPerPage + 1;
    const endItem = Math.min(currentPage * itemsPerPage, sortedData.length);

    return (
        <div className="mini-data-table space-y-3">
            {/* Cabeçalho com busca e refresh */}
            <div className="flex items-center justify-between gap-3">
                {showSearch && (
                    <div className="relative flex-1 max-w-sm">
                        <Search className="absolute left-2 top-1/2 h-3 w-3 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Buscar..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                            className="h-7 pl-7 text-xs"
                        />
                    </div>
                )}
                
                <div className="flex items-center gap-2">
                    <Badge variant="secondary" className="text-xs">
                        {sortedData.length} {sortedData.length === 1 ? 'item' : 'itens'}
                    </Badge>
                    
                    {onRefresh && (
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={onRefresh}
                            className="h-7 w-7 p-0"
                            title="Atualizar"
                        >
                            <RotateCcw className="h-3 w-3" />
                        </Button>
                    )}
                </div>
            </div>

            {/* Tabela */}
            <div className="rounded-md border border-border/50">
                <Table>
                    <TableHeader>
                        <TableRow className="bg-muted/30">
                            {columns.map((column) => (
                                <TableHead 
                                    key={column.key}
                                    className={cn(
                                        "h-8 px-2 text-xs font-medium",
                                        column.sortable && "cursor-pointer hover:bg-muted/50"
                                    )}
                                    onClick={() => column.sortable && handleSort(column.key)}
                                >
                                    <div className="flex items-center gap-1">
                                        <span>{column.label}</span>
                                        {column.sortable && getSortIcon(column.key)}
                                    </div>
                                </TableHead>
                            ))}
                            
                            {actions.length > 0 && (
                                <TableHead className="h-8 px-2 text-xs font-medium w-20">
                                    Ações
                                </TableHead>
                            )}
                        </TableRow>
                    </TableHeader>
                    
                    <TableBody>
                        {paginatedData.length === 0 ? (
                            <TableRow>
                                <TableCell 
                                    colSpan={columns.length + (actions.length > 0 ? 1 : 0)}
                                    className="h-16 text-center text-muted-foreground text-xs"
                                >
                                    {searchTerm ? 'Nenhum resultado encontrado' : 'Nenhum item'}
                                </TableCell>
                            </TableRow>
                        ) : (
                            paginatedData.map((item, index) => (
                                <TableRow 
                                    key={item.id || index}
                                    className="hover:bg-muted/30 transition-colors"
                                >
                                    {columns.map((column) => (
                                        <TableCell 
                                            key={column.key} 
                                            className="px-2 py-1.5 text-xs"
                                        >
                                            <ColumnRenderer
                                                column={column}
                                                item={item}
                                                value={item[column.key]}
                                            />
                                        </TableCell>
                                    ))}
                                    
                                    {actions.length > 0 && (
                                        <TableCell className="px-2 py-1.5">
                                            <div className="flex items-center gap-1">
                                                {actions.map((action) => (
                                                    <ActionRenderer
                                                        key={action.key}
                                                        action={action}
                                                        item={item}
                                                    />
                                                ))}
                                            </div>
                                        </TableCell>
                                    )}
                                </TableRow>
                            ))
                        )}
                    </TableBody>
                </Table>
            </div>

            {/* Paginação */}
            {showPagination && totalPages > 1 && (
                <div className="flex items-center justify-between text-xs text-muted-foreground">
                    <div>
                        Mostrando {startItem} a {endItem} de {sortedData.length} itens
                    </div>
                    
                    <div className="flex items-center gap-2">
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setCurrentPage(currentPage - 1)}
                            disabled={currentPage === 1}
                            className="h-6 w-6 p-0"
                        >
                            <ChevronLeft className="h-3 w-3" />
                        </Button>
                        
                        <span className="text-xs">
                            {currentPage} de {totalPages}
                        </span>
                        
                        <Button
                            variant="ghost"
                            size="sm"
                            onClick={() => setCurrentPage(currentPage + 1)}
                            disabled={currentPage === totalPages}
                            className="h-6 w-6 p-0"
                        >
                            <ChevronRight className="h-3 w-3" />
                        </Button>
                    </div>
                </div>
            )}
        </div>
    );
} 
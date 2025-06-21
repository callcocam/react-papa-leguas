import React from 'react';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';

interface TableSkeletonProps {
  rows?: number;
  columns?: number;
  showHeader?: boolean;
  className?: string;
}

/**
 * Skeleton loader profissional para tabelas
 * 
 * Funcionalidades:
 * - Número configurável de linhas e colunas
 * - Header opcional
 * - Animação suave
 * - Layout responsivo
 * - Variações de largura para realismo
 */
export function TableSkeleton({ 
  rows = 5, 
  columns = 4, 
  showHeader = true,
  className 
}: TableSkeletonProps) {
  // Variações de largura para mais realismo
  const columnWidths = [
    'w-16',  // Pequena - Checkbox/ID
    'w-32',  // Média - Nome
    'w-24',  // Pequena-Média - Status
    'w-20',  // Pequena - Ações
    'w-28',  // Média - Data
    'w-36',  // Grande - Email
  ];

  return (
    <div className={cn("w-full", className)}>
      {/* Header Skeleton */}
      {showHeader && (
        <div className="border-b border-border/40 pb-3 mb-4">
          <div className="flex items-center space-x-4">
            {Array.from({ length: columns }).map((_, index) => (
              <Skeleton
                key={`header-${index}`}
                className={cn(
                  "h-4",
                  columnWidths[index % columnWidths.length]
                )}
              />
            ))}
          </div>
        </div>
      )}

      {/* Body Skeleton */}
      <div className="space-y-3">
        {Array.from({ length: rows }).map((_, rowIndex) => (
          <div key={`row-${rowIndex}`} className="flex items-center space-x-4">
            {Array.from({ length: columns }).map((_, colIndex) => (
              <Skeleton
                key={`cell-${rowIndex}-${colIndex}`}
                className={cn(
                  "h-5",
                  columnWidths[colIndex % columnWidths.length],
                  // Adiciona variação na altura para mais realismo
                  rowIndex % 3 === 0 && colIndex === 1 && "h-6",
                  // Primeira coluna geralmente é menor (checkbox/select)
                  colIndex === 0 && "w-5 h-5 rounded-sm"
                )}
              />
            ))}
          </div>
        ))}
      </div>

      {/* Footer Skeleton (Paginação) */}
      <div className="flex items-center justify-between pt-4 mt-4 border-t border-border/40">
        <div className="flex items-center space-x-2">
          <Skeleton className="h-4 w-32" />
          <Skeleton className="h-4 w-16" />
        </div>
        
        <div className="flex items-center space-x-2">
          <Skeleton className="h-8 w-20" />
          <Skeleton className="h-8 w-8" />
          <Skeleton className="h-8 w-8" />
          <Skeleton className="h-8 w-8" />
          <Skeleton className="h-8 w-20" />
        </div>
      </div>
    </div>
  );
}

/**
 * Skeleton loader simples para uma única linha de tabela
 */
export function TableRowSkeleton({ columns = 4 }: { columns?: number }) {
  const columnWidths = ['w-16', 'w-32', 'w-24', 'w-20', 'w-28', 'w-36'];

  return (
    <div className="flex items-center space-x-4 py-2">
      {Array.from({ length: columns }).map((_, index) => (
        <Skeleton
          key={index}
          className={cn(
            "h-5",
            columnWidths[index % columnWidths.length]
          )}
        />
      ))}
    </div>
  );
}

export default TableSkeleton; 
import React from 'react';
import { Card, CardContent } from '@/components/ui/card';

interface ResumeData {
    current_page?: number;
    last_page?: number;
    from?: number;
    to?: number;
    total?: number;
    per_page?: number;
}

interface ResumeProps {
    data: any[];
    columns: any[];
    filters: any[];
    pagination?: ResumeData;
    activeFiltersCount?: number;
}

export default function Resume({ 
    data, 
    columns, 
    filters, 
    pagination, 
    activeFiltersCount = 0 
}: ResumeProps) {
    const stats: Array<{
        label: string;
        value: string | number;
        icon: string;
    }> = [
        {
            label: 'Registros Exibidos',
            value: data.length,
            icon: '📄'
        },
        {
            label: 'Total de Registros',
            value: pagination?.total || data.length,
            icon: '📊'
        },
        {
            label: 'Colunas',
            value: columns.length,
            icon: '📋'
        },
        {
            label: 'Filtros Disponíveis',
            value: filters.length,
            icon: '🔍'
        }
    ];

    // Adicionar estatística de filtros ativos se houver
    if (activeFiltersCount > 0) {
        stats.push({
            label: 'Filtros Ativos',
            value: activeFiltersCount,
            icon: '✅'
        });
    }

    // Adicionar estatística de paginação se houver
    if (pagination && pagination.last_page && pagination.last_page > 1) {
        stats.push({
            label: 'Páginas',
            value: `${pagination.current_page}/${pagination.last_page}`,
            icon: '📑'
        });
    }

    return (
        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            {stats.map((stat, index) => (
                <Card key={index}>
                    <CardContent className="p-4">
                        <div className="flex items-center gap-2 mb-2">
                            <span className="text-lg">{stat.icon}</span>
                            <div className="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                {stat.value}
                            </div>
                        </div>
                        <div className="text-sm text-gray-600 dark:text-gray-400">
                            {stat.label}
                        </div>
                    </CardContent>
                </Card>
            ))}
        </div>
    );
} 
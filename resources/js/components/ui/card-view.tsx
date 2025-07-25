import React from 'react';
import { 
    Construction,
    LayoutGrid
} from 'lucide-react';
import { ViewConfig } from '../../types';

interface CardViewProps {
    data: any[];
    columns: any[];
    config: ViewConfig['config'];
    actions?: any;
    className?: string;
}

export default function CardView({
    data = [],
    columns = [],
    config = {},
    actions = {},
    className = ''
}: CardViewProps) {
    return (
        <div className="flex flex-col items-center justify-center py-16 text-center">
            <div className="rounded-full bg-green-100 p-4 dark:bg-green-900/20">
                <Construction className="h-8 w-8 text-green-600 dark:text-green-400" />
            </div>
            <h3 className="mt-6 text-lg font-semibold text-gray-900 dark:text-gray-100">
                Visualização em Cards
            </h3>
            <p className="mt-2 text-sm text-gray-500 dark:text-gray-400 max-w-md">
                Esta funcionalidade está em desenvolvimento e será implementada em breve.
            </p>
            <div className="mt-4 flex items-center gap-2 text-xs text-green-600 dark:text-green-400">
                <LayoutGrid className="h-4 w-4" />
                <span>Grid responsivo de cards em breve</span>
            </div>
        </div>
    );
} 
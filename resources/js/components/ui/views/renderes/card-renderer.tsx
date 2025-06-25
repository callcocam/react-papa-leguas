import React from 'react';
import { 
    Construction,
    LayoutGrid
} from 'lucide-react';
import { ViewConfig } from '../../../../types';

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
            Vamos renderizar um card aqui
        </div>
    );
} 
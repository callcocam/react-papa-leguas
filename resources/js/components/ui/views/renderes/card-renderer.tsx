import React from 'react';
import { 
    Construction,
    LayoutGrid
} from 'lucide-react';
import { ViewConfig } from '../../../../types'; 
import Cards from '../../../papa-leguas/cards/cards';

interface CardRendererProps {
    data: any[];
    columns: any[];
    config: ViewConfig['config'];
    actions?: any;
    className?: string;
}

export default function CardRenderer({
    data = [],
    columns = [],
    config = {},
    actions = {},
    className = ''
}: CardRendererProps) {
    return (
         <Cards data={data} columns={columns} config={config} actions={actions} className={className} />
    );
} 
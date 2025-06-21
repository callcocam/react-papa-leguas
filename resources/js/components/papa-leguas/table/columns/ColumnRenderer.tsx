import React from 'react'; 
import { type TableColumn } from '../../types';
import { getColumnRenderers } from './renderers';

export interface ColumnRendererProps {
    column: TableColumn;
    item: any;
    value: any;
}

export default function ColumnRenderer({ column, item, value }: ColumnRendererProps) {
    // Verificação de segurança
    if (!column || typeof column !== 'object') {
        console.warn('⚠️ Coluna inválida:', column);
        return null;
    } 

    const renderers = getColumnRenderers(); 
    // Prioridade 1: Usar `renderAs` se definido
    // Prioridade 2: Usar `type` como fallback
    // Prioridade 3: Usar 'text' como padrão
    let type = column.renderAs || column.type; 

    if(!type || !renderers[type]){
        if(!type) console.log('❌ ERRO: Tipo de coluna não definido! Usando renderer padrão.');
        if(type && !renderers[type]) console.log(`❌ ERRO: Renderer para o tipo '${type}' NÃO encontrado! Usando renderer padrão.`);
        
        console.log('Renderers disponíveis:', Object.keys(renderers));
        type = 'text';
    }
    
    // Seleciona o renderer apropriado
    const Renderer = renderers[type] || renderers.default;
      
    return <Renderer value={value} item={item} column={column} />;
} 
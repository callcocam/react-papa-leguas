import React from 'react';
import { TableColumn, TableRow } from '../../types';
import { getEditRenderers } from './renderers';

export interface EditRendererProps {
    item: TableRow;
    column: TableColumn;
    value: string;
    onValueChange: (value: string) => void;
}

const EditRenderer: React.FC<EditRendererProps> = ({ item, column, value, onValueChange }) => {
    // Verificação de segurança
    if (!column || typeof column !== 'object') {
        console.warn('⚠️ Coluna inválida:', column);
        return null;
    }

    const renderers = getEditRenderers();
    let editorType: string;

    const hasOptions = column.options && Array.isArray(column.options) && column.options.length > 0;

    if (hasOptions) {
        editorType = 'editable-select';
    } else {
        editorType = column.renderAs || column.type || 'text-editor';
    }
    console.log(editorType);
    const Renderer = Object.prototype.hasOwnProperty.call(renderers, editorType)
        ? renderers[editorType as keyof typeof renderers]
        : renderers.default;


    if (!Renderer) {
        console.error(`❌ ERRO: Nenhum editor encontrado para o tipo '${editorType}' e nenhum editor padrão definido.`);
        return <div>Editor não encontrado</div>;
    }

    return <Renderer value={value} onValueChange={onValueChange} item={item} column={column} />;
};

export default EditRenderer; 
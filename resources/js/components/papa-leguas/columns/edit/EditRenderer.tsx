import React from 'react';
import { type TableColumn } from '../../types';
import { getEditRenderers } from './renderers';
import { EditorProps } from './renderers/TextEditor';

export interface EditRendererProps extends EditorProps {
    column: TableColumn;
}

export default function EditRenderer({ column, value, onValueChange }: EditRendererProps) {
    // Verificação de segurança
    if (!column || typeof column !== 'object') {
        console.warn('⚠️ Coluna inválida para edição:', column);
        return null;
    }

    const renderers = getEditRenderers();

    // Usa a propriedade `editor` vinda do backend, com fallback para 'text'
    const editorType = column.renderAs || 'text';

    // typescript-eslint: Element implicitly has an 'any' type
    // Corrigido: verificamos se a chave existe antes de usá-la.
    const Renderer = Object.prototype.hasOwnProperty.call(renderers, editorType)
        ? renderers[editorType as keyof typeof renderers]
        : renderers.default;

    if (!Renderer) {
        console.error(`❌ ERRO: Nenhum editor encontrado para o tipo '${editorType}' e nenhum editor padrão definido.`);
        return <div>Editor não encontrado</div>;
    }

    return <Renderer value={value} onValueChange={onValueChange} column={column} />;
} 
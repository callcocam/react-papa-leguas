import React from 'react';
import { Input } from '@/components/ui/input';

export interface EditorProps {
    value: string;
    onValueChange: (value: string) => void;
    // Podemos adicionar mais props no futuro, como 'disabled', 'placeholder', etc.
}

const TextEditor: React.FC<EditorProps> = ({ value, onValueChange }) => {
    return (
        <Input
            type="text"
            value={value}
            onChange={(e) => onValueChange(e.target.value)}
            autoFocus
            onKeyDown={(e) => e.stopPropagation()} // Impede que atalhos da tabela (ex: setas) interfiram na edição
        />
    );
};

export default TextEditor; 
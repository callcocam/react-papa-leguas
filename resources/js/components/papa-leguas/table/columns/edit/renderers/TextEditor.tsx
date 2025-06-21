import { Input } from '@/components/ui/input';
import React from 'react';
import { EditorProps } from './types';

const TextEditor: React.FC<EditorProps> = ({ value, onValueChange }) => {
    return (
        <Input
            type="text"
            value={value || ''}
            onChange={(e) => onValueChange(e.target.value)}
            autoFocus
            onFocus={(e) => e.target.select()}
        />
    );
};

export default TextEditor; 
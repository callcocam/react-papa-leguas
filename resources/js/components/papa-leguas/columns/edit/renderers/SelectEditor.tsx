import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import React from 'react';
import { EditorProps } from './types';

const SelectEditor: React.FC<EditorProps> = ({ value, onValueChange, column }) => {
    const options = column.options || [];

    return (
        <Select
            value={String(value)}
            onValueChange={onValueChange}
        >
            <SelectTrigger>
                <SelectValue placeholder="Selecione uma opção" />
            </SelectTrigger>
            <SelectContent>
                {options.map((option: { value: any; label: string }) => (
                    <SelectItem key={option.value} value={String(option.value)}>
                        {option.label}
                    </SelectItem>
                ))}
            </SelectContent>
        </Select>
    );
};

export default SelectEditor; 
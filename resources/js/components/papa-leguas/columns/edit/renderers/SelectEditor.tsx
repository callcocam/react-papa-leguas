import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import React from 'react'; 

export interface EditorProps {
    value: string;
    onValueChange: (value: string) => void;
    // Podemos adicionar mais props no futuro, como 'disabled', 'placeholder', etc.
}

const SelectEditor: React.FC<EditorProps> = ({ value, onValueChange }) => {
    return (
        <Select
            value={value}
            onValueChange={onValueChange}
        >
            <SelectTrigger>
                <SelectValue placeholder="Selecione uma opção" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="1">Opção 1</SelectItem> 
            </SelectContent>
        </Select>
    );
};

export default SelectEditor; 
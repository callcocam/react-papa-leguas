import React, { useState, useEffect, useContext } from 'react';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { Loader2, X, Check } from 'lucide-react';
import get from 'lodash/get';
import { TableContext, TableContextProps } from '../../contexts/TableContext';
import { useActionProcessor } from '../../hooks/useActionProcessor';
import { TableColumn } from '../../types';
import SelectEditor from '../edit/renderers/SelectEditor'; 
import ColumnEditRenderer from '../edit/ColumnEditRenderer';

// Helper para extrair o valor de texto para edição
const getEditableValue = (value: any): string => {
    if (value && typeof value === 'object' && value.value !== undefined) {
        // Prioriza o valor bruto (value) para edição, não o formatado (formatted)
        return String(value.value);
    }
    // Fallback para valores primitivos ou objetos que não têm a nossa estrutura
    return String(value ?? '');
};

// Tipos locais para este componente
type DataItem = Record<string, any>;

interface EditableCellProps {
    item: DataItem;
    column: TableColumn;
}

const EditableCell: React.FC<EditableCellProps> = ({ item, column }) => {
    const { tableData, setTableData, meta } = useContext(TableContext) as TableContextProps;
    const { processAction, isLoading } = useActionProcessor();
    
    // O valor bruto (rawValue) pode ser um objeto { value, formatted, ... }
    const rawValue = column.key ? get(item, column.key, '') : '';

    // O estado 'currentValue' deve guardar APENAS a string para o input
    const [currentValue, setCurrentValue] = useState(() => getEditableValue(rawValue));
    const [isEditing, setIsEditing] = useState(false);

    useEffect(() => {
        // Garante que o valor de edição seja atualizado se o item mudar externamente
        const newRawValue = column.key ? get(item, column.key, '') : '';
        setCurrentValue(getEditableValue(newRawValue));
    }, [item, column.key]);
    
    const handleUpdate = async () => {
        console.log('item:', item, 'column:', column, 'meta:', meta);
        if (!column.key || !meta?.key || !item?.id) {
            console.error("Dados incompletos para a ação (coluna, tabela ou id do item). Ação cancelada.");
            return;
        }

        const result = await processAction({
            table: meta.key,
            actionKey: column.key,
            item: { id: item.id },
            data: { value: currentValue },
        }); 
        if (result?.success) {
            setIsEditing(false);
            // Atualiza o estado localmente para evitar recarregar a página
            if (tableData && setTableData && column.key) {
                const updatedData = tableData.map((d: DataItem) => {
                    if (d.id === item.id) {
                        const newStringValue = result.value ?? currentValue;
                        // Para manter a reatividade, criamos um novo objeto de valor para a célula
                        const newValueForCell = {
                            ...(typeof rawValue === 'object' ? rawValue : {}),
                            value: newStringValue,
                            formatted: newStringValue,
                        };
                        return { ...d, [column.key as string]: newValueForCell };
                    }
                    return d;
                });
                setTableData(updatedData);
            }
        } else {
            // Em caso de erro, reverte para o valor original
            setCurrentValue(getEditableValue(rawValue));
            console.log('result:', result?.message);
            // alert(result?.message || 'Falha ao atualizar o valor.');
        }
    };

    return (
        <Popover open={isEditing} onOpenChange={setIsEditing}>
            <PopoverTrigger asChild>
                <div className="cursor-pointer w-full h-full p-2 -m-2 hover:bg-muted/50 rounded-md transition-colors">
                    <ColumnEditRenderer value={rawValue} item={item} column={column} />
                </div>
            </PopoverTrigger>
            <PopoverContent className="w-80 p-4" onOpenAutoFocus={(e) => e.preventDefault()}>
                <div className="grid gap-4">
                    <div className="space-y-2">
                        <h4 className="font-medium leading-none">{`Editar ${column.label}`}</h4>
                        <p className="text-sm text-muted-foreground">
                            Altere o valor e clique em salvar.
                        </p>
                    </div>
                    <div className="grid gap-2">
                        <SelectEditor
                            item={item}
                            column={column}
                            value={currentValue}
                            onValueChange={setCurrentValue}
                        />
                    </div>
                    <div className="flex justify-end gap-2 mt-2">
                        <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => setIsEditing(false)}
                            disabled={isLoading}
                        >
                            <X className="h-4 w-4" />
                        </Button>
                        <Button onClick={handleUpdate} size="icon" disabled={isLoading}>
                            {isLoading ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                            ) : (
                                <Check className="h-4 w-4" />
                            )}
                        </Button>
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    );
};

export default EditableCell; 
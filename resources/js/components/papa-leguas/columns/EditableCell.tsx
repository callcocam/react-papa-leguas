import React, { useState, useEffect, useContext } from 'react';
import { TableColumn } from '../types';
import ColumnRenderer, { ColumnRendererProps } from './ColumnRenderer';
import { EditPopover } from './edit/EditPopover';
import get from 'lodash/get';
import { TableContext } from '../contexts/TableContext';
import { useActionProcessor } from '../hooks/useActionProcessor';

// Tipos locais para este componente
type DataItem = Record<string, any>;

interface EditableCellProps {
    item: DataItem;
    column: TableColumn;
}

const EditableCell: React.FC<EditableCellProps> = ({ item, column }) => {
    const { tableData, setTableData } = useContext(TableContext);
    const { processAction, isLoading } = useActionProcessor();
    
    const initialValue = column.key ? get(item, column.key, '') : '';
    const [currentValue, setCurrentValue] = useState(initialValue);
    const [isEditing, setIsEditing] = useState(false);

    useEffect(() => {
        // Garante que o valor seja atualizado se o item mudar externamente
        setCurrentValue(column.key ? get(item, column.key, '') : '');
    }, [item, column.key]);
    
    const handleUpdate = async () => {
        if (!column.key) return;

        const actionKey = column.key; // A chave da ação é a mesma da coluna
        
        const result = await processAction({
            actionKey,
            item,
            data: { value: currentValue }, // Enviamos o novo valor no payload
        });

        if (result?.success) {
            setIsEditing(false);
            // Atualiza o estado localmente para evitar recarregar a página
            if (tableData && setTableData && column.key) {
                const updatedData = tableData.map(d => {
                    if (d.id === item.id) {
                        return { ...d, [column.key as string]: result.value ?? currentValue };
                    }
                    return d;
                });
                setTableData(updatedData);
            }
        } else {
            // Em caso de erro, reverte para o valor original
            setCurrentValue(initialValue);
            alert(result?.message || 'Falha ao atualizar o valor.');
        }
    };

    const columnRendererProps: ColumnRendererProps = {
        item,
        column,
        value: column.key ? get(item, column.key) : '',
    };

    // O popover de edição agora é o container principal
    return (
        <EditPopover
            isEditing={isEditing}
            onIsEditingChange={setIsEditing}
            value={currentValue}
            onValueChange={setCurrentValue}
            onSave={handleUpdate}
            isLoading={isLoading}
            title={`Editar ${column.label}`}
        >
            <div 
                onClick={(e) => {
                    e.stopPropagation(); // Impede que o clique se propague para a linha
                    if (!isEditing) setIsEditing(true)
                }} 
                className="cursor-pointer w-full h-full p-2 -m-2 hover:bg-muted/50 rounded-md transition-colors"
            >
                {/* Renderizamos o valor que está sendo editado */}
                <ColumnRenderer {...columnRendererProps} value={currentValue} />
            </div>
        </EditPopover>
    );
};

export default EditableCell; 
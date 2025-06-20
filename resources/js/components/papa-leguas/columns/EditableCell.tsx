import React, { useState, useEffect } from 'react';
import { Column, DataItem, TableConfig } from '@rpl/components/papa-leguas/types';
import ColumnRenderer from '@rpl/components/papa-leguas/columns/ColumnRenderer';
import { EditPopover } from '@rpl/components/papa-leguas/columns/edit/EditPopover';
import get from 'lodash/get';

interface EditableCellProps {
    item: DataItem;
    column: Column;
    config: TableConfig;
}

const EditableCell: React.FC<EditableCellProps> = ({ item, column, config }) => {
    const [isEditing, setIsEditing] = useState(false);
    const initialValue = get(item, column.key, '');
    const [currentValue, setCurrentValue] = useState(initialValue);
    const [isLoading, setIsLoading] = useState(false);

    useEffect(() => {
        setCurrentValue(initialValue);
    }, [initialValue]);
    
    const handleUpdate = async () => {
        setIsLoading(true);
        try {
            const response = await fetch('/api/papaleguas/table/update', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
                },
                body: JSON.stringify({
                    model: config.model,
                    key: item.id,
                    field: column.key,
                    value: currentValue,
                }),
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Falha ao atualizar');
            }
            
            setIsEditing(false);
            // Recarregar a página é uma solução simples mas eficaz por enquanto.
            // Uma solução mais sofisticada poderia atualizar o estado no Inertia.
            window.location.reload(); 

        } catch (error) {
            console.error("Erro ao atualizar:", error);
            if (error instanceof Error) {
                alert(error.message);
            }
            // Reverter ao valor inicial em caso de erro
            setCurrentValue(initialValue);
        } finally {
            setIsLoading(false);
        }
    };

    if (!column.editable) {
        return <div className="p-2 -m-2"><ColumnRenderer item={item} column={column} /></div>;
    }

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
                onClick={() => !isEditing && setIsEditing(true)} 
                className="cursor-pointer w-full h-full p-2 -m-2 hover:bg-muted/50 rounded-md transition-colors"
            >
                {/* Renderiza o valor atual, que pode ter sido alterado no popover */}
                <ColumnRenderer item={{...item, [column.key]: currentValue}} column={column} />
            </div>
        </EditPopover>
    );
};

export default EditableCell; 
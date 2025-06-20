import React from 'react';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Check, X, Loader2 } from 'lucide-react';

interface EditPopoverProps {
    children: React.ReactNode;
    isEditing: boolean;
    onIsEditingChange: (isEditing: boolean) => void;
    value: string;
    onValueChange: (value: string) => void;
    onSave: () => void;
    isLoading: boolean;
    title?: string;
}

export const EditPopover: React.FC<EditPopoverProps> = ({
    children,
    isEditing,
    onIsEditingChange,
    value,
    onValueChange,
    onSave,
    isLoading,
    title,
}) => {
    const handleSave = () => {
        onSave();
    };

    const handleCancel = () => {
        onIsEditingChange(false);
    };

    return (
        <Popover open={isEditing} onOpenChange={onIsEditingChange}>
            <PopoverTrigger asChild>{children}</PopoverTrigger>
            <PopoverContent className="w-80">
                <div className="grid gap-4">
                    <div className="space-y-2">
                        <h4 className="font-medium leading-none">{title || 'Editar Valor'}</h4>
                        <p className="text-sm text-muted-foreground">
                            Altere o valor e clique em salvar.
                        </p>
                    </div>
                    <div className="grid gap-2">
                        {/* Aqui podemos adicionar diferentes tipos de input baseados em column.editOptions.type */}
                        <Input
                            id="edit-value"
                            value={value}
                            onChange={(e) => onValueChange(e.target.value)}
                            className="col-span-2 h-8"
                            disabled={isLoading}
                            autoFocus
                        />
                    </div>
                    <div className="flex justify-end gap-2">
                        <Button variant="ghost" size="sm" onClick={handleCancel} disabled={isLoading}>
                            <X className="h-4 w-4" />
                            <span className="sr-only">Cancelar</span>
                        </Button>
                        <Button size="sm" onClick={handleSave} disabled={isLoading}>
                            {isLoading ? (
                                <Loader2 className="h-4 w-4 animate-spin" />
                            ) : (
                                <Check className="h-4 w-4" />
                            )}
                            <span className="sr-only">Salvar</span>
                        </Button>
                    </div>
                </div>
            </PopoverContent>
        </Popover>
    );
}; 
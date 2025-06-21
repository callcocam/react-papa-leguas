import React, { createContext, useContext, useState, ReactNode, useCallback } from 'react';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';

type ConfirmationOptions = {
    title: string;
    message: string;
    confirmText?: string;
    cancelText?: string;
    confirmVariant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link';
    onConfirm: () => void;
    onCancel?: () => void;
};

type ConfirmationContextType = {
    confirm: (options: ConfirmationOptions) => void;
};

const ConfirmationDialogContext = createContext<ConfirmationContextType | undefined>(undefined);

export function ConfirmationDialogProvider({ children }: { children: ReactNode }) {
    const [options, setOptions] = useState<ConfirmationOptions | null>(null);

    const confirm = useCallback((options: ConfirmationOptions) => {
        setOptions(options);
    }, []);

    const handleClose = () => {
        setOptions(null);
    };

    const handleCancel = () => {
        if (options?.onCancel) {
            options.onCancel();
        }
        handleClose();
    };

    const handleConfirm = () => {
        if (options?.onConfirm) {
            options.onConfirm();
        }
        handleClose();
    };

    return (
        <ConfirmationDialogContext.Provider value={{ confirm }}>
            {children}
            {options && (
                 <AlertDialog open={options !== null}>
                    <AlertDialogContent onEscapeKeyDown={handleCancel}>
                        <AlertDialogHeader>
                            <AlertDialogTitle>{options.title}</AlertDialogTitle>
                            <AlertDialogDescription>
                                {options.message}
                            </AlertDialogDescription>
                        </AlertDialogHeader>
                        <AlertDialogFooter>
                            <AlertDialogCancel asChild>
                                <Button variant="outline" onClick={handleCancel}>
                                    {options.cancelText || 'Cancelar'}
                                </Button>
                            </AlertDialogCancel>
                            <AlertDialogAction asChild>
                                <Button variant={options.confirmVariant || 'default'} onClick={handleConfirm}>
                                    {options.confirmText || 'Confirmar'}
                                </Button>
                            </AlertDialogAction>
                        </AlertDialogFooter>
                    </AlertDialogContent>
                </AlertDialog>
            )}
        </ConfirmationDialogContext.Provider>
    );
}

export function useConfirmationDialog() {
    const context = useContext(ConfirmationDialogContext);
    if (context === undefined) {
        throw new Error('useConfirmationDialog must be used within a ConfirmationDialogProvider');
    }
    return context;
} 
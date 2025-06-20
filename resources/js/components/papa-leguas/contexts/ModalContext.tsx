import React, { createContext, useContext, useState, ReactNode, useCallback } from 'react';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
} from '@/components/ui/dialog';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetDescription,
} from '@/components/ui/sheet';

type ModalMode = 'modal' | 'slideover';

type ModalOptions = {
    title: string;
    description?: string;
    content: ReactNode;
    mode?: ModalMode;
};

type ModalContextType = {
    openModal: (options: ModalOptions) => void;
    closeModal: () => void;
};

const ModalContext = createContext<ModalContextType | undefined>(undefined);

export function ModalProvider({ children }: { children: ReactNode }) {
    const [options, setOptions] = useState<ModalOptions | null>(null);
    const [isOpen, setIsOpen] = useState(false);

    const openModal = useCallback((opts: ModalOptions) => {
        setOptions(opts);
        setIsOpen(true);
    }, []);

    const closeModal = useCallback(() => {
        setIsOpen(false);
        // Atraso para animação de saída
        setTimeout(() => setOptions(null), 300);
    }, []);

    const handleOpenChange = (open: boolean) => {
        if (!open) {
            closeModal();
        }
    };

    const renderContent = () => {
        if (!options) return null;

        if (options.mode === 'slideover') {
            return (
                <Sheet open={isOpen} onOpenChange={handleOpenChange}>
                    <SheetContent className="sm:max-w-xl">
                        <SheetHeader>
                            <SheetTitle>{options.title}</SheetTitle>
                            {options.description && (
                                <SheetDescription>{options.description}</SheetDescription>
                            )}
                        </SheetHeader>
                        <div className="py-4">{options.content}</div>
                    </SheetContent>
                </Sheet>
            );
        }

        // Padrão é 'modal'
        return (
            <Dialog open={isOpen} onOpenChange={handleOpenChange}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{options.title}</DialogTitle>
                        {options.description && (
                            <DialogDescription>{options.description}</DialogDescription>
                        )}
                    </DialogHeader>
                    <div>{options.content}</div>
                </DialogContent>
            </Dialog>
        );
    };
    
    return (
        <ModalContext.Provider value={{ openModal, closeModal }}>
            {children}
            {renderContent()}
        </ModalContext.Provider>
    );
}

export function useModal() {
    const context = useContext(ModalContext);
    if (context === undefined) {
        throw new Error('useModal must be used within a ModalProvider');
    }
    return context;
} 
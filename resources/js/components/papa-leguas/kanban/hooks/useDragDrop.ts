import { useState, useCallback } from 'react';
import { DragEndEvent, DragStartEvent, DragOverEvent } from '@dnd-kit/core';
import type { 
    DragDropConfig,  
} from '../types';

/**
 * Hook personalizado para gerenciar drag & drop no Kanban
 * 
 * Funcionalidades:
 * - Gerencia estado de arraste
 * - Valida transições entre colunas
 * - Executa chamadas para backend
 * - Fornece feedback visual
 */
export function useDragDrop(config: DragDropConfig) {
    const [activeId, setActiveId] = useState<string | null>(null);
    const [isDragging, setIsDragging] = useState(false);
    const [draggedItem, setDraggedItem] = useState<any>(null);
    const [draggedFromColumnId, setDraggedFromColumnId] = useState<string | null>(null);

    /**
     * Inicia o arraste
     */
    const handleDragStart = useCallback((event: DragStartEvent) => {
        const { active } = event;
        
        setActiveId(active.id as string);
        setIsDragging(true);
        setDraggedItem(active.data.current?.item || null);
        setDraggedFromColumnId(active.data.current?.columnId || null);
        
        // Callback customizado
        config.onDragStart?.(event);
        
        console.log('🎯 Drag Start:', {
            id: active.id,
            item: active.data.current?.item,
            fromColumnId: active.data.current?.columnId
        });
    }, [config]);

    /**
     * Durante o arraste (hover sobre colunas)
     */
    const handleDragOver = useCallback((event: DragOverEvent) => {
        const { active, over } = event;
        
        if (!over) return;
        
        // Callback customizado
        config.onDragOver?.(event);
        
        console.log('🎯 Drag Over:', {
            activeId: active.id,
            overId: over.id
        });
    }, [config]);

    /**
     * Finaliza o arraste
     */
    const handleDragEnd = useCallback(async (event: DragEndEvent) => {
        const { active, over } = event;
        
        setActiveId(null);
        setIsDragging(false);
        
        if (!over) {
            console.log('🎯 Drag cancelled - no drop target');
            setDraggedItem(null);
            setDraggedFromColumnId(null);
            return;
        }

        const fromColumnId = active.data.current?.columnId;
        const toColumnId = over.id as string;
        const item = active.data.current?.item;
        
        console.log('🎯 Drag End:', {
            cardId: active.id,
            fromColumnId,
            toColumnId,
            item
        });

        // Se não mudou de coluna, não fazer nada
        if (fromColumnId === toColumnId) {
            console.log('🎯 Same column - no action needed');
            setDraggedItem(null);
            return;
        }

        // Validar transição se configurada
        if (config.validateTransition) {
            const isValid = config.validateTransition(fromColumnId, toColumnId, item);
            if (!isValid) {
                console.log('🚫 Invalid transition:', fromColumnId, '→', toColumnId);
                setDraggedItem(null);
                return;
            }
        }

        // Executar movimento
        if (config.onMoveCard) {
            try {
                const success = await config.onMoveCard(
                    active.id as string,
                    fromColumnId,
                    toColumnId,
                    item
                );
                
                if (success) {
                    console.log('✅ Card moved successfully');
                } else {
                    console.log('❌ Failed to move card');
                }
            } catch (error) {
                console.error('❌ Error moving card:', error);
            }
        }
        
        // Callback customizado
        config.onDragEnd?.(event);
        
        setDraggedItem(null);
        setDraggedFromColumnId(null);
    }, [config]);

    /**
     * Cancela o arraste
     */
    const handleDragCancel = useCallback(() => {
        setActiveId(null);
        setIsDragging(false);
        setDraggedItem(null);
        setDraggedFromColumnId(null);
        
        console.log('🎯 Drag cancelled');
    }, []);

    return {
        // Estado
        activeId,
        isDragging,
        draggedItem,
        draggedFromColumnId,
        
        // Handlers
        handleDragStart,
        handleDragOver,
        handleDragEnd,
        handleDragCancel,
        
        // Utilities
        isCardDragging: (cardId: string) => activeId === cardId,
        isColumnOver: (columnId: string, overId: string | null) => overId === columnId,
    };
} 
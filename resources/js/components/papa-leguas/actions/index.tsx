// Componente principal
export { default as ActionRenderer } from './ActionRenderer';

// Hook para processamento programático
export { useActionProcessor } from './ActionRenderer';

// Funções para injeção/extensão de renderers
export { 
    addActionRenderer, 
    removeActionRenderer, 
    getActionRenderers, 
    hasActionRenderer 
} from './ActionRenderer';

// Renderers específicos
export { default as ButtonActionRenderer } from './renderers/ButtonActionRenderer';
export { default as LinkActionRenderer } from './renderers/LinkActionRenderer';
export { default as DropdownActionRenderer } from './renderers/DropdownActionRenderer';
export { default as CallbackActionRenderer } from './renderers/CallbackActionRenderer';

// Re-exportar tipos da interface
export type { ActionRendererProps, TableAction } from '../types'; 
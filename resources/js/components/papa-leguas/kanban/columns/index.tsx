// Componente principal
export { default as ColumnRenderer } from './ColumnRenderer';

// Funções para injeção/extensão de renderers
export { 
    addColumnRenderer, 
    removeColumnRenderer, 
    getColumnRenderers, 
    hasColumnRenderer 
} from './ColumnRenderer';

// Renderers específicos
// export { default as TextRenderer } from './renderers/TextRenderer';
// export { default as BadgeRenderer } from './renderers/BadgeRenderer';
// export { default as EmailRenderer } from './renderers/EmailRenderer';

// Re-exportar tipos da interface
export type { RendererProps, TableColumn } from '../../types'; 
// Componente principal
export { default as FilterRenderer } from './FilterRenderer';

// Funções para injeção/extensão de renderers
export { 
    addFilterRenderer, 
    removeFilterRenderer, 
    getFilterRenderers, 
    hasFilterRenderer 
} from './FilterRenderer';

// Renderers específicos
export { default as TextFilterRenderer } from './renderers/TextFilterRenderer';
export { default as SelectFilterRenderer } from './renderers/SelectFilterRenderer';
export { default as BooleanFilterRenderer } from './renderers/BooleanFilterRenderer';
export { default as DateFilterRenderer } from './renderers/DateFilterRenderer';
export { default as NumberFilterRenderer } from './renderers/NumberFilterRenderer';

// Re-exportar tipos da interface
export type { FilterRendererProps, TableFilter } from '../../types'; 
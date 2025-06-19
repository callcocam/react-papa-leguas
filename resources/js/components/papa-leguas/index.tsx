// Componente principal da tabela
export { default as DataTable } from './DataTable';

// Componentes da tabela separados
export { default as Filters } from './components/Filters';
export { default as Headers } from './components/Headers';
export { default as Table } from './components/Table';
export { default as TableBody } from './components/TableBody';
export { default as Pagination } from './components/Pagination';
export { default as Resume } from './components/Resume';

// Column Renderers
export { default as ColumnRenderer } from './columns/ColumnRenderer';
export { default as TextRenderer } from './columns/renderers/TextRenderer';
export { default as BadgeRenderer } from './columns/renderers/BadgeRenderer';
export { default as EmailRenderer } from './columns/renderers/EmailRenderer';

// Filter Renderers
export { default as FilterRenderer } from './filters/FilterRenderer';
export { default as TextFilterRenderer } from './filters/renderers/TextFilterRenderer';
export { default as SelectFilterRenderer } from './filters/renderers/SelectFilterRenderer';
export { default as BooleanFilterRenderer } from './filters/renderers/BooleanFilterRenderer';
export { default as DateFilterRenderer } from './filters/renderers/DateFilterRenderer';
export { default as NumberFilterRenderer } from './filters/renderers/NumberFilterRenderer';

// Action Renderers
export { default as ActionRenderer } from './actions/ActionRenderer';
export { default as ButtonActionRenderer } from './actions/renderers/ButtonActionRenderer';
export { default as LinkActionRenderer } from './actions/renderers/LinkActionRenderer';
export { default as DropdownActionRenderer } from './actions/renderers/DropdownActionRenderer';

// Tipos TypeScript
export type {
    RendererProps,
    TableColumn,
    TableFilter,
    FilterRendererProps,
    TableAction,
    ActionRendererProps,
    PapaLeguasTableProps
} from './types';

// Componente principal da tabela
export { default as DataTable } from './DataTable';

// Componentes individuais para uso separado
export { default as SearchField, useSearchField } from './SearchField';
export { default as Filters } from './table/components/Filters';
export { default as Table } from './table/components/Table';
export { default as PaginationWrapper, usePagination } from './PaginationWrapper';
export { default as Resume } from './table/components/Resume';

// Componentes internos da tabela (para uso avançado)
export { default as Headers } from './table/components/Headers';
export { default as TableBody } from './table/components/TableBody';
export { default as Pagination } from './table/components/Pagination';

// Column Renderers
export { default as ColumnRenderer } from './table/columns/ColumnRenderer';
export { default as TextRenderer } from './table/columns/renderers/TextRenderer';
export { default as BadgeRenderer } from './table/columns/renderers/BadgeRenderer';
export { default as EmailRenderer } from './table/columns/renderers/EmailRenderer';

// Filter Renderers
export { default as FilterRenderer } from './table/filters/FilterRenderer';
export { default as TextFilterRenderer } from './table/filters/renderers/TextFilterRenderer';
export { default as SelectFilterRenderer } from './table/filters/renderers/SelectFilterRenderer';
export { default as BooleanFilterRenderer } from './table/filters/renderers/BooleanFilterRenderer';
export { default as DateFilterRenderer } from './table/filters/renderers/DateFilterRenderer';
export { default as NumberFilterRenderer } from './table/filters/renderers/NumberFilterRenderer';

// Action Renderers
export { default as ActionRenderer } from './table/actions/ActionRenderer';
export { default as ButtonActionRenderer } from './table/actions/renderers/ButtonActionRenderer';
export { default as LinkActionRenderer } from './table/actions/renderers/LinkActionRenderer';
export { default as DropdownActionRenderer } from './table/actions/renderers/DropdownActionRenderer';

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

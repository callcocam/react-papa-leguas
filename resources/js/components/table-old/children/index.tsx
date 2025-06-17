// Exports centralizados dos componentes children para sintaxe declarativa

// Componente principal Table
export { Table, type TableProps } from './Table'

// Componentes de definição
export { Column, type ColumnProps } from './Column'
export { Content, type ContentProps } from './Content'
export { Rows, type RowsProps } from './Rows'

// Re-export do PapaLeguasTable para conveniência
export { PapaLeguasTable, type PapaLeguasTableProps } from '../index' 
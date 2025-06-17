import React from 'react'

/**
 * Componente Column para sintaxe declarativa
 * 
 * Define uma coluna da tabela via JSX:
 * <Column key="name" label="Nome" sortable filterable>
 *   <Content>{(row) => row.name}</Content>
 * </Column>
 */
export interface ColumnProps {
  // Identificação da coluna
  key: string
  label: string
  
  // Comportamento da coluna
  sortable?: boolean
  filterable?: boolean
  visible?: boolean
  width?: string | number
  
  // Tipo e formatação
  type?: 'text' | 'email' | 'money' | 'date' | 'status' | 'actions' | 'custom'
  format?: {
    currency?: 'BRL' | 'USD' | 'EUR'
    locale?: string
    dateFormat?: string
    [key: string]: any
  }
  
  // Permissões
  permission?: string | string[]
  
  // Styling
  className?: string
  headerClassName?: string
  cellClassName?: string
  
  // Configurações específicas por tipo
  options?: Array<{ value: any; label: string; disabled?: boolean }>
  
  // Children (Content component)
  children?: React.ReactNode
  
  // Props extras para extensibilidade
  [key: string]: any
}

/**
 * Componente Column
 * 
 * Este componente não renderiza nada diretamente.
 * Ele é parseado pelo componente pai (Table) para extrair
 * a configuração da coluna e o conteúdo customizado.
 */
export const Column: React.FC<ColumnProps> = ({ children, ...props }) => {
  // Este componente é apenas um placeholder
  // Ele é parseado pelo TableParser para extrair a configuração
  return null
}

// Definir displayName para o detector conseguir identificar
Column.displayName = 'Column'

export default Column 
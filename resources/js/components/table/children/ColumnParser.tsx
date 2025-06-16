import React from 'react'
import { ColumnConfig } from '../index'
import { getComponentName } from '../core/TableDetector'

/**
 * Função de renderização customizada extraída do Content
 */
export type CustomRowRenderer = (row: any, index: number, columns?: ColumnConfig[]) => React.ReactNode

/**
 * Configuração de coluna parseada dos children
 */
export interface ParsedColumnConfig extends ColumnConfig {
  source: 'children'
  hasCustomContent: boolean
  render?: (value: any, row: any, index: number) => React.ReactNode
}

/**
 * Parser inteligente de children para extrair configurações de colunas
 * 
 * @param children - React children para parsear
 * @returns Array de configurações de colunas
 */
export const parseChildrenColumns = (children: React.ReactNode): ParsedColumnConfig[] => {
  const columns: ParsedColumnConfig[] = []
  
  React.Children.forEach(children, (child) => {
    if (React.isValidElement(child) && getComponentName(child) === 'Column') {
      const props = child.props as any
      const { children: columnChildren, ...columnProps } = props
      
      // Parse Content child para extrair função de renderização
      const contentChild = React.Children.toArray(columnChildren)
        .find(c => React.isValidElement(c) && getComponentName(c) === 'Content')
      
      // Extrair função de renderização do Content
      const renderFunction = contentChild ? (contentChild as any).props.children : undefined
      
      const column: ParsedColumnConfig = {
        ...columnProps,
        source: 'children',
        hasCustomContent: !!contentChild,
        render: renderFunction
      }
      
      columns.push(column)
    }
  })
  
  return columns
}

/**
 * Parser para extrair customização de linhas dos children
 * 
 * @param children - React children para parsear
 * @returns Função de renderização customizada de linhas ou undefined
 */
export const parseChildrenRows = (children: React.ReactNode): CustomRowRenderer | undefined => {
  const rowsChild = React.Children.toArray(children)
    .find(child => React.isValidElement(child) && getComponentName(child) === 'Rows')
  
  return rowsChild ? (rowsChild as any).props.children : undefined
}

/**
 * Parser para extrair filtros customizados dos children
 * 
 * @param children - React children para parsear
 * @returns Array de configurações de filtros customizados
 */
export const parseChildrenFilters = (children: React.ReactNode): any[] => {
  const filters: any[] = []
  
  React.Children.forEach(children, (child) => {
    if (React.isValidElement(child) && getComponentName(child) === 'Filters') {
      // Implementar parsing de filtros customizados
      // Por ora retorna array vazio
    }
  })
  
  return filters
}

/**
 * Parser para extrair ações customizadas dos children
 * 
 * @param children - React children para parsear
 * @returns Array de configurações de ações customizadas
 */
export const parseChildrenActions = (children: React.ReactNode): any[] => {
  const actions: any[] = []
  
  React.Children.forEach(children, (child) => {
    if (React.isValidElement(child) && getComponentName(child) === 'Actions') {
      // Implementar parsing de ações customizadas
      // Por ora retorna array vazio
    }
  })
  
  return actions
}

/**
 * Valida se uma configuração de coluna é válida
 * 
 * @param column - Configuração da coluna para validar
 * @returns boolean indicando se é válida
 */
export const validateColumnConfig = (column: ParsedColumnConfig): boolean => {
  // Validações básicas
  if (!column.key || !column.label) {
    console.warn('Papa Leguas Table: Coluna deve ter "key" e "label"', column)
    return false
  }
  
  // Validar tipos suportados
  const supportedTypes = ['text', 'email', 'money', 'date', 'status', 'actions', 'custom']
  if (column.type && !supportedTypes.includes(column.type)) {
    console.warn(`Papa Leguas Table: Tipo "${column.type}" não suportado para coluna "${column.key}"`)
  }
  
  return true
}

/**
 * Gera relatório de parsing dos children
 * 
 * @param children - React children parseados
 * @returns Relatório detalhado do parsing
 */
export const generateParsingReport = (children: React.ReactNode) => {
  const columns = parseChildrenColumns(children)
  const customRows = parseChildrenRows(children)
  const filters = parseChildrenFilters(children)
  const actions = parseChildrenActions(children)
  
  const report = {
    summary: {
      totalChildren: React.Children.count(children),
      columnsFound: columns.length,
      customRowsFound: !!customRows,
      filtersFound: filters.length,
      actionsFound: actions.length
    },
    columns: columns.map(col => ({
      key: col.key,
      label: col.label,
      hasCustomContent: col.hasCustomContent,
      type: col.type || 'text',
      sortable: col.sortable || false,
      filterable: col.filterable || false
    })),
    warnings: [] as string[],
    recommendations: [] as string[]
  }
  
  // Validações e avisos
  columns.forEach(col => {
    if (!validateColumnConfig(col)) {
      report.warnings.push(`Coluna "${col.key}" tem configuração inválida`)
    }
    
    if (!col.hasCustomContent && !col.type) {
      report.recommendations.push(`Coluna "${col.key}" poderia ter um tipo definido para melhor renderização`)
    }
  })
  
  if (columns.length === 0) {
    report.warnings.push('Nenhuma coluna encontrada nos children')
    report.recommendations.push('Adicione componentes <Column> para definir as colunas')
  }
  
  return report
}

/**
 * Hook para usar o parser de children com cache e validação
 * 
 * @param children - React children para parsear
 * @returns Objeto com dados parseados e relatório
 */
export const useChildrenParser = (children: React.ReactNode) => {
  const parsedData = React.useMemo(() => {
    const columns = parseChildrenColumns(children)
    const customRows = parseChildrenRows(children)
    const filters = parseChildrenFilters(children)
    const actions = parseChildrenActions(children)
    
    return {
      columns,
      customRows,
      filters,
      actions
    }
  }, [children])
  
  const report = React.useMemo(() => {
    return generateParsingReport(children)
  }, [children])
  
  // Log de debug em desenvolvimento
  React.useEffect(() => {
    if (process.env.NODE_ENV === 'development') {
      console.group('🧩 Papa Leguas Children Parser')
      console.log('📊 Resumo:', report.summary)
      
      if (report.columns.length > 0) {
        console.log('📋 Colunas encontradas:', report.columns)
      }
      
      if (report.warnings.length > 0) {
        console.warn('⚠️ Avisos:', report.warnings)
      }
      
      if (report.recommendations.length > 0) {
        console.log('💡 Recomendações:', report.recommendations)
      }
      
      console.groupEnd()
    }
  }, [report])
  
  return {
    ...parsedData,
    report,
    hasColumns: parsedData.columns.length > 0,
    hasCustomRows: !!parsedData.customRows,
    isValid: report.warnings.length === 0
  }
} 
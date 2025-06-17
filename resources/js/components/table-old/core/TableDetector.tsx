import React from 'react'

// Tipos para detecção de modo da tabela
export interface TableMode {
  hasChildren: boolean
  hasPropsConfig: boolean
  mode: 'dynamic' | 'declarative' | 'hybrid'
  priority: 'children' | 'props' | 'merge'
  childrenCount: number
  columnsCount: number
}

// Configuração básica da tabela
export interface TableConfig {
  columns?: any[]
  filters?: any[]
  actions?: any[]
  bulkActions?: any[]
  [key: string]: any
}

// Componentes que identificamos como children válidos
const VALID_CHILDREN_TYPES = ['Column', 'Rows', 'Filters', 'Actions']

/**
 * Detecta o modo de operação da tabela baseado nos props e children
 * 
 * @param children - React children passados para a tabela
 * @param columns - Configuração de colunas via props
 * @param config - Configuração geral da tabela via props
 * @returns TableMode com informações sobre como renderizar a tabela
 */
export const detectTableMode = (
  children: React.ReactNode,
  columns?: any[],
  config?: TableConfig
): TableMode => {
  // Contar children válidos
  const childrenCount = React.Children.count(children)
  const hasValidChildren = childrenCount > 0 && hasValidTableChildren(children)
  
  // Verificar se tem configuração via props
  const columnsCount = columns?.length || 0
  const hasPropsConfig = !!(columnsCount || config?.filters?.length || config?.actions?.length)
  
  // Lógica de detecção baseada na presença de children e props
  if (hasValidChildren && hasPropsConfig) {
    return {
      hasChildren: true,
      hasPropsConfig: true,
      mode: 'hybrid',
      priority: 'children', // Children sempre tem prioridade
      childrenCount,
      columnsCount
    }
  }
  
  if (hasValidChildren) {
    return {
      hasChildren: true,
      hasPropsConfig: false,
      mode: 'declarative',
      priority: 'children',
      childrenCount,
      columnsCount: 0
    }
  }
  
  return {
    hasChildren: false,
    hasPropsConfig: true,
    mode: 'dynamic',
    priority: 'props',
    childrenCount: 0,
    columnsCount
  }
}

/**
 * Verifica se os children contêm componentes válidos da tabela
 * 
 * @param children - React children para verificar
 * @returns boolean indicando se tem children válidos
 */
export const hasValidTableChildren = (children: React.ReactNode): boolean => {
  let hasValidChildren = false
  
  React.Children.forEach(children, (child) => {
    if (React.isValidElement(child)) {
      const childType = getComponentName(child)
      if (VALID_CHILDREN_TYPES.includes(childType)) {
        hasValidChildren = true
      }
    }
  })
  
  return hasValidChildren
}

/**
 * Extrai o nome do componente React
 * 
 * @param element - Elemento React
 * @returns string com o nome do componente
 */
export const getComponentName = (element: React.ReactElement): string => {
  if (typeof element.type === 'string') {
    return element.type
  }
  
  if (typeof element.type === 'function') {
    return element.type.displayName || element.type.name || 'Unknown'
  }
  
  return 'Unknown'
}

/**
 * Detecta conflitos entre children e props (mesmas colunas definidas em ambos)
 * 
 * @param children - React children
 * @param columns - Colunas via props
 * @returns Array com as chaves que estão em conflito
 */
export const detectColumnConflicts = (
  children: React.ReactNode,
  columns?: any[]
): string[] => {
  if (!columns?.length) return []
  
  const childrenColumnKeys: string[] = []
  const propsColumnKeys = columns.map(col => col.key || col.id).filter(Boolean)
  
  // Extrair keys das colunas children
  React.Children.forEach(children, (child) => {
    if (React.isValidElement(child) && getComponentName(child) === 'Column') {
      const key = child.props.key || child.key
      if (key) {
        childrenColumnKeys.push(key)
      }
    }
  })
  
  // Encontrar conflitos (keys que existem em ambos)
  return childrenColumnKeys.filter(key => propsColumnKeys.includes(key))
}

/**
 * Gera relatório detalhado sobre o modo detectado (útil para debug)
 * 
 * @param mode - Modo detectado
 * @param conflicts - Conflitos detectados
 * @returns Objeto com informações detalhadas
 */
export const generateModeReport = (
  mode: TableMode,
  conflicts: string[] = []
): {
  summary: string
  details: string[]
  warnings: string[]
  recommendations: string[]
} => {
  const details: string[] = []
  const warnings: string[] = []
  const recommendations: string[] = []
  
  // Resumo baseado no modo
  let summary = ''
  switch (mode.mode) {
    case 'dynamic':
      summary = 'Tabela será renderizada via configuração de props (backend-driven)'
      details.push(`${mode.columnsCount} colunas configuradas via props`)
      break
      
    case 'declarative':
      summary = 'Tabela será renderizada via children declarativos (JSX)'
      details.push(`${mode.childrenCount} children declarativos encontrados`)
      break
      
    case 'hybrid':
      summary = 'Tabela será renderizada em modo híbrido (props + children)'
      details.push(`${mode.childrenCount} children declarativos`)
      details.push(`${mode.columnsCount} colunas via props`)
      details.push('Children terão prioridade sobre props em caso de conflito')
      break
  }
  
  // Avisos sobre conflitos
  if (conflicts.length > 0) {
    warnings.push(`Conflitos detectados nas colunas: ${conflicts.join(', ')}`)
    warnings.push('Children irão sobrescrever a configuração de props')
  }
  
  // Recomendações baseadas no modo
  if (mode.mode === 'hybrid' && conflicts.length === 0) {
    recommendations.push('Modo híbrido sem conflitos - configuração ideal!')
  }
  
  if (mode.mode === 'dynamic' && mode.columnsCount === 0) {
    warnings.push('Nenhuma coluna configurada - tabela pode ficar vazia')
    recommendations.push('Adicione configuração de colunas via props ou children')
  }
  
  return {
    summary,
    details,
    warnings,
    recommendations
  }
}

/**
 * Hook para usar o detector de tabela com informações detalhadas
 * 
 * @param children - React children
 * @param columns - Colunas via props
 * @param config - Configuração da tabela
 * @returns Objeto com modo detectado e informações adicionais
 */
export const useTableDetector = (
  children: React.ReactNode,
  columns?: any[],
  config?: TableConfig
) => {
  const mode = React.useMemo(() => {
    return detectTableMode(children, columns, config)
  }, [children, columns, config])
  
  const conflicts = React.useMemo(() => {
    return detectColumnConflicts(children, columns)
  }, [children, columns])
  
  const report = React.useMemo(() => {
    return generateModeReport(mode, conflicts)
  }, [mode, conflicts])
  
  // Log de debug em desenvolvimento
  React.useEffect(() => {
    if (process.env.NODE_ENV === 'development') {
      console.group('🔍 Papa Leguas Table Detector')
      console.log('📊 Modo:', mode.mode)
      console.log('📋 Resumo:', report.summary)
      
      if (report.details.length > 0) {
        console.log('📝 Detalhes:', report.details)
      }
      
      if (report.warnings.length > 0) {
        console.warn('⚠️ Avisos:', report.warnings)
      }
      
      if (report.recommendations.length > 0) {
        console.log('💡 Recomendações:', report.recommendations)
      }
      
      console.groupEnd()
    }
  }, [mode, report])
  
  return {
    mode,
    conflicts,
    report,
    isHybrid: mode.mode === 'hybrid',
    isDynamic: mode.mode === 'dynamic',
    isDeclarative: mode.mode === 'declarative',
    hasConflicts: conflicts.length > 0
  }
} 
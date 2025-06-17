import React from 'react'
import { ColumnConfig } from '../index'
import { ParsedColumnConfig, parseChildrenColumns } from './ColumnParser'

/**
 * Configuração de coluna mesclada (props + children)
 */
export interface MergedColumnConfig extends ColumnConfig {
  source: 'props' | 'children' | 'merged'
  hasConflict: boolean
  conflictResolution?: string
  originalProps?: ColumnConfig
  originalChildren?: ParsedColumnConfig
  mergeStrategy: 'props-only' | 'children-only' | 'children-override' | 'props-fallback'
}

/**
 * Resultado do processo de merge
 */
export interface ColumnMergeResult {
  columns: MergedColumnConfig[]
  conflicts: Array<{
    key: string
    field: string
    propsValue: any
    childrenValue: any
    resolution: 'children-wins' | 'props-fallback'
    reason: string
  }>
  summary: {
    totalColumns: number
    propsOnly: number
    childrenOnly: number
    merged: number
    conflictsResolved: number
  }
  warnings: string[]
  recommendations: string[]
}

/**
 * Estratégias de merge disponíveis
 */
export type MergeStrategy = 'children-priority' | 'props-priority' | 'strict-merge' | 'permissive-merge'

/**
 * Configurações do merger
 */
export interface MergerConfig {
  strategy: MergeStrategy
  allowConflicts: boolean
  validatePermissions: boolean
  debugMode: boolean
}

/**
 * Merge inteligente de colunas de props e children
 * 
 * @param propsColumns - Colunas vindas das props
 * @param children - React children para parsear
 * @param config - Configurações do merge
 * @returns Resultado do merge com colunas mescladas
 */
export const mergeColumns = (
  propsColumns: ColumnConfig[] = [],
  children: React.ReactNode,
  config: MergerConfig = {
    strategy: 'children-priority',
    allowConflicts: true,
    validatePermissions: true,
    debugMode: false
  }
): ColumnMergeResult => {
  // Parse children para extrair colunas
  const childrenColumns = parseChildrenColumns(children)
  
  // Mapas para facilitar lookup
  const propsMap = new Map<string, ColumnConfig>()
  const childrenMap = new Map<string, ParsedColumnConfig>()
  
  propsColumns.forEach(col => propsMap.set(col.key, col))
  childrenColumns.forEach(col => childrenMap.set(col.key, col))
  
  // Coletar todas as keys únicas
  const allKeys = new Set([
    ...propsColumns.map(col => col.key),
    ...childrenColumns.map(col => col.key)
  ])
  
  const mergedColumns: MergedColumnConfig[] = []
  const conflicts: ColumnMergeResult['conflicts'] = []
  const warnings: string[] = []
  const recommendations: string[] = []
  
  // Processar cada coluna
  allKeys.forEach(key => {
    const propsCol = propsMap.get(key)
    const childrenCol = childrenMap.get(key)
    
    if (propsCol && childrenCol) {
      // Ambas existem - fazer merge
      const mergeResult = mergeSingleColumn(propsCol, childrenCol, config)
      mergedColumns.push(mergeResult.column)
      conflicts.push(...mergeResult.conflicts)
      warnings.push(...mergeResult.warnings)
      recommendations.push(...mergeResult.recommendations)
      
    } else if (propsCol) {
      // Apenas props
      mergedColumns.push({
        ...propsCol,
        source: 'props',
        hasConflict: false,
        mergeStrategy: 'props-only'
      })
      
    } else if (childrenCol) {
      // Apenas children
      mergedColumns.push({
        ...childrenCol,
        source: 'children',
        hasConflict: false,
        mergeStrategy: 'children-only'
      })
    }
  })
  
  // Ordenar colunas (manter ordem dos children quando possível, senão props)
  const orderedColumns = sortMergedColumns(mergedColumns, propsColumns, childrenColumns)
  
  // Gerar resumo
  const summary = generateMergeSummary(orderedColumns, conflicts)
  
  // Validações finais
  const finalWarnings = [...warnings, ...validateMergedColumns(orderedColumns, config)]
  const finalRecommendations = [...recommendations, ...generateRecommendations(orderedColumns, conflicts)]
  
  return {
    columns: orderedColumns,
    conflicts,
    summary,
    warnings: finalWarnings,
    recommendations: finalRecommendations
  }
}

/**
 * Merge de uma única coluna (props + children)
 */
const mergeSingleColumn = (
  propsCol: ColumnConfig,
  childrenCol: ParsedColumnConfig,
  config: MergerConfig
): {
  column: MergedColumnConfig
  conflicts: ColumnMergeResult['conflicts']
  warnings: string[]
  recommendations: string[]
} => {
  const conflicts: ColumnMergeResult['conflicts'] = []
  const warnings: string[] = []
  const recommendations: string[] = []
  
  // Campos que podem ter conflito
  const conflictFields = ['label', 'sortable', 'filterable', 'visible', 'width', 'type', 'permission', 'className']
  
  // Detectar conflitos
  conflictFields.forEach(field => {
    const propsValue = (propsCol as any)[field]
    const childrenValue = (childrenCol as any)[field]
    
    if (propsValue !== undefined && childrenValue !== undefined && propsValue !== childrenValue) {
      conflicts.push({
        key: propsCol.key,
        field,
        propsValue,
        childrenValue,
        resolution: 'children-wins',
        reason: `Children tem prioridade sobre props para o campo "${field}"`
      })
    }
  })
  
  // Estratégia de merge baseada na configuração
  let mergedColumn: MergedColumnConfig
  
  switch (config.strategy) {
    case 'children-priority':
      mergedColumn = {
        ...propsCol,
        ...childrenCol,
        source: 'merged',
        hasConflict: conflicts.length > 0,
        conflictResolution: conflicts.length > 0 ? 'children-override' : undefined,
        originalProps: propsCol,
        originalChildren: childrenCol,
        mergeStrategy: 'children-override'
      }
      break
      
    case 'props-priority':
      mergedColumn = {
        ...childrenCol,
        ...propsCol,
        source: 'merged',
        hasConflict: conflicts.length > 0,
        conflictResolution: conflicts.length > 0 ? 'props-override' : undefined,
        originalProps: propsCol,
        originalChildren: childrenCol,
        mergeStrategy: 'props-fallback'
      }
      break
      
    case 'strict-merge':
      if (conflicts.length > 0 && !config.allowConflicts) {
        warnings.push(`Coluna "${propsCol.key}" tem conflitos e strategy é "strict-merge"`)
      }
      mergedColumn = {
        ...propsCol,
        ...childrenCol,
        source: 'merged',
        hasConflict: conflicts.length > 0,
        originalProps: propsCol,
        originalChildren: childrenCol,
        mergeStrategy: 'children-override'
      }
      break
      
    case 'permissive-merge':
    default:
      // Merge inteligente: children para UI, props para dados
      mergedColumn = {
        ...propsCol,
        // Children tem prioridade para aspectos visuais
        label: childrenCol.label || propsCol.label,
        className: childrenCol.className || propsCol.className,
        width: childrenCol.width || propsCol.width,
        // Children tem prioridade para comportamento
        sortable: childrenCol.sortable !== undefined ? childrenCol.sortable : propsCol.sortable,
        filterable: childrenCol.filterable !== undefined ? childrenCol.filterable : propsCol.filterable,
        visible: childrenCol.visible !== undefined ? childrenCol.visible : propsCol.visible,
        // Children sempre tem prioridade para renderização
        render: childrenCol.render || propsCol.render,
        // Props tem prioridade para tipo e permissões (dados do backend)
        type: propsCol.type || childrenCol.type,
        permission: propsCol.permission || childrenCol.permission,
        // Metadados do merge
        source: 'merged',
        hasConflict: conflicts.length > 0,
        originalProps: propsCol,
        originalChildren: childrenCol,
        mergeStrategy: 'children-override'
      }
      break
  }
  
  // Recomendações específicas
  if (childrenCol.hasCustomContent && propsCol.render) {
    recommendations.push(`Coluna "${propsCol.key}" tem render em props e Content em children. Children será usado.`)
  }
  
  if (conflicts.length > 0) {
    recommendations.push(`Coluna "${propsCol.key}" tem ${conflicts.length} conflito(s). Considere usar apenas uma sintaxe.`)
  }
  
  return {
    column: mergedColumn,
    conflicts,
    warnings,
    recommendations
  }
}

/**
 * Ordena colunas mescladas mantendo ordem lógica
 */
const sortMergedColumns = (
  mergedColumns: MergedColumnConfig[],
  propsColumns: ColumnConfig[],
  childrenColumns: ParsedColumnConfig[]
): MergedColumnConfig[] => {
  // Criar mapa de índices para ordenação
  const propsOrder = new Map<string, number>()
  const childrenOrder = new Map<string, number>()
  
  propsColumns.forEach((col, index) => propsOrder.set(col.key, index))
  childrenColumns.forEach((col, index) => childrenOrder.set(col.key, index))
  
  return mergedColumns.sort((a, b) => {
    // Prioridade: children order > props order > alfabética
    const aChildrenIndex = childrenOrder.get(a.key) ?? 999
    const bChildrenIndex = childrenOrder.get(b.key) ?? 999
    
    if (aChildrenIndex !== 999 || bChildrenIndex !== 999) {
      return aChildrenIndex - bChildrenIndex
    }
    
    const aPropsIndex = propsOrder.get(a.key) ?? 999
    const bPropsIndex = propsOrder.get(b.key) ?? 999
    
    if (aPropsIndex !== 999 || bPropsIndex !== 999) {
      return aPropsIndex - bPropsIndex
    }
    
    return a.key.localeCompare(b.key)
  })
}

/**
 * Gera resumo do merge
 */
const generateMergeSummary = (
  columns: MergedColumnConfig[],
  conflicts: ColumnMergeResult['conflicts']
): ColumnMergeResult['summary'] => {
  const summary = {
    totalColumns: columns.length,
    propsOnly: 0,
    childrenOnly: 0,
    merged: 0,
    conflictsResolved: conflicts.length
  }
  
  columns.forEach(col => {
    switch (col.mergeStrategy) {
      case 'props-only':
        summary.propsOnly++
        break
      case 'children-only':
        summary.childrenOnly++
        break
      case 'children-override':
      case 'props-fallback':
        summary.merged++
        break
    }
  })
  
  return summary
}

/**
 * Valida colunas mescladas
 */
const validateMergedColumns = (
  columns: MergedColumnConfig[],
  config: MergerConfig
): string[] => {
  const warnings: string[] = []
  
  // Verificar duplicatas
  const keys = columns.map(col => col.key)
  const duplicates = keys.filter((key, index) => keys.indexOf(key) !== index)
  if (duplicates.length > 0) {
    warnings.push(`Chaves duplicadas encontradas: ${duplicates.join(', ')}`)
  }
  
  // Verificar colunas sem label
  const noLabel = columns.filter(col => !col.label)
  if (noLabel.length > 0) {
    warnings.push(`Colunas sem label: ${noLabel.map(col => col.key).join(', ')}`)
  }
  
  // Verificar permissões se habilitado
  if (config.validatePermissions) {
    const invalidPermissions = columns.filter(col => 
      col.permission && typeof col.permission !== 'string' && !Array.isArray(col.permission)
    )
    if (invalidPermissions.length > 0) {
      warnings.push(`Permissões inválidas: ${invalidPermissions.map(col => col.key).join(', ')}`)
    }
  }
  
  return warnings
}

/**
 * Gera recomendações baseadas no merge
 */
const generateRecommendations = (
  columns: MergedColumnConfig[],
  conflicts: ColumnMergeResult['conflicts']
): string[] => {
  const recommendations: string[] = []
  
  // Recomendações sobre conflitos
  if (conflicts.length > 0) {
    const conflictKeys = [...new Set(conflicts.map(c => c.key))]
    recommendations.push(`${conflictKeys.length} coluna(s) com conflitos: ${conflictKeys.join(', ')}. Considere usar sintaxe única.`)
  }
  
  // Recomendações sobre performance
  const customRenderColumns = columns.filter(col => col.render || (col.originalChildren?.hasCustomContent))
  if (customRenderColumns.length > 5) {
    recommendations.push(`${customRenderColumns.length} colunas com renderização customizada. Considere otimização para performance.`)
  }
  
  // Recomendações sobre consistência
  const mixedSources = columns.some(col => col.source === 'props') && columns.some(col => col.source === 'children')
  if (mixedSources) {
    recommendations.push('Mistura de sintaxes detectada. Para consistência, considere usar apenas uma abordagem.')
  }
  
  return recommendations
}

/**
 * Hook para usar o merger com cache e validação
 */
export const useColumnMerger = (
  propsColumns: ColumnConfig[] = [],
  children: React.ReactNode,
  config?: Partial<MergerConfig>
) => {
  const mergerConfig: MergerConfig = {
    strategy: 'children-priority',
    allowConflicts: true,
    validatePermissions: true,
    debugMode: false,
    ...config
  }
  
  const mergeResult = React.useMemo(() => {
    return mergeColumns(propsColumns, children, mergerConfig)
  }, [propsColumns, children, mergerConfig])
  
  // Log de debug em desenvolvimento
  React.useEffect(() => {
    if (mergerConfig.debugMode && process.env.NODE_ENV === 'development') {
      console.group('🔀 Papa Leguas Column Merger')
      console.log('📊 Resumo do merge:', mergeResult.summary)
      
      if (mergeResult.conflicts.length > 0) {
        console.log('⚠️ Conflitos resolvidos:', mergeResult.conflicts)
      }
      
      if (mergeResult.warnings.length > 0) {
        console.warn('⚠️ Avisos:', mergeResult.warnings)
      }
      
      if (mergeResult.recommendations.length > 0) {
        console.log('💡 Recomendações:', mergeResult.recommendations)
      }
      
      console.log('📋 Colunas finais:', mergeResult.columns.map(col => ({
        key: col.key,
        source: col.source,
        strategy: col.mergeStrategy,
        hasConflict: col.hasConflict
      })))
      
      console.groupEnd()
    }
  }, [mergeResult, mergerConfig.debugMode])
  
  return {
    ...mergeResult,
    hasConflicts: mergeResult.conflicts.length > 0,
    hasWarnings: mergeResult.warnings.length > 0,
    isValid: mergeResult.warnings.length === 0,
    mergerConfig
  }
}

export default useColumnMerger 
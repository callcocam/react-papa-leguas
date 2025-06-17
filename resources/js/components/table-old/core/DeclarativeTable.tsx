import React from 'react'
import { PermissionsData } from '../index'
import { useChildrenParser, ParsedColumnConfig, CustomRowRenderer } from '../children/ColumnParser'

/**
 * Props do DeclarativeTable
 */
export interface DeclarativeTableProps {
  // Dados obrigatórios
  data: any[]
  children: React.ReactNode
  
  // Configurações opcionais
  permissions?: PermissionsData
  
  // Props de UI
  loading?: boolean
  className?: string
  onRowClick?: (row: any, index: number) => void
  onSelectionChange?: (selectedRows: any[]) => void
  
  // Configurações avançadas
  config?: {
    selectable?: boolean
    sortable?: boolean
    filterable?: boolean
    searchable?: boolean
    [key: string]: any
  }
  
  // Debug
  debug?: boolean
}

/**
 * DeclarativeTable - Renderização via children JSX
 * 
 * Este componente renderiza tabelas baseadas completamente em children
 * declarativos usando sintaxe JSX. Oferece máximo controle sobre a
 * renderização e permite customizações avançadas.
 */
export const DeclarativeTable: React.FC<DeclarativeTableProps> = ({
  data,
  children,
  permissions,
  loading = false,
  className = '',
  onRowClick,
  onSelectionChange,
  config = {},
  debug = false
}) => {
  // Parser dos children para extrair configurações
  const {
    columns,
    customRows,
    report,
    hasColumns,
    hasCustomRows,
    isValid
  } = useChildrenParser(children)
  
  // Estado local para seleção de linhas
  const [selectedRows, setSelectedRows] = React.useState<Set<string | number>>(new Set())
  const [selectAll, setSelectAll] = React.useState(false)
  
  // Estado para ordenação
  const [sortConfig, setSortConfig] = React.useState<{
    key: string
    direction: 'asc' | 'desc'
  } | null>(null)
  
  // Estado para filtros locais (baseados nas colunas filterable)
  const [activeFilters, setActiveFilters] = React.useState<Record<string, any>>({})
  
  // Colunas visíveis (filtradas por permissão)
  const visibleColumns = React.useMemo(() => {
    return columns.filter(column => {
      if (!column.permission) return true
      if (!permissions) return true
      
      const userPermissions = permissions.user_permissions || []
      const hasPermission = Array.isArray(column.permission)
        ? column.permission.some(perm => userPermissions.includes(perm))
        : userPermissions.includes(column.permission)
      
      return hasPermission || permissions.is_super_admin
    })
  }, [columns, permissions])
  
  // Dados processados (filtrados e ordenados)
  const processedData = React.useMemo(() => {
    let result = [...data]
    
    // Aplicar filtros
    Object.entries(activeFilters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        result = result.filter(row => {
          const cellValue = row[key]
          if (typeof value === 'string') {
            return String(cellValue).toLowerCase().includes(value.toLowerCase())
          }
          return cellValue === value
        })
      }
    })
    
    // Aplicar ordenação
    if (sortConfig) {
      result.sort((a, b) => {
        const aValue = a[sortConfig.key]
        const bValue = b[sortConfig.key]
        
        if (aValue < bValue) {
          return sortConfig.direction === 'asc' ? -1 : 1
        }
        if (aValue > bValue) {
          return sortConfig.direction === 'asc' ? 1 : -1
        }
        return 0
      })
    }
    
    return result
  }, [data, activeFilters, sortConfig])
  
  // Handlers
  const handleSort = (columnKey: string) => {
    const column = columns.find(col => col.key === columnKey)
    if (!column?.sortable) return
    
    setSortConfig(current => {
      if (current?.key === columnKey) {
        return current.direction === 'asc' 
          ? { key: columnKey, direction: 'desc' }
          : null
      }
      return { key: columnKey, direction: 'asc' }
    })
  }
  
  const handleSelectRow = (rowId: string | number) => {
    const newSelected = new Set(selectedRows)
    if (newSelected.has(rowId)) {
      newSelected.delete(rowId)
    } else {
      newSelected.add(rowId)
    }
    setSelectedRows(newSelected)
    
    // Callback para componente pai
    if (onSelectionChange) {
      const selectedData = data.filter(row => newSelected.has(row.id || row.key))
      onSelectionChange(selectedData)
    }
  }
  
  const handleSelectAll = () => {
    if (selectAll) {
      setSelectedRows(new Set())
      setSelectAll(false)
    } else {
      const allIds = processedData.map(row => row.id || row.key).filter(Boolean)
      setSelectedRows(new Set(allIds))
      setSelectAll(true)
    }
  }
  
  const renderCell = (row: any, column: ParsedColumnConfig, index: number) => {
    const value = row[column.key]
    
    // Se tem função render customizada (do Content)
    if (column.render) {
      return column.render(value, row, index)
    }
    
    // Renderização baseada no tipo (fallback)
    switch (column.type) {
      case 'email':
        return value ? (
          <a 
            href={`mailto:${value}`}
            className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
          >
            {value}
          </a>
        ) : null
        
      case 'money':
        return formatMoney(value, column.format?.currency || 'BRL')
        
      case 'date':
        return formatDate(value, column.format?.dateFormat)
        
      case 'status':
        return renderStatusBadge(value, column.format?.statusMap)
        
      default:
        return value
    }
  }
  
  const renderCustomRow = (row: any, index: number, customRowRenderer: CustomRowRenderer) => {
    return customRowRenderer(row, index, visibleColumns)
  }
  
  const renderDefaultRow = (row: any, index: number) => {
    return (
      <tr
        key={row.id || row.key || index}
        className={`hover:bg-gray-50 dark:hover:bg-gray-800 ${
          onRowClick ? 'cursor-pointer' : ''
        } ${selectedRows.has(row.id || row.key) ? 'bg-blue-50 dark:bg-blue-900/20' : ''}`}
        onClick={() => onRowClick?.(row, index)}
      >
        {/* Checkbox de seleção */}
        {config.selectable && (
          <td className="px-4 py-3">
            <input
              type="checkbox"
              checked={selectedRows.has(row.id || row.key)}
              onChange={(e) => {
                e.stopPropagation()
                handleSelectRow(row.id || row.key)
              }}
              className="rounded border-gray-300 dark:border-gray-600"
            />
          </td>
        )}
        
        {/* Células das colunas */}
        {visibleColumns.map(column => (
          <td
            key={column.key}
            className={`px-4 py-3 text-sm text-gray-900 dark:text-gray-100 ${
              column.cellClassName || ''
            }`}
          >
            {renderCell(row, column, index)}
          </td>
        ))}
      </tr>
    )
  }
  
  // Log de debug
  React.useEffect(() => {
    if (debug) {
      console.group('🧩 Papa Leguas DeclarativeTable - Debug')
      console.log('📊 Dados:', { 
        dataLength: data.length,
        columnsFound: columns.length,
        hasCustomRows,
        visibleColumns: visibleColumns.length
      })
      console.log('📋 Relatório de parsing:', report)
      console.groupEnd()
    }
  }, [debug, data, columns, hasCustomRows, visibleColumns, report])
  
  // Validação
  if (!isValid) {
    return (
      <div className={`border rounded-lg bg-white dark:bg-gray-900 ${className}`}>
        <div className="p-4 border border-red-200 rounded-lg bg-red-50 dark:bg-red-900/20 dark:border-red-800">
          <p className="text-red-600 dark:text-red-400 font-medium">
            Erro na configuração da tabela declarativa
          </p>
          <ul className="text-red-500 dark:text-red-300 text-sm mt-2 list-disc list-inside">
            {report.warnings.map((warning, index) => (
              <li key={index}>{warning}</li>
            ))}
          </ul>
          {report.recommendations.length > 0 && (
            <div className="mt-3">
              <p className="text-red-600 dark:text-red-400 font-medium text-sm">Recomendações:</p>
              <ul className="text-red-500 dark:text-red-300 text-sm mt-1 list-disc list-inside">
                {report.recommendations.map((rec, index) => (
                  <li key={index}>{rec}</li>
                ))}
              </ul>
            </div>
          )}
        </div>
      </div>
    )
  }
  
  if (loading) {
    return (
      <div className={`border rounded-lg bg-white dark:bg-gray-900 ${className}`}>
        <div className="p-8 text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto"></div>
          <p className="mt-2 text-gray-500 dark:text-gray-400">Carregando...</p>
        </div>
      </div>
    )
  }
  
  if (!hasColumns) {
    return (
      <div className={`border rounded-lg bg-white dark:bg-gray-900 ${className}`}>
        <div className="p-8 text-center">
          <p className="text-gray-500 dark:text-gray-400">
            Nenhuma coluna definida. Adicione componentes &lt;Column&gt; para configurar a tabela.
          </p>
        </div>
      </div>
    )
  }
  
  return (
    <div className={`border rounded-lg bg-white dark:bg-gray-900 ${className}`}>
      {/* Header com filtros (se houver colunas filterable) */}
      {visibleColumns.some(col => col.filterable) && (
        <div className="p-4 border-b border-gray-200 dark:border-gray-700">
          <div className="flex gap-2 flex-wrap">
            {visibleColumns
              .filter(col => col.filterable)
              .map(column => (
                <div key={column.key} className="min-w-[200px]">
                  <input
                    type="text"
                    placeholder={`Filtrar ${column.label}...`}
                    value={activeFilters[column.key] || ''}
                    onChange={(e) => 
                      setActiveFilters(prev => ({ ...prev, [column.key]: e.target.value }))
                    }
                    className="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
                  />
                </div>
              ))}
          </div>
        </div>
      )}
      
      {/* Tabela */}
      <div className="overflow-x-auto">
        <table className="w-full">
          <thead className="bg-gray-50 dark:bg-gray-800">
            <tr>
              {/* Checkbox para seleção */}
              {config.selectable && (
                <th className="w-12 px-4 py-3">
                  <input
                    type="checkbox"
                    checked={selectAll}
                    onChange={handleSelectAll}
                    className="rounded border-gray-300 dark:border-gray-600"
                  />
                </th>
              )}
              
              {/* Cabeçalhos das colunas */}
              {visibleColumns.map(column => (
                <th
                  key={column.key}
                  className={`px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider ${
                    column.sortable ? 'cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700' : ''
                  } ${column.headerClassName || ''}`}
                  style={{ width: column.width }}
                  onClick={() => handleSort(column.key)}
                >
                  <div className="flex items-center gap-2">
                    {column.label}
                    {column.sortable && (
                      <span className="text-gray-400">
                        {sortConfig?.key === column.key ? (
                          sortConfig.direction === 'asc' ? '↑' : '↓'
                        ) : '↕'}
                      </span>
                    )}
                  </div>
                </th>
              ))}
            </tr>
          </thead>
          
          <tbody className="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            {processedData.length === 0 ? (
              <tr>
                <td 
                  colSpan={visibleColumns.length + (config.selectable ? 1 : 0)}
                  className="px-4 py-8 text-center text-gray-500 dark:text-gray-400"
                >
                  Nenhum registro encontrado
                </td>
              </tr>
            ) : (
              processedData.map((row, index) => 
                hasCustomRows && customRows
                  ? renderCustomRow(row, index, customRows)
                  : renderDefaultRow(row, index)
              )
            )}
          </tbody>
        </table>
      </div>
    </div>
  )
}

// Funções auxiliares (reutilizadas do DynamicTable)
const formatMoney = (value: number, currency: string = 'BRL') => {
  if (!value) return '-'
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: currency
  }).format(value)
}

const formatDate = (value: string | Date, format?: string) => {
  if (!value) return '-'
  const date = new Date(value)
  return date.toLocaleDateString('pt-BR')
}

const renderStatusBadge = (value: string, statusMap?: Record<string, any>) => {
  if (!statusMap || !statusMap[value]) {
    return <span className="px-2 py-1 text-xs rounded bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">{value}</span>
  }
  
  const config = statusMap[value]
  return (
    <span className={`px-2 py-1 text-xs rounded ${config.className || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200'}`}>
      {config.label || value}
    </span>
  )
}

export default DeclarativeTable 
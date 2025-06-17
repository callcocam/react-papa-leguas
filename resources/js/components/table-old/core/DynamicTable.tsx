import React from 'react'
import { ColumnConfig, FilterConfig, ActionConfig, BulkActionConfig, PermissionsData, PaginationData } from '../index'

/**
 * Props do DynamicTable
 */
export interface DynamicTableProps {
  // Dados obrigatórios
  data: any[]
  columns: ColumnConfig[]
  
  // Configurações opcionais
  filters?: FilterConfig[]
  actions?: ActionConfig[]
  bulkActions?: BulkActionConfig[]
  permissions?: PermissionsData
  pagination?: PaginationData
  
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
    exportable?: boolean
    [key: string]: any
  }
}

/**
 * DynamicTable - Renderização via props (backend-driven)
 * 
 * Este componente renderiza tabelas baseadas completamente em configuração
 * vinda do backend via props. Ideal para casos onde toda a lógica de
 * apresentação é controlada pelo servidor.
 */
export const DynamicTable: React.FC<DynamicTableProps> = ({
  data,
  columns,
  filters = [],
  actions = [],
  bulkActions = [],
  permissions,
  pagination,
  loading = false,
  className = '',
  onRowClick,
  onSelectionChange,
  config = {}
}) => {
  // Estado local para seleção de linhas
  const [selectedRows, setSelectedRows] = React.useState<Set<string | number>>(new Set())
  const [selectAll, setSelectAll] = React.useState(false)
  
  // Estado para ordenação
  const [sortConfig, setSortConfig] = React.useState<{
    key: string
    direction: 'asc' | 'desc'
  } | null>(null)
  
  // Estado para filtros
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
  
  const renderCell = (row: any, column: ColumnConfig, index: number) => {
    const value = row[column.key]
    
    // Se tem função render customizada
    if (column.render) {
      return column.render(value, row, index)
    }
    
    // Renderização baseada no tipo
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
        
      case 'actions':
        return renderActions(row, actions, permissions)
        
      default:
        return value
    }
  }
  
  if (loading) {
    return (
      <div className={`border rounded-lg bg-white dark:bg-gray-900 ${className}`}>
        <div className="p-8 text-center">
          <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
          <p className="mt-2 text-gray-500 dark:text-gray-400">Carregando...</p>
        </div>
      </div>
    )
  }
  
  return (
    <div className={`border rounded-lg bg-white dark:bg-gray-900 ${className}`}>
      {/* Header com filtros e ações */}
      {(filters.length > 0 || bulkActions.length > 0) && (
        <div className="p-4 border-b border-gray-200 dark:border-gray-700">
          <div className="flex justify-between items-center">
            {/* Filtros */}
            {filters.length > 0 && (
              <div className="flex gap-2">
                {filters.map(filter => (
                  <div key={filter.key} className="min-w-[200px]">
                    {renderFilter(filter, activeFilters[filter.key], (value) => 
                      setActiveFilters(prev => ({ ...prev, [filter.key]: value }))
                    )}
                  </div>
                ))}
              </div>
            )}
            
            {/* Ações em massa */}
            {bulkActions.length > 0 && selectedRows.size > 0 && (
              <div className="flex gap-2">
                {bulkActions.map(action => (
                  <button
                    key={action.key}
                    className={`px-3 py-1 rounded text-sm ${getActionClasses(action.variant)}`}
                    onClick={() => handleBulkAction(action, Array.from(selectedRows))}
                  >
                    {action.label} ({selectedRows.size})
                  </button>
                ))}
              </div>
            )}
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
              processedData.map((row, index) => (
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
              ))
            )}
          </tbody>
        </table>
      </div>
      
      {/* Paginação */}
      {pagination && (
        <div className="px-4 py-3 border-t border-gray-200 dark:border-gray-700">
          {renderPagination(pagination)}
        </div>
      )}
    </div>
  )
}

// Funções auxiliares
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

const renderActions = (row: any, actions: ActionConfig[], permissions?: PermissionsData) => {
  return (
    <div className="flex gap-2">
      {actions.map(action => (
        <button
          key={action.key}
          className={`px-2 py-1 text-xs rounded ${getActionClasses(action.variant)}`}
          onClick={(e) => {
            e.stopPropagation()
            action.onClick?.(row)
          }}
        >
          {action.label}
        </button>
      ))}
    </div>
  )
}

const renderFilter = (filter: FilterConfig, value: any, onChange: (value: any) => void) => {
  switch (filter.type) {
    case 'text':
      return (
        <input
          type="text"
          placeholder={filter.placeholder || `Filtrar ${filter.label}`}
          value={value || ''}
          onChange={(e) => onChange(e.target.value)}
          className="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
        />
      )
    
    case 'select':
      return (
        <select
          value={value || ''}
          onChange={(e) => onChange(e.target.value)}
          className="w-full px-3 py-2 border border-gray-300 rounded-md dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100"
        >
          <option value="">Todos</option>
          {filter.options?.map(option => (
            <option key={option.value} value={option.value}>
              {option.label}
            </option>
          ))}
        </select>
      )
    
    default:
      return null
  }
}

const renderPagination = (pagination: PaginationData) => {
  return (
    <div className="flex items-center justify-between">
      <div className="text-sm text-gray-700 dark:text-gray-300">
        Mostrando {pagination.from} a {pagination.to} de {pagination.total} registros
      </div>
      <div className="flex gap-2">
        {pagination.links.map((link, index) => (
          <button
            key={index}
            className={`px-3 py-1 text-sm rounded ${
              link.active 
                ? 'bg-blue-600 text-white' 
                : 'bg-gray-100 text-gray-700 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600'
            }`}
            disabled={!link.url}
            dangerouslySetInnerHTML={{ __html: link.label }}
          />
        ))}
      </div>
    </div>
  )
}

const getActionClasses = (variant?: string) => {
  switch (variant) {
    case 'destructive':
      return 'bg-red-600 text-white hover:bg-red-700'
    case 'outline':
      return 'border border-gray-300 text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-800'
    default:
      return 'bg-blue-600 text-white hover:bg-blue-700'
  }
}

const handleBulkAction = (action: BulkActionConfig, selectedIds: (string | number)[]) => {
  // Implementar ações em massa
  console.log('Bulk action:', action.key, 'for items:', selectedIds)
}

export default DynamicTable 
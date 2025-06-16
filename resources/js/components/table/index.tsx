import React from 'react'
import { useTableDetector, type TableConfig } from './core/TableDetector'
import DynamicTable from './core/DynamicTable'
import DeclarativeTable from './core/DeclarativeTable'

// Tipos principais da tabela
export interface PapaLeguasTableProps {
  // Dados obrigatórios
  data: any[]
  
  // Props dinâmicas (vem do backend via Inertia)
  columns?: ColumnConfig[]
  filters?: FilterConfig[]
  actions?: ActionConfig[]
  bulkActions?: BulkActionConfig[]
  permissions?: PermissionsData
  pagination?: PaginationData
  
  // Props de UI e comportamento
  loading?: boolean
  className?: string
  onRowClick?: (row: any, index: number) => void
  onSelectionChange?: (selectedRows: any[]) => void
  
  // Children declarativos (sintaxe JSX)
  children?: React.ReactNode
  
  // Configurações avançadas
  config?: TableConfig
  debug?: boolean // Habilita logs detalhados
}

// Tipos básicos (serão expandidos em arquivos específicos)
export interface ColumnConfig {
  key: string
  label: string
  sortable?: boolean
  filterable?: boolean
  visible?: boolean
  width?: string
  type?: 'text' | 'email' | 'money' | 'date' | 'status' | 'actions' | 'custom'
  permission?: string | string[]
  className?: string
  render?: (value: any, row: any, index: number) => React.ReactNode
  [key: string]: any
}

export interface FilterConfig {
  key: string
  label: string
  type: 'text' | 'select' | 'date' | 'daterange' | 'number' | 'boolean'
  options?: Array<{ value: any; label: string }>
  placeholder?: string
  [key: string]: any
}

export interface ActionConfig {
  key: string
  label: string
  icon?: string
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost'
  permission?: string | string[]
  onClick?: (row: any) => void
  route?: string
  method?: 'get' | 'post' | 'put' | 'delete'
  requireConfirmation?: boolean
  [key: string]: any
}

export interface BulkActionConfig {
  key: string
  label: string
  icon?: string
  variant?: 'default' | 'destructive' | 'outline' | 'secondary'
  permission?: string | string[]
  onClick?: (selectedRows: any[]) => void
  route?: string
  method?: 'post' | 'put' | 'delete'
  requireConfirmation?: boolean
  maxSelection?: number
  [key: string]: any
}

export interface PermissionsData {
  user_permissions: string[]
  user_roles: string[]
  is_super_admin: boolean
  [key: string]: any
}

export interface PaginationData {
  current_page: number
  last_page: number
  per_page: number
  total: number
  from: number
  to: number
  links: Array<{
    url: string | null
    label: string
    active: boolean
  }>
  [key: string]: any
}

/**
 * Componente principal da tabela Papa Leguas
 * 
 * Suporta três modos de operação:
 * - Dynamic: Configuração via props (backend-driven)
 * - Declarative: Configuração via children (JSX)
 * - Hybrid: Ambos simultaneamente (children tem prioridade)
 */
export const PapaLeguasTable: React.FC<PapaLeguasTableProps> = ({
  children,
  columns,
  data,
  permissions,
  config,
  debug = false,
  ...props
}) => {
  // Usar o detector para determinar o modo de renderização
  const {
    mode,
    conflicts,
    report,
    isHybrid,
    isDynamic,
    isDeclarative,
    hasConflicts
  } = useTableDetector(children, columns, config)
  
  // Log adicional se debug estiver habilitado
  React.useEffect(() => {
    if (debug) {
      console.group('🚀 Papa Leguas Table - Debug Mode')
      console.log('🎯 Modo detectado:', mode.mode)
      console.log('📊 Props recebidas:', { 
        dataLength: data?.length || 0,
        columnsCount: columns?.length || 0,
        hasChildren: !!children,
        hasPermissions: !!permissions
      })
      
      if (hasConflicts) {
        console.warn('⚠️ Conflitos detectados:', conflicts)
      }
      
      console.groupEnd()
    }
  }, [debug, mode, data, columns, children, permissions, hasConflicts, conflicts])
  
  // Validação básica
  if (!data || !Array.isArray(data)) {
    console.error('❌ Papa Leguas Table: prop "data" é obrigatória e deve ser um array')
    return (
      <div className="p-4 border border-red-200 rounded-lg bg-red-50 dark:bg-red-900/20 dark:border-red-800">
        <p className="text-red-600 dark:text-red-400 font-medium">
          Erro: Dados da tabela não fornecidos ou inválidos
        </p>
        <p className="text-red-500 dark:text-red-300 text-sm mt-1">
          A prop "data" deve ser um array válido
        </p>
      </div>
    )
  }
  
  // Renderizar baseado no modo detectado
  switch (mode.mode) {
    case 'declarative':
      return (
        <DeclarativeTable 
          data={data} 
          permissions={permissions}
          {...props}
        >
          {children}
        </DeclarativeTable>
      )
    
    case 'hybrid':
      return (
        <HybridTable 
          data={data}
          permissions={permissions}
          columns={columns}
          config={config}
          conflicts={conflicts}
          {...props}
        >
          {children}
        </HybridTable>
      )
    
    case 'dynamic':
    default:
      return (
        <DynamicTable
          data={data}
          columns={columns || []}
          permissions={permissions}
          config={config}
          {...props}
        />
      )
  }
}





// Componente placeholder para HybridTable (será implementado depois)
const HybridTable: React.FC<any> = ({ data, columns, children, conflicts, ...props }) => {
  const childrenCount = React.Children.count(children)
  const columnsCount = columns?.length || 0
  
  return (
    <div className="border rounded-lg p-4 bg-white dark:bg-gray-900">
      <div className="mb-4 p-3 bg-purple-50 dark:bg-purple-900/20 rounded border border-purple-200 dark:border-purple-800">
        <h3 className="font-medium text-purple-800 dark:text-purple-200">
          🔀 Modo Híbrido (Props + Children)
        </h3>
        <p className="text-purple-600 dark:text-purple-300 text-sm mt-1">
          Combinação inteligente de props e children
        </p>
        <p className="text-purple-500 dark:text-purple-400 text-xs mt-1">
          {data.length} registros • {columnsCount} props + {childrenCount} children
        </p>
        
        {conflicts.length > 0 && (
          <div className="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded border border-yellow-200 dark:border-yellow-800">
            <p className="text-yellow-700 dark:text-yellow-300 text-xs">
              ⚠️ Conflitos: {conflicts.join(', ')} (children terão prioridade)
            </p>
          </div>
        )}
      </div>
      
      <div className="text-center py-8 text-gray-500 dark:text-gray-400">
        <p>🚧 HybridTable em desenvolvimento</p>
        <p className="text-sm mt-1">Será implementado na próxima etapa</p>
      </div>
    </div>
  )
}

// Export do componente principal
export default PapaLeguasTable

// Re-exports úteis
export { useTableDetector } from './core/TableDetector'
export type { TableMode, TableConfig } from './core/TableDetector' 
import React from 'react'
import { PapaLeguasTable } from '../index'
import { Table, Column, Content } from '../children'

// Dados de exemplo
const sampleUsers = [
  {
    id: 1,
    name: 'João Silva',
    email: 'joao@example.com',
    status: 'active',
    created_at: '2024-01-15',
    role: 'admin'
  },
  {
    id: 2,
    name: 'Maria Santos',
    email: 'maria@example.com',
    status: 'inactive',
    created_at: '2024-01-10',
    role: 'user'
  },
  {
    id: 3,
    name: 'Pedro Costa',
    email: 'pedro@example.com',
    status: 'pending',
    created_at: '2024-01-20',
    role: 'user'
  }
]

// Configuração de colunas para modo dinâmico
const dynamicColumns = [
  {
    key: 'id',
    label: 'ID',
    sortable: true,
    width: '80px'
  },
  {
    key: 'name',
    label: 'Nome',
    sortable: true,
    filterable: true
  },
  {
    key: 'email',
    label: 'Email',
    type: 'email' as const,
    filterable: true
  },
  {
    key: 'status',
    label: 'Status',
    type: 'status' as const,
    format: {
      statusMap: {
        active: { label: 'Ativo', className: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' },
        inactive: { label: 'Inativo', className: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' },
        pending: { label: 'Pendente', className: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }
      }
    }
  },
  {
    key: 'created_at',
    label: 'Criado em',
    type: 'date' as const,
    sortable: true
  }
]

// Configuração de filtros
const filters = [
  {
    key: 'name',
    label: 'Nome',
    type: 'text' as const,
    placeholder: 'Buscar por nome...'
  },
  {
    key: 'status',
    label: 'Status',
    type: 'select' as const,
    options: [
      { value: 'active', label: 'Ativo' },
      { value: 'inactive', label: 'Inativo' },
      { value: 'pending', label: 'Pendente' }
    ]
  }
]

// Configuração de ações
const actions = [
  {
    key: 'edit',
    label: 'Editar',
    variant: 'outline' as const,
    onClick: (row: any) => console.log('Editar:', row)
  },
  {
    key: 'delete',
    label: 'Excluir',
    variant: 'destructive' as const,
    onClick: (row: any) => console.log('Excluir:', row)
  }
]

// Permissões de exemplo
const permissions = {
  user_permissions: ['users.view', 'users.edit'],
  user_roles: ['admin'],
  is_super_admin: false
}

/**
 * Componente de exemplo demonstrando os três modos da tabela
 */
export const TableExample: React.FC = () => {
  const [currentMode, setCurrentMode] = React.useState<'dynamic' | 'declarative' | 'hybrid'>('dynamic')

  return (
    <div className="p-6 space-y-6">
      <div>
        <h1 className="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">
          Papa Leguas Table - Exemplos
        </h1>
        
        {/* Seletor de modo */}
        <div className="flex gap-2 mb-6">
          <button
            onClick={() => setCurrentMode('dynamic')}
            className={`px-4 py-2 rounded text-sm font-medium ${
              currentMode === 'dynamic'
                ? 'bg-blue-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300'
            }`}
          >
            🔧 Modo Dinâmico
          </button>
          <button
            onClick={() => setCurrentMode('declarative')}
            className={`px-4 py-2 rounded text-sm font-medium ${
              currentMode === 'declarative'
                ? 'bg-green-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300'
            }`}
          >
            🧩 Modo Declarativo
          </button>
          <button
            onClick={() => setCurrentMode('hybrid')}
            className={`px-4 py-2 rounded text-sm font-medium ${
              currentMode === 'hybrid'
                ? 'bg-purple-600 text-white'
                : 'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-300'
            }`}
          >
            🔀 Modo Híbrido
          </button>
        </div>
      </div>

      {/* Modo Dinâmico - Props */}
      {currentMode === 'dynamic' && (
        <div>
          <h2 className="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">
            Modo Dinâmico - Configuração via Props
          </h2>
          <p className="text-gray-600 dark:text-gray-400 mb-4">
            Tabela configurada completamente via props vindas do backend.
          </p>
          
          <PapaLeguasTable
            data={sampleUsers}
            columns={dynamicColumns}
            filters={filters}
            actions={actions}
            permissions={permissions}
            config={{
              selectable: true,
              sortable: true,
              filterable: true
            }}
            debug={true}
          />
        </div>
      )}

      {/* Modo Declarativo - Children */}
      {currentMode === 'declarative' && (
        <div>
          <h2 className="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">
            Modo Declarativo - Sintaxe JSX
          </h2>
          <p className="text-gray-600 dark:text-gray-400 mb-4">
            Tabela definida via componentes JSX com controle total sobre renderização.
          </p>
          
          <Table data={sampleUsers} permissions={permissions} debug={true}>
            <Column key="id" label="ID" sortable width="80px">
              <Content>
                {(user) => (
                  <span className="font-mono text-sm text-gray-500">
                    #{user.id}
                  </span>
                )}
              </Content>
            </Column>

            <Column key="name" label="Nome" sortable filterable>
              <Content>
                {(user) => (
                  <div className="flex items-center gap-2">
                    <div className="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                      {user.name.charAt(0)}
                    </div>
                    <span className="font-medium">{user.name}</span>
                  </div>
                )}
              </Content>
            </Column>

            <Column key="email" label="Email" filterable>
              <Content>
                {(user) => (
                  <a 
                    href={`mailto:${user.email}`}
                    className="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                  >
                    {user.email}
                  </a>
                )}
              </Content>
            </Column>

            <Column key="status" label="Status">
              <Content>
                {(user) => {
                  const statusConfig = {
                    active: { label: 'Ativo', className: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' },
                    inactive: { label: 'Inativo', className: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' },
                    pending: { label: 'Pendente', className: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' }
                  }
                  const config = statusConfig[user.status as keyof typeof statusConfig]
                  
                  return (
                    <span className={`px-2 py-1 text-xs rounded font-medium ${config.className}`}>
                      {config.label}
                    </span>
                  )
                }}
              </Content>
            </Column>

            <Column key="actions" label="Ações" width="120px">
              <Content>
                {(user) => (
                  <div className="flex gap-2">
                    <button
                      onClick={() => console.log('Editar:', user)}
                      className="px-2 py-1 text-xs bg-blue-600 text-white rounded hover:bg-blue-700"
                    >
                      Editar
                    </button>
                    <button
                      onClick={() => console.log('Excluir:', user)}
                      className="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700"
                    >
                      Excluir
                    </button>
                  </div>
                )}
              </Content>
            </Column>
          </Table>
        </div>
      )}

      {/* Modo Híbrido - Props + Children */}
      {currentMode === 'hybrid' && (
        <div>
          <h2 className="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-3">
            Modo Híbrido - Props + Children
          </h2>
          <p className="text-gray-600 dark:text-gray-400 mb-4">
            Combina configuração do backend com customizações específicas via JSX.
          </p>
          
          <PapaLeguasTable 
            data={sampleUsers} 
            permissions={permissions}
            columns={dynamicColumns} // Base do backend
            filters={filters}
            debug={true}
          >
            {/* Customizar apenas colunas específicas */}
            <PapaLeguasTable.Column key="name" label="Nome Customizado">
                              <PapaLeguasTable.Content>
                  {(user) => (
                    <div className="flex items-center gap-3">
                      <div className="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                        {user.name.split(' ').map((n: string) => n.charAt(0)).join('')}
                      </div>
                      <div>
                        <div className="font-medium text-gray-900 dark:text-gray-100">
                          {user.name}
                        </div>
                        <div className="text-sm text-gray-500 dark:text-gray-400">
                          {user.role === 'admin' ? '👑 Administrador' : '👤 Usuário'}
                        </div>
                      </div>
                    </div>
                  )}
                </PapaLeguasTable.Content>
              </PapaLeguasTable.Column>

            {/* Outras colunas (id, email, status, created_at) */}
            {/* serão renderizadas automaticamente via dynamicColumns */}
          </PapaLeguasTable>
        </div>
      )}
    </div>
  )
}

export default TableExample 
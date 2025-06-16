import React from 'react'
import { PapaLeguasTable } from '../index'
import { Table, Column, Content, Rows } from '../children'

/**
 * Exemplo de uso do DeclarativeTable com sintaxe JSX
 * 
 * Este exemplo demonstra como usar a sintaxe declarativa
 * para criar tabelas com máximo controle sobre renderização
 */
export const DeclarativeExample: React.FC = () => {
  // Dados de exemplo
  const users = [
    {
      id: 1,
      name: 'João Silva',
      email: 'joao@example.com',
      role: 'admin',
      status: 'active',
      created_at: '2024-01-15',
      salary: 5000.00,
      avatar: 'https://via.placeholder.com/40'
    },
    {
      id: 2,
      name: 'Maria Santos',
      email: 'maria@example.com',
      role: 'user',
      status: 'inactive',
      created_at: '2024-02-20',
      salary: 3500.00,
      avatar: 'https://via.placeholder.com/40'
    },
    {
      id: 3,
      name: 'Pedro Costa',
      email: 'pedro@example.com',
      role: 'moderator',
      status: 'active',
      created_at: '2024-03-10',
      salary: 4200.00,
      avatar: 'https://via.placeholder.com/40'
    }
  ]

  // Permissões de exemplo
  const permissions = {
    user_permissions: ['users.view', 'users.edit', 'users.delete'],
    user_roles: ['admin'],
    is_super_admin: false
  }

  // Handlers
  const handleRowClick = (user: any) => {
    console.log('Linha clicada:', user)
  }

  const handleEdit = (user: any) => {
    console.log('Editar usuário:', user)
  }

  const handleDelete = (user: any) => {
    console.log('Excluir usuário:', user)
  }

  const handleSelectionChange = (selectedUsers: any[]) => {
    console.log('Seleção alterada:', selectedUsers)
  }

  return (
    <div className="space-y-8">
      <div>
        <h2 className="text-2xl font-bold mb-4">Exemplo Declarativo - Sintaxe JSX</h2>
        <p className="text-gray-600 dark:text-gray-400 mb-6">
          Tabela configurada completamente via children JSX com máximo controle sobre renderização.
        </p>
      </div>

      {/* Exemplo 1: Sintaxe Declarativa Básica */}
      <div>
        <h3 className="text-lg font-semibold mb-3">1. Sintaxe Declarativa Básica</h3>
        
        <Table 
          data={users} 
          permissions={permissions}
          onRowClick={handleRowClick}
          onSelectionChange={handleSelectionChange}
          config={{ 
            selectable: true,
            sortable: true,
            filterable: true 
          }}
        >
          <Column key="name" label="Nome" sortable filterable>
            <Content>
              {(user) => (
                <div className="flex items-center gap-3">
                  <img 
                    src={user.avatar} 
                    alt={user.name}
                    className="w-8 h-8 rounded-full"
                  />
                  <div>
                    <div className="font-medium text-gray-900 dark:text-gray-100">
                      {user.name}
                    </div>
                    <div className="text-sm text-gray-500 dark:text-gray-400">
                      ID: {user.id}
                    </div>
                  </div>
                </div>
              )}
            </Content>
          </Column>

          <Column key="email" label="Email" type="email" filterable>
            <Content>
              {(user) => (
                <a 
                  href={`mailto:${user.email}`}
                  className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                >
                  {user.email}
                </a>
              )}
            </Content>
          </Column>

          <Column key="role" label="Função" filterable>
            <Content>
              {(user) => {
                const roleColors = {
                  admin: 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                  moderator: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
                  user: 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                }
                
                return (
                  <span className={`px-2 py-1 text-xs rounded-full ${roleColors[user.role as keyof typeof roleColors]}`}>
                    {user.role}
                  </span>
                )
              }}
            </Content>
          </Column>

          <Column key="status" label="Status" sortable>
            <Content>
              {(user) => (
                <span className={`px-2 py-1 text-xs rounded-full ${
                  user.status === 'active' 
                    ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                    : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
                }`}>
                  {user.status === 'active' ? 'Ativo' : 'Inativo'}
                </span>
              )}
            </Content>
          </Column>

          <Column key="salary" label="Salário" type="money" sortable>
            <Content>
              {(user) => (
                <span className="font-medium text-green-600 dark:text-green-400">
                  {new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                  }).format(user.salary)}
                </span>
              )}
            </Content>
          </Column>

          <Column key="actions" label="Ações" width="120px">
            <Content>
              {(user) => (
                <div className="flex gap-2">
                  <button
                    onClick={(e) => {
                      e.stopPropagation()
                      handleEdit(user)
                    }}
                    className="p-1 text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                    title="Editar"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                  </button>
                  
                  <button
                    onClick={(e) => {
                      e.stopPropagation()
                      handleDelete(user)
                    }}
                    className="p-1 text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
                    title="Excluir"
                  >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </button>
                </div>
              )}
            </Content>
          </Column>
        </Table>
      </div>

      {/* Exemplo 2: Com Rows Customizadas */}
      <div>
        <h3 className="text-lg font-semibold mb-3">2. Com Renderização de Linhas Customizada</h3>
        
        <Table 
          data={users} 
          permissions={permissions}
          config={{ selectable: false }}
        >
          <Column key="name" label="Nome">
            <Content>
              {(user) => user.name}
            </Content>
          </Column>

          <Column key="email" label="Email">
            <Content>
              {(user) => user.email}
            </Content>
          </Column>

          <Column key="status" label="Status">
            <Content>
              {(user) => user.status}
            </Content>
          </Column>

          {/* Customização completa das linhas */}
          <Rows>
            {(user, index) => (
              <tr 
                key={user.id}
                className={`
                  ${index % 2 === 0 ? 'bg-gray-50 dark:bg-gray-800/50' : 'bg-white dark:bg-gray-900'}
                  hover:bg-blue-50 dark:hover:bg-blue-900/20 
                  border-l-4 border-transparent hover:border-blue-500
                  transition-all duration-200
                `}
                onClick={() => handleRowClick(user)}
              >
                <td className="px-4 py-4">
                  <div className="flex items-center gap-3">
                    <div className={`w-3 h-3 rounded-full ${
                      user.status === 'active' ? 'bg-green-500' : 'bg-gray-400'
                    }`}></div>
                    <span className="font-medium">{user.name}</span>
                  </div>
                </td>
                <td className="px-4 py-4 text-gray-600 dark:text-gray-400">
                  {user.email}
                </td>
                <td className="px-4 py-4">
                  <span className={`px-3 py-1 rounded-full text-sm font-medium ${
                    user.status === 'active' 
                      ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
                      : 'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400'
                  }`}>
                    {user.status === 'active' ? 'Ativo' : 'Inativo'}
                  </span>
                </td>
              </tr>
            )}
          </Rows>
        </Table>
      </div>

      {/* Exemplo 3: Modo Debug */}
      <div>
        <h3 className="text-lg font-semibold mb-3">3. Modo Debug (Verifique o Console)</h3>
        
        <Table 
          data={users} 
          permissions={permissions}
          debug={true}
          config={{ selectable: true }}
        >
          <Column key="name" label="Nome" sortable>
            <Content>
              {(user) => user.name}
            </Content>
          </Column>

          <Column key="email" label="Email" filterable>
            <Content>
              {(user) => user.email}
            </Content>
          </Column>
        </Table>
      </div>
    </div>
  )
}

export default DeclarativeExample 
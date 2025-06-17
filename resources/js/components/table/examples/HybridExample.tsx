import React from 'react'
import { PapaLeguasTable } from '../index'
import { Column, Content, Rows } from '../children'

/**
 * Exemplo de uso do HybridTable com sintaxe mista
 * 
 * Este exemplo demonstra como usar props e children simultaneamente,
 * com resolução automática de conflitos pelo ColumnMerger
 */
export const HybridExample: React.FC = () => {
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
      avatar: 'https://via.placeholder.com/40',
      department: 'TI'
    },
    {
      id: 2,
      name: 'Maria Santos',
      email: 'maria@example.com',
      role: 'user',
      status: 'inactive',
      created_at: '2024-02-20',
      salary: 3500.00,
      avatar: 'https://via.placeholder.com/40',
      department: 'RH'
    },
    {
      id: 3,
      name: 'Pedro Costa',
      email: 'pedro@example.com',
      role: 'moderator',
      status: 'active',
      created_at: '2024-03-10',
      salary: 4200.00,
      avatar: 'https://via.placeholder.com/40',
      department: 'Vendas'
    }
  ]

  // Configuração de colunas via props (vinda do backend)
  const propsColumns = [
    {
      key: 'name',
      label: 'Nome do Usuário', // Será sobrescrito pelo children
      sortable: true,
      filterable: true,
      type: 'text' as const
    },
    {
      key: 'email',
      label: 'E-mail',
      sortable: true,
      filterable: true,
      type: 'email' as const
    },
    {
      key: 'department',
      label: 'Departamento',
      sortable: true,
      filterable: true,
      type: 'text' as const
    },
    {
      key: 'salary',
      label: 'Salário',
      sortable: true,
      type: 'money' as const,
      format: { currency: 'BRL' }
    },
    {
      key: 'status',
      label: 'Status',
      sortable: true,
      type: 'status' as const,
      format: {
        statusMap: {
          active: { label: 'Ativo', className: 'bg-green-100 text-green-800' },
          inactive: { label: 'Inativo', className: 'bg-gray-100 text-gray-800' }
        }
      }
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
        <h2 className="text-2xl font-bold mb-4">Exemplo Híbrido - Props + Children</h2>
        <p className="text-gray-600 dark:text-gray-400 mb-6">
          Demonstração de como combinar configurações do backend (props) com customizações frontend (children).
          O ColumnMerger resolve conflitos automaticamente dando prioridade aos children.
        </p>
      </div>

      {/* Exemplo 1: Híbrido Básico com Conflitos */}
      <div>
        <h3 className="text-lg font-semibold mb-3">1. Híbrido Básico - Conflitos Resolvidos Automaticamente</h3>
        <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
          Props definem colunas básicas, children customizam algumas específicas. 
          Note que o label "Nome" será sobrescrito por "Nome Completo".
        </p>
        
        <PapaLeguasTable 
          data={users} 
          columns={propsColumns} // Props do backend
          permissions={permissions}
          onRowClick={handleRowClick}
          onSelectionChange={handleSelectionChange}
          config={{ 
            selectable: true,
            mergeStrategy: 'children-priority',
            allowConflicts: true
          }}
          debug={true} // Ativar debug para ver conflitos
        >
          {/* Children customizam colunas específicas */}
          <Column key="name" label="Nome Completo" sortable filterable>
            <Content>
              {(user) => (
                <div className="flex items-center gap-3">
                  <img 
                    src={user.avatar} 
                    alt={user.name}
                    className="w-10 h-10 rounded-full border-2 border-gray-200"
                  />
                  <div>
                    <div className="font-semibold text-gray-900 dark:text-gray-100">
                      {user.name}
                    </div>
                    <div className="text-sm text-gray-500 dark:text-gray-400">
                      {user.role} • ID: {user.id}
                    </div>
                  </div>
                </div>
              )}
            </Content>
          </Column>

          {/* Coluna totalmente nova (não existe em props) */}
          <Column key="actions" label="Ações" width="150px">
            <Content>
              {(user) => (
                <div className="flex gap-1">
                  <button
                    onClick={(e) => {
                      e.stopPropagation()
                      handleEdit(user)
                    }}
                    className="px-2 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200 dark:bg-blue-900/20 dark:text-blue-400"
                    title="Editar"
                  >
                    Editar
                  </button>
                  
                  <button
                    onClick={(e) => {
                      e.stopPropagation()
                      handleDelete(user)
                    }}
                    className="px-2 py-1 text-xs bg-red-100 text-red-700 rounded hover:bg-red-200 dark:bg-red-900/20 dark:text-red-400"
                    title="Excluir"
                  >
                    Excluir
                  </button>
                </div>
              )}
            </Content>
          </Column>
        </PapaLeguasTable>
      </div>

      {/* Exemplo 2: Estratégia Props Priority */}
      <div>
        <h3 className="text-lg font-semibold mb-3">2. Estratégia Props Priority</h3>
        <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
          Neste exemplo, as props têm prioridade sobre children em caso de conflito.
        </p>
        
        <PapaLeguasTable 
          data={users} 
          columns={propsColumns}
          permissions={permissions}
          config={{ 
            selectable: false,
            mergeStrategy: 'props-priority', // Props têm prioridade
            allowConflicts: true
          }}
          debug={true}
        >
          {/* Tentativa de sobrescrever label (será ignorada) */}
          <Column key="name" label="Este Label Será Ignorado" sortable={false}>
            <Content>
              {(user) => (
                <span className="font-medium text-purple-600 dark:text-purple-400">
                  {user.name} (Customizado)
                </span>
              )}
            </Content>
          </Column>

          {/* Coluna nova ainda funciona */}
          <Column key="custom" label="Campo Customizado">
            <Content>
              {(user) => (
                <span className="text-sm bg-yellow-100 text-yellow-800 px-2 py-1 rounded dark:bg-yellow-900/20 dark:text-yellow-400">
                  {user.department} - {user.role}
                </span>
              )}
            </Content>
          </Column>
        </PapaLeguasTable>
      </div>

      {/* Exemplo 3: Merge Permissivo */}
      <div>
        <h3 className="text-lg font-semibold mb-3">3. Merge Permissivo - Melhor dos Dois Mundos</h3>
        <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
          Estratégia inteligente: children para UI, props para dados e permissões.
        </p>
        
        <PapaLeguasTable 
          data={users} 
          columns={propsColumns}
          permissions={permissions}
          config={{ 
            selectable: true,
            mergeStrategy: 'permissive-merge', // Merge inteligente
            allowConflicts: true
          }}
          debug={true}
        >
          {/* UI customizada mantendo dados das props */}
          <Column key="email" label="Contato" filterable>
            <Content>
              {(user) => (
                <div className="space-y-1">
                  <a 
                    href={`mailto:${user.email}`}
                    className="text-blue-600 hover:text-blue-800 dark:text-blue-400 block"
                  >
                    {user.email}
                  </a>
                  <span className="text-xs text-gray-500 dark:text-gray-400">
                    Criado em: {new Date(user.created_at).toLocaleDateString('pt-BR')}
                  </span>
                </div>
              )}
            </Content>
          </Column>

          {/* Customização completa das linhas */}
          <Rows>
            {(user, index) => (
              <tr 
                key={user.id}
                className={`
                  ${index % 2 === 0 ? 'bg-gradient-to-r from-blue-50 to-purple-50 dark:from-blue-900/10 dark:to-purple-900/10' : 'bg-white dark:bg-gray-900'}
                  hover:bg-gradient-to-r hover:from-blue-100 hover:to-purple-100 dark:hover:from-blue-900/20 dark:hover:to-purple-900/20
                  border-l-4 border-transparent hover:border-gradient-to-b hover:from-blue-500 hover:to-purple-500
                  transition-all duration-300
                `}
                onClick={() => handleRowClick(user)}
              >
                {/* Checkbox de seleção */}
                <td className="px-4 py-4">
                  <input
                    type="checkbox"
                    className="rounded border-gray-300 dark:border-gray-600"
                    onChange={(e) => {
                      e.stopPropagation()
                      // Lógica de seleção seria implementada aqui
                    }}
                  />
                </td>
                
                {/* Renderizar células automaticamente baseado nas colunas mescladas */}
                {/* Nota: Em uma implementação real, isso seria feito automaticamente pelo HybridTable */}
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
                <td className="px-4 py-4">{user.department}</td>
                <td className="px-4 py-4 font-medium text-green-600">
                  {new Intl.NumberFormat('pt-BR', {
                    style: 'currency',
                    currency: 'BRL'
                  }).format(user.salary)}
                </td>
                <td className="px-4 py-4">
                  <span className={`px-3 py-1 rounded-full text-sm ${
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
        </PapaLeguasTable>
      </div>

      {/* Exemplo 4: Modo Strict (Sem Conflitos) */}
      <div>
        <h3 className="text-lg font-semibold mb-3">4. Modo Strict - Detecta Conflitos</h3>
        <p className="text-sm text-gray-600 dark:text-gray-400 mb-3">
          Este modo detecta conflitos e pode gerar avisos ou erros.
        </p>
        
        <PapaLeguasTable 
          data={users} 
          columns={propsColumns}
          permissions={permissions}
          config={{ 
            selectable: false,
            mergeStrategy: 'strict-merge',
            allowConflicts: false // Não permite conflitos
          }}
          debug={true}
        >
          {/* Esta coluna causará conflito e será reportada */}
          <Column key="name" label="Nome Conflitante" sortable={false}>
            <Content>
              {(user) => user.name}
            </Content>
          </Column>
        </PapaLeguasTable>
      </div>
    </div>
  )
}

export default HybridExample 
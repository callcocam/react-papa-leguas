import React from 'react'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { usePage } from '@inertiajs/react'
import { PapaLeguasTable } from '../index'
import { usePermissions } from '../hooks/usePermissions'

// Mocks
jest.mock('@inertiajs/react', () => ({
  usePage: jest.fn(),
  router: {
    get: jest.fn(),
    post: jest.fn(),
    put: jest.fn(),
    patch: jest.fn(),
    delete: jest.fn(),
  },
  Link: jest.fn(({ children, ...props }) => (
    <a {...props} data-testid="inertia-link">
      {children}
    </a>
  ))
}))

const mockUsePage = usePage as jest.MockedFunction<typeof usePage>

describe('Sistema de Tabelas Papa Leguas - Testes de Integração', () => {
  // Dados de teste
  const mockUsers = [
    {
      id: 1,
      name: 'João Silva',
      email: 'joao@example.com',
      status: 'active',
      role: 'editor',
      created_at: '2024-01-15T10:30:00Z'
    },
    {
      id: 2,
      name: 'Maria Santos',
      email: 'maria@example.com',
      status: 'inactive',
      role: 'viewer',
      created_at: '2024-01-16T14:20:00Z'
    },
    {
      id: 3,
      name: 'Pedro Costa',
      email: 'pedro@example.com',
      status: 'active',
      role: 'admin',
      created_at: '2024-01-17T09:15:00Z'
    }
  ]

  const mockPermissionsData = {
    user_permissions: ['users.view', 'users.edit', 'users.create'],
    user_roles: ['admin'],
    is_super_admin: false
  }

  const mockColumnsConfig = [
    {
      key: 'name',
      label: 'Nome',
      sortable: true,
      filterable: true
    },
    {
      key: 'email',
      label: 'Email',
      type: 'email',
      filterable: true
    },
    {
      key: 'status',
      label: 'Status',
      type: 'status',
      filterable: true
    },
    {
      key: 'actions',
      label: 'Ações',
      type: 'actions',
      actions: [
        {
          key: 'edit',
          label: 'Editar',
          permission: 'users.edit',
          route: 'users.edit'
        },
        {
          key: 'delete',
          label: 'Excluir',
          permission: 'users.delete',
          variant: 'destructive',
          requireConfirmation: true
        }
      ]
    }
  ]

  beforeEach(() => {
    jest.clearAllMocks()
    
    mockUsePage.mockReturnValue({
      props: {
        auth: {
          user: {
            id: 1,
            name: 'Admin User',
            email: 'admin@example.com'
          }
        },
        permissions: mockPermissionsData
      }
    } as any)
  })

  describe('Modo Dinâmico (Props)', () => {
    it('deve renderizar tabela com dados e colunas via props', () => {
      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
        />
      )

      // Verificar cabeçalhos
      expect(screen.getByText('Nome')).toBeInTheDocument()
      expect(screen.getByText('Email')).toBeInTheDocument()
      expect(screen.getByText('Status')).toBeInTheDocument()
      expect(screen.getByText('Ações')).toBeInTheDocument()

      // Verificar dados
      expect(screen.getByText('João Silva')).toBeInTheDocument()
      expect(screen.getByText('joao@example.com')).toBeInTheDocument()
      expect(screen.getByText('Maria Santos')).toBeInTheDocument()
      expect(screen.getByText('Pedro Costa')).toBeInTheDocument()
    })

    it('deve aplicar filtros corretamente', async () => {
      const user = userEvent.setup()
      
      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
        />
      )

      // Procurar por input de filtro de nome
      const nameFilter = screen.getByPlaceholderText(/filtrar por nome/i)
      
      // Aplicar filtro
      await user.type(nameFilter, 'João')

      // Verificar se apenas João aparece
      expect(screen.getByText('João Silva')).toBeInTheDocument()
      expect(screen.queryByText('Maria Santos')).not.toBeInTheDocument()
      expect(screen.queryByText('Pedro Costa')).not.toBeInTheDocument()
    })

    it('deve ordenar colunas clicáveis', async () => {
      const user = userEvent.setup()
      
      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
        />
      )

      // Clicar no cabeçalho "Nome" para ordenar
      const nameHeader = screen.getByText('Nome')
      await user.click(nameHeader)

      // Verificar se a ordenação foi aplicada (primeiro item deve ser João)
      const rows = screen.getAllByRole('row')
      expect(rows[1]).toHaveTextContent('João Silva')
    })

    it('deve mostrar ações baseadas em permissões', () => {
      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
        />
      )

      // Verificar se botões de ação aparecem (usuário tem permissão users.edit)
      const editButtons = screen.getAllByText('Editar')
      expect(editButtons).toHaveLength(mockUsers.length)
    })
  })

  describe('Modo Declarativo (Children)', () => {
    it('deve renderizar tabela com children JSX', () => {
      render(
        <PapaLeguasTable data={mockUsers}>
          <PapaLeguasTable.Column key="name" label="Nome" sortable filterable>
            <PapaLeguasTable.Content>
              {(user) => (
                <div className="font-medium">
                  {user.name}
                </div>
              )}
            </PapaLeguasTable.Content>
          </PapaLeguasTable.Column>

          <PapaLeguasTable.Column key="email" label="Email" type="email" />

          <PapaLeguasTable.Column key="actions" label="Ações">
            <PapaLeguasTable.Content>
              {(user) => (
                <div className="flex gap-2">
                  <button className="btn-edit">Editar {user.name}</button>
                  <button className="btn-delete">Excluir</button>
                </div>
              )}
            </PapaLeguasTable.Content>
          </PapaLeguasTable.Column>
        </PapaLeguasTable>
      )

      // Verificar cabeçalhos
      expect(screen.getByText('Nome')).toBeInTheDocument()
      expect(screen.getByText('Email')).toBeInTheDocument()
      expect(screen.getByText('Ações')).toBeInTheDocument()

      // Verificar conteúdo customizado
      expect(screen.getByText('Editar João Silva')).toBeInTheDocument()
      expect(screen.getByText('Editar Maria Santos')).toBeInTheDocument()
      expect(screen.getByText('Editar Pedro Costa')).toBeInTheDocument()
    })

    it('deve aplicar filtros em colunas declarativas', async () => {
      const user = userEvent.setup()
      
      render(
        <PapaLeguasTable data={mockUsers}>
          <PapaLeguasTable.Column key="name" label="Nome" filterable>
            <PapaLeguasTable.Content>
              {(user) => user.name}
            </PapaLeguasTable.Content>
          </PapaLeguasTable.Column>

          <PapaLeguasTable.Column key="email" label="Email" />
        </PapaLeguasTable>
      )

      // Aplicar filtro
      const nameFilter = screen.getByPlaceholderText(/filtrar por nome/i)
      await user.type(nameFilter, 'Maria')

      // Verificar resultado
      expect(screen.getByText('Maria Santos')).toBeInTheDocument()
      expect(screen.queryByText('João Silva')).not.toBeInTheDocument()
    })
  })

  describe('Modo Híbrido (Props + Children)', () => {
    it('deve combinar props e children corretamente', () => {
      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={[
            { key: 'name', label: 'Nome (Backend)', sortable: true },
            { key: 'email', label: 'Email (Backend)', type: 'email' }
          ]}
        >
          {/* Children sobrescreve props para esta coluna */}
          <PapaLeguasTable.Column key="name" label="Nome (Frontend)">
            <PapaLeguasTable.Content>
              {(user) => (
                <div className="custom-name">
                  🧑‍💼 {user.name}
                </div>
              )}
            </PapaLeguasTable.Content>
          </PapaLeguasTable.Column>

          {/* Nova coluna apenas no frontend */}
          <PapaLeguasTable.Column key="actions" label="Ações Frontend">
            <PapaLeguasTable.Content>
              {(user) => (
                <button className="custom-action">
                  Ação para {user.name}
                </button>
              )}
            </PapaLeguasTable.Content>
          </PapaLeguasTable.Column>
        </PapaLeguasTable>
      )

      // Verificar que children sobrescreveu props
      expect(screen.getByText('Nome (Frontend)')).toBeInTheDocument()
      expect(screen.queryByText('Nome (Backend)')).not.toBeInTheDocument()

      // Verificar que email do backend permaneceu
      expect(screen.getByText('Email (Backend)')).toBeInTheDocument()

      // Verificar nova coluna do frontend
      expect(screen.getByText('Ações Frontend')).toBeInTheDocument()

      // Verificar conteúdo customizado
      expect(screen.getByText('🧑‍💼 João Silva')).toBeInTheDocument()
      expect(screen.getByText('Ação para João Silva')).toBeInTheDocument()
    })

    it('deve manter funcionalidades de ambos os modos', async () => {
      const user = userEvent.setup()
      
      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={[
            { key: 'name', label: 'Nome', sortable: true, filterable: true },
            { key: 'email', label: 'Email', type: 'email' }
          ]}
        >
          <PapaLeguasTable.Column key="status" label="Status Customizado" filterable>
            <PapaLeguasTable.Content>
              {(user) => (
                <span className={`status-${user.status}`}>
                  {user.status === 'active' ? '✅ Ativo' : '❌ Inativo'}
                </span>
              )}
            </PapaLeguasTable.Content>
          </PapaLeguasTable.Column>
        </PapaLeguasTable>
      )

      // Testar ordenação (do backend)
      const nameHeader = screen.getByText('Nome')
      await user.click(nameHeader)

      // Testar filtro (do frontend)
      const statusFilter = screen.getByPlaceholderText(/filtrar por status/i)
      await user.type(statusFilter, 'ativo')

      // Verificar conteúdo customizado
      expect(screen.getByText('✅ Ativo')).toBeInTheDocument()
    })
  })

  describe('Sistema de Permissões Integrado', () => {
    it('deve ocultar ações sem permissão', () => {
      // Simular usuário sem permissão de delete
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: { id: 1, name: 'Limited User' } },
          permissions: {
            user_permissions: ['users.view', 'users.edit'], // Sem users.delete
            user_roles: ['editor'],
            is_super_admin: false
          }
        }
      } as any)

      const columnsWithDelete = [
        ...mockColumnsConfig,
        {
          key: 'delete_action',
          label: 'Excluir',
          type: 'actions',
          actions: [
            {
              key: 'delete',
              label: 'Excluir',
              permission: 'users.delete',
              variant: 'destructive'
            }
          ]
        }
      ]

      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={columnsWithDelete}
        />
      )

      // Botões de editar devem aparecer (tem permissão)
      expect(screen.getAllByText('Editar')).toHaveLength(mockUsers.length)

      // Botões de excluir não devem aparecer (sem permissão)
      expect(screen.queryByText('Excluir')).not.toBeInTheDocument()
    })

    it('deve mostrar todas as ações para super admin', () => {
      // Simular super admin
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: { id: 1, name: 'Super Admin' } },
          permissions: {
            user_permissions: [],
            user_roles: ['super-admin'],
            is_super_admin: true
          }
        }
      } as any)

      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
        />
      )

      // Super admin deve ver todas as ações
      expect(screen.getAllByText('Editar')).toHaveLength(mockUsers.length)
      // Note: Delete buttons would appear if they were in the config
    })

    it('deve integrar permissões com children declarativos', () => {
      render(
        <PapaLeguasTable data={mockUsers}>
          <PapaLeguasTable.Column key="name" label="Nome">
            <PapaLeguasTable.Content>
              {(user) => user.name}
            </PapaLeguasTable.Content>
          </PapaLeguasTable.Column>

          <PapaLeguasTable.Column key="actions" label="Ações">
            <PapaLeguasTable.Content>
              {(user) => (
                <div className="flex gap-2">
                  <PapaLeguasTable.PermissionButton
                    permission="users.edit"
                    variant="outline"
                    size="sm"
                  >
                    Editar
                  </PapaLeguasTable.PermissionButton>
                  
                  <PapaLeguasTable.PermissionButton
                    permission="users.delete"
                    variant="destructive"
                    size="sm"
                  >
                    Excluir
                  </PapaLeguasTable.PermissionButton>
                </div>
              )}
            </PapaLeguasTable.Content>
          </PapaLeguasTable.Column>
        </PapaLeguasTable>
      )

      // Botão de editar deve aparecer (tem permissão)
      expect(screen.getAllByText('Editar')).toHaveLength(mockUsers.length)

      // Botão de excluir não deve aparecer (sem permissão users.delete)
      expect(screen.queryByText('Excluir')).not.toBeInTheDocument()
    })
  })

  describe('Estados da Tabela', () => {
    it('deve mostrar estado de loading', () => {
      render(
        <PapaLeguasTable
          data={[]}
          columns={mockColumnsConfig}
          loading={true}
        />
      )

      expect(screen.getByText(/carregando/i)).toBeInTheDocument()
    })

    it('deve mostrar estado vazio', () => {
      render(
        <PapaLeguasTable
          data={[]}
          columns={mockColumnsConfig}
        />
      )

      expect(screen.getByText(/nenhum registro encontrado/i)).toBeInTheDocument()
    })

    it('deve mostrar estado de erro', () => {
      render(
        <PapaLeguasTable
          data={[]}
          columns={mockColumnsConfig}
          error="Erro ao carregar dados"
        />
      )

      expect(screen.getByText('Erro ao carregar dados')).toBeInTheDocument()
    })
  })

  describe('Responsividade', () => {
    it('deve adaptar para mobile', () => {
      // Simular viewport mobile
      Object.defineProperty(window, 'innerWidth', {
        writable: true,
        configurable: true,
        value: 375,
      })

      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
        />
      )

      // Em mobile, deve mostrar cards ao invés de tabela
      expect(screen.getByTestId('mobile-cards')).toBeInTheDocument()
      expect(screen.queryByRole('table')).not.toBeInTheDocument()
    })

    it('deve mostrar tabela completa em desktop', () => {
      // Simular viewport desktop
      Object.defineProperty(window, 'innerWidth', {
        writable: true,
        configurable: true,
        value: 1024,
      })

      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
        />
      )

      // Em desktop, deve mostrar tabela completa
      expect(screen.getByRole('table')).toBeInTheDocument()
      expect(screen.queryByTestId('mobile-cards')).not.toBeInTheDocument()
    })
  })

  describe('Seleção e Ações em Massa', () => {
    it('deve permitir seleção múltipla', async () => {
      const user = userEvent.setup()
      
      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
          selectable={true}
        />
      )

      // Selecionar checkbox master
      const masterCheckbox = screen.getByRole('checkbox', { name: /selecionar todos/i })
      await user.click(masterCheckbox)

      // Todos os checkboxes individuais devem estar marcados
      const individualCheckboxes = screen.getAllByRole('checkbox')
      individualCheckboxes.slice(1).forEach(checkbox => {
        expect(checkbox).toBeChecked()
      })
    })

    it('deve executar ações em massa', async () => {
      const mockBulkAction = jest.fn()
      const user = userEvent.setup()
      
      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
          selectable={true}
          bulkActions={[
            {
              key: 'delete_selected',
              label: 'Excluir Selecionados',
              permission: 'users.delete',
              action: mockBulkAction
            }
          ]}
        />
      )

      // Selecionar alguns itens
      const checkboxes = screen.getAllByRole('checkbox')
      await user.click(checkboxes[1]) // Primeiro item
      await user.click(checkboxes[2]) // Segundo item

      // Executar ação em massa
      const bulkActionButton = screen.getByText('Excluir Selecionados')
      await user.click(bulkActionButton)

      expect(mockBulkAction).toHaveBeenCalledWith([mockUsers[0], mockUsers[1]])
    })
  })

  describe('Paginação', () => {
    it('deve mostrar controles de paginação', () => {
      const paginationData = {
        current_page: 1,
        last_page: 5,
        per_page: 10,
        total: 50,
        from: 1,
        to: 10
      }

      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
          pagination={paginationData}
        />
      )

      expect(screen.getByText('Página 1 de 5')).toBeInTheDocument()
      expect(screen.getByText('Mostrando 1 a 10 de 50 registros')).toBeInTheDocument()
    })

    it('deve navegar entre páginas', async () => {
      const user = userEvent.setup()
      const paginationData = {
        current_page: 1,
        last_page: 3,
        per_page: 10,
        total: 30,
        from: 1,
        to: 10
      }

      render(
        <PapaLeguasTable
          data={mockUsers}
          columns={mockColumnsConfig}
          pagination={paginationData}
        />
      )

      // Clicar em "Próxima página"
      const nextButton = screen.getByRole('button', { name: /próxima/i })
      await user.click(nextButton)

      // Verificar se navegação foi chamada (via Inertia.js)
      // Note: This would require mocking the router calls
    })
  })

  describe('Performance e Otimização', () => {
    it('deve renderizar grandes volumes de dados eficientemente', () => {
      const largeDataset = Array.from({ length: 1000 }, (_, i) => ({
        id: i + 1,
        name: `User ${i + 1}`,
        email: `user${i + 1}@example.com`,
        status: i % 2 === 0 ? 'active' : 'inactive'
      }))

      const startTime = performance.now()
      
      render(
        <PapaLeguasTable
          data={largeDataset}
          columns={mockColumnsConfig}
        />
      )

      const endTime = performance.now()
      const renderTime = endTime - startTime

      // Renderização deve ser rápida (menos de 100ms)
      expect(renderTime).toBeLessThan(100)
    })

    it('deve usar virtualização para grandes listas', () => {
      const largeDataset = Array.from({ length: 10000 }, (_, i) => ({
        id: i + 1,
        name: `User ${i + 1}`,
        email: `user${i + 1}@example.com`
      }))

      render(
        <PapaLeguasTable
          data={largeDataset}
          columns={mockColumnsConfig}
          virtualized={true}
        />
      )

      // Apenas uma parte dos itens deve estar no DOM
      const renderedRows = screen.getAllByRole('row')
      expect(renderedRows.length).toBeLessThan(100) // Muito menos que 10000
    })
  })
}) 
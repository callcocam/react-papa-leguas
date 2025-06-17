import React from 'react'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { Link } from '@inertiajs/react'
import {
  PermissionLink,
  PermissionNavLink,
  PermissionSidebarLink,
  PermissionBreadcrumbLink,
  usePermissionLink
} from '../PermissionLink'
import { usePermissions } from '../../hooks/usePermissions'

// Mocks
jest.mock('@inertiajs/react', () => ({
  Link: jest.fn(({ children, ...props }) => (
    <a {...props} data-testid="inertia-link">
      {children}
    </a>
  ))
}))

jest.mock('../../hooks/usePermissions', () => ({
  usePermissions: jest.fn()
}))

const mockUsePermissions = usePermissions as jest.MockedFunction<typeof usePermissions>
const mockLink = Link as jest.MockedFunction<typeof Link>

describe('PermissionLink', () => {
  const defaultPermissionsReturn = {
    hasPermission: jest.fn(),
    hasAnyPermission: jest.fn(),
    hasAllPermissions: jest.fn(),
    hasRole: jest.fn(),
    hasAnyRole: jest.fn(),
    hasAllRoles: jest.fn(),
    userPermissions: ['users.view', 'users.edit'],
    userRoles: ['editor'],
    isSuperAdmin: false,
    isAuthenticated: true,
    user: { id: 1, name: 'Test User' },
    can: jest.fn(),
    cannot: jest.fn(),
    is: jest.fn(),
    isNot: jest.fn(),
    permissionsCount: 2,
    rolesCount: 1,
    debugInfo: jest.fn()
  }

  beforeEach(() => {
    jest.clearAllMocks()
    mockUsePermissions.mockReturnValue(defaultPermissionsReturn)
  })

  describe('Renderização básica', () => {
    it('deve renderizar link quando tem permissão', () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
      
      render(
        <PermissionLink permission="users.view" href="/users">
          Ver Usuários
        </PermissionLink>
      )
      
      expect(screen.getByTestId('inertia-link')).toBeInTheDocument()
      expect(screen.getByText('Ver Usuários')).toBeInTheDocument()
      expect(defaultPermissionsReturn.hasPermission).toHaveBeenCalledWith('users.view')
    })

    it('deve aplicar props do Link corretamente', () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
      
      render(
        <PermissionLink
          permission="users.view"
          href="/users"
          className="custom-class"
          aria-label="Link para usuários"
          title="Clique para ver usuários"
        >
          Ver Usuários
        </PermissionLink>
      )
      
      const link = screen.getByTestId('inertia-link')
      expect(link).toHaveClass('custom-class')
      expect(link).toHaveAttribute('aria-label', 'Link para usuários')
      expect(link).toHaveAttribute('title', 'Clique para ver usuários')
    })

    it('deve usar route quando href não fornecido', () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
      
      render(
        <PermissionLink permission="users.view" route="/users">
          Ver Usuários
        </PermissionLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          href: '/users'
        }),
        {}
      )
    })

    it('deve usar # como fallback quando nem href nem route fornecidos', () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
      
      render(
        <PermissionLink permission="users.view">
          Ver Usuários
        </PermissionLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          href: '#'
        }),
        {}
      )
    })
  })

  describe('Comportamentos de fallback', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(false)
    })

    it('deve esconder link quando fallbackBehavior é "hide" (padrão)', () => {
      render(
        <PermissionLink permission="users.delete" href="/users/1/delete">
          Excluir
        </PermissionLink>
      )
      
      expect(screen.queryByTestId('inertia-link')).not.toBeInTheDocument()
    })

    it('deve esconder link quando fallbackBehavior é "hide" explícito', () => {
      render(
        <PermissionLink
          permission="users.delete"
          href="/users/1/delete"
          fallbackBehavior="hide"
        >
          Excluir
        </PermissionLink>
      )
      
      expect(screen.queryByTestId('inertia-link')).not.toBeInTheDocument()
    })

    it('deve desabilitar link quando fallbackBehavior é "disable"', () => {
      render(
        <PermissionLink
          permission="users.delete"
          href="/users/1/delete"
          fallbackBehavior="disable"
        >
          Excluir
        </PermissionLink>
      )
      
      const link = screen.getByTestId('inertia-link')
      expect(link).toBeInTheDocument()
      expect(link).toHaveClass('opacity-50', 'cursor-not-allowed', 'pointer-events-none')
    })

    it('deve mostrar link quando fallbackBehavior é "show"', () => {
      render(
        <PermissionLink
          permission="users.delete"
          href="/users/1/delete"
          fallbackBehavior="show"
        >
          Excluir
        </PermissionLink>
      )
      
      const link = screen.getByTestId('inertia-link')
      expect(link).toBeInTheDocument()
      expect(link).not.toHaveClass('opacity-50', 'cursor-not-allowed')
    })

    it('deve aplicar disabledClassName customizada', () => {
      render(
        <PermissionLink
          permission="users.delete"
          href="/users/1/delete"
          fallbackBehavior="disable"
          disabledClassName="custom-disabled-class"
        >
          Excluir
        </PermissionLink>
      )
      
      const link = screen.getByTestId('inertia-link')
      expect(link).toHaveClass('custom-disabled-class')
    })

    it('deve mostrar tooltip quando desabilitado e showTooltip é true', async () => {
      const user = userEvent.setup()
      
      render(
        <PermissionLink
          permission="users.delete"
          href="/users/1/delete"
          fallbackBehavior="disable"
          showTooltip={true}
          disabledReason="Você não tem permissão para excluir"
        >
          Excluir
        </PermissionLink>
      )
      
      const link = screen.getByTestId('inertia-link')
      await user.hover(link)
      
      await waitFor(() => {
        expect(screen.getByText('Você não tem permissão para excluir')).toBeInTheDocument()
      })
    })
  })

  describe('Validação de permissões', () => {
    it('deve validar permissão única', () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
      
      render(
        <PermissionLink permission="users.edit" href="/users/1/edit">
          Editar
        </PermissionLink>
      )
      
      expect(defaultPermissionsReturn.hasPermission).toHaveBeenCalledWith('users.edit')
    })

    it('deve validar array de permissões', () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
      
      render(
        <PermissionLink permission={['users.edit', 'users.delete']} href="/users/1">
          Gerenciar
        </PermissionLink>
      )
      
      expect(defaultPermissionsReturn.hasPermission).toHaveBeenCalledWith(['users.edit', 'users.delete'])
    })
  })

  describe('Eventos onClick', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve executar onClick quando clicado', async () => {
      const mockOnClick = jest.fn()
      const user = userEvent.setup()
      
      render(
        <PermissionLink
          permission="users.view"
          href="/users"
          onClick={mockOnClick}
        >
          Ver Usuários
        </PermissionLink>
      )
      
      await user.click(screen.getByTestId('inertia-link'))
      
      expect(mockOnClick).toHaveBeenCalledTimes(1)
    })

    it('deve prevenir clique quando desabilitado', async () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(false)
      const mockOnClick = jest.fn()
      const user = userEvent.setup()
      
      render(
        <PermissionLink
          permission="users.delete"
          href="/users/1/delete"
          fallbackBehavior="disable"
          onClick={mockOnClick}
        >
          Excluir
        </PermissionLink>
      )
      
      const link = screen.getByTestId('inertia-link')
      
      // Simular clique
      fireEvent.click(link)
      
      expect(mockOnClick).not.toHaveBeenCalled()
    })
  })

  describe('Propriedades do Inertia.js', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve passar propriedades do Inertia.js corretamente', () => {
      render(
        <PermissionLink
          permission="users.view"
          href="/users"
          method="post"
          data={{ filter: 'active' }}
          preserveScroll={true}
          preserveState={true}
          replace={true}
          only={['users']}
          except={['meta']}
        >
          Ver Usuários
        </PermissionLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          href: '/users',
          method: 'post',
          data: { filter: 'active' },
          preserveScroll: true,
          preserveState: true,
          replace: true,
          only: ['users'],
          except: ['meta']
        }),
        {}
      )
    })

    it('deve usar valores padrão para propriedades do Inertia.js', () => {
      render(
        <PermissionLink permission="users.view" href="/users">
          Ver Usuários
        </PermissionLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          method: 'get',
          preserveScroll: false,
          preserveState: false,
          replace: false
        }),
        {}
      )
    })
  })

  describe('Classes CSS condicionais', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve aplicar activeClassName quando ativo', () => {
      render(
        <PermissionLink
          permission="users.view"
          href="/users"
          activeClassName="active-class"
          className="base-class"
        >
          Ver Usuários
        </PermissionLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          className: expect.stringContaining('base-class active-class')
        }),
        {}
      )
    })

    it('deve aplicar inactiveClassName quando inativo', () => {
      render(
        <PermissionLink
          permission="users.view"
          href="/users"
          inactiveClassName="inactive-class"
          className="base-class"
        >
          Ver Usuários
        </PermissionLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          className: expect.stringContaining('base-class inactive-class')
        }),
        {}
      )
    })
  })

  describe('Componentes especializados', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('PermissionNavLink deve aplicar classes de navegação', () => {
      render(
        <PermissionNavLink permission="dashboard.view" href="/dashboard">
          Dashboard
        </PermissionNavLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          className: expect.stringContaining('px-4 py-2 transition-colors'),
          activeClassName: 'text-primary font-medium border-b-2 border-primary',
          inactiveClassName: 'text-muted-foreground hover:text-foreground'
        }),
        {}
      )
    })

    it('PermissionSidebarLink deve aplicar classes de sidebar', () => {
      render(
        <PermissionSidebarLink permission="users.view" href="/users">
          <span>👥</span>
          Usuários
        </PermissionSidebarLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          className: expect.stringContaining('flex items-center gap-3 rounded-lg px-3 py-2'),
          activeClassName: 'bg-muted text-primary'
        }),
        {}
      )
    })

    it('PermissionBreadcrumbLink deve ter configurações de breadcrumb', () => {
      render(
        <PermissionBreadcrumbLink permission="users.view" href="/users">
          Usuários
        </PermissionBreadcrumbLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          className: expect.stringContaining('text-muted-foreground hover:text-foreground'),
          fallbackBehavior: 'show',
          showTooltip: false
        }),
        {}
      )
    })
  })

  describe('usePermissionLink hook', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve fornecer links pré-configurados', () => {
      const TestComponent = () => {
        const { EditLink, ViewLink, CreateLink, DeleteLink, NavLink, SidebarLink } = usePermissionLink()
        
        return (
          <div>
            <EditLink href="/users/1/edit">Editar</EditLink>
            <ViewLink href="/users/1">Ver</ViewLink>
            <CreateLink href="/users/create">Criar</CreateLink>
            <DeleteLink href="/users/1/delete">Excluir</DeleteLink>
            <NavLink permission="dashboard.view" href="/dashboard">Dashboard</NavLink>
            <SidebarLink permission="users.view" href="/users">Usuários</SidebarLink>
          </div>
        )
      }
      
      render(<TestComponent />)
      
      expect(screen.getByText('Editar')).toBeInTheDocument()
      expect(screen.getByText('Ver')).toBeInTheDocument()
      expect(screen.getByText('Criar')).toBeInTheDocument()
      expect(screen.getByText('Excluir')).toBeInTheDocument()
      expect(screen.getByText('Dashboard')).toBeInTheDocument()
      expect(screen.getByText('Usuários')).toBeInTheDocument()
    })

    it('EditLink deve ter classes de edição', () => {
      const TestComponent = () => {
        const { EditLink } = usePermissionLink()
        return <EditLink href="/users/1/edit">Editar</EditLink>
      }
      
      render(<TestComponent />)
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          permission: 'edit',
          className: 'text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300'
        }),
        {}
      )
    })

    it('ViewLink deve ter classes de visualização', () => {
      const TestComponent = () => {
        const { ViewLink } = usePermissionLink()
        return <ViewLink href="/users/1">Ver</ViewLink>
      }
      
      render(<TestComponent />)
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          permission: 'view',
          className: 'text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300'
        }),
        {}
      )
    })

    it('CreateLink deve ter classes de criação', () => {
      const TestComponent = () => {
        const { CreateLink } = usePermissionLink()
        return <CreateLink href="/users/create">Criar</CreateLink>
      }
      
      render(<TestComponent />)
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          permission: 'create',
          className: 'text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300'
        }),
        {}
      )
    })

    it('DeleteLink deve ter classes de exclusão', () => {
      const TestComponent = () => {
        const { DeleteLink } = usePermissionLink()
        return <DeleteLink href="/users/1/delete">Excluir</DeleteLink>
      }
      
      render(<TestComponent />)
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          permission: 'delete',
          className: 'text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300'
        }),
        {}
      )
    })
  })

  describe('Acessibilidade', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve aplicar propriedades de acessibilidade', () => {
      render(
        <PermissionLink
          permission="users.view"
          href="/users"
          aria-label="Ver lista de usuários"
          title="Clique para ver usuários"
          target="_blank"
          rel="noopener noreferrer"
        >
          Ver Usuários
        </PermissionLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          'aria-label': 'Ver lista de usuários',
          title: 'Clique para ver usuários',
          target: '_blank',
          rel: 'noopener noreferrer'
        }),
        {}
      )
    })
  })

  describe('Callbacks de sucesso e erro', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve passar callbacks para o Link', () => {
      const mockOnSuccess = jest.fn()
      const mockOnError = jest.fn()
      
      render(
        <PermissionLink
          permission="users.view"
          href="/users"
          onSuccess={mockOnSuccess}
          onError={mockOnError}
        >
          Ver Usuários
        </PermissionLink>
      )
      
      expect(mockLink).toHaveBeenCalledWith(
        expect.objectContaining({
          onSuccess: mockOnSuccess,
          onError: mockOnError
        }),
        {}
      )
    })
  })
}) 
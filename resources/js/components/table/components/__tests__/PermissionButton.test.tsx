import React from 'react'
import { render, screen, fireEvent, waitFor } from '@testing-library/react'
import userEvent from '@testing-library/user-event'
import { router } from '@inertiajs/react'
import { PermissionButton, usePermissionButton } from '../PermissionButton'
import { usePermissions } from '../../hooks/usePermissions'

// Mocks
jest.mock('@inertiajs/react', () => ({
  router: {
    get: jest.fn(),
    post: jest.fn(),
    put: jest.fn(),
    patch: jest.fn(),
    delete: jest.fn(),
  }
}))

jest.mock('../../hooks/usePermissions', () => ({
  usePermissions: jest.fn()
}))

const mockUsePermissions = usePermissions as jest.MockedFunction<typeof usePermissions>
const mockRouter = router as jest.Mocked<typeof router>

describe('PermissionButton', () => {
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
    it('deve renderizar botão quando tem permissão', () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
      
      render(
        <PermissionButton permission="users.edit">
          Editar
        </PermissionButton>
      )
      
      expect(screen.getByRole('button', { name: 'Editar' })).toBeInTheDocument()
      expect(defaultPermissionsReturn.hasPermission).toHaveBeenCalledWith('users.edit')
    })

    it('deve aplicar props do Button corretamente', () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
      
      render(
        <PermissionButton
          permission="users.edit"
          variant="destructive"
          size="sm"
          className="custom-class"
          aria-label="Botão de edição"
        >
          Editar
        </PermissionButton>
      )
      
      const button = screen.getByRole('button', { name: 'Botão de edição' })
      expect(button).toHaveClass('custom-class')
    })
  })

  describe('Comportamentos de fallback', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(false)
    })

    it('deve esconder botão quando fallbackBehavior é "hide" (padrão)', () => {
      render(
        <PermissionButton permission="users.delete">
          Excluir
        </PermissionButton>
      )
      
      expect(screen.queryByRole('button')).not.toBeInTheDocument()
    })

    it('deve esconder botão quando fallbackBehavior é "hide" explícito', () => {
      render(
        <PermissionButton permission="users.delete" fallbackBehavior="hide">
          Excluir
        </PermissionButton>
      )
      
      expect(screen.queryByRole('button')).not.toBeInTheDocument()
    })

    it('deve desabilitar botão quando fallbackBehavior é "disable"', () => {
      render(
        <PermissionButton permission="users.delete" fallbackBehavior="disable">
          Excluir
        </PermissionButton>
      )
      
      const button = screen.getByRole('button', { name: 'Excluir' })
      expect(button).toBeDisabled()
      expect(button).toHaveClass('opacity-50', 'cursor-not-allowed')
    })

    it('deve mostrar botão quando fallbackBehavior é "show"', () => {
      render(
        <PermissionButton permission="users.delete" fallbackBehavior="show">
          Excluir
        </PermissionButton>
      )
      
      const button = screen.getByRole('button', { name: 'Excluir' })
      expect(button).toBeInTheDocument()
      expect(button).not.toBeDisabled()
    })

    it('deve mostrar tooltip quando desabilitado e showTooltip é true', async () => {
      const user = userEvent.setup()
      
      render(
        <PermissionButton
          permission="users.delete"
          fallbackBehavior="disable"
          showTooltip={true}
          disabledReason="Você não tem permissão para excluir"
        >
          Excluir
        </PermissionButton>
      )
      
      const button = screen.getByRole('button', { name: 'Excluir' })
      await user.hover(button)
      
      await waitFor(() => {
        expect(screen.getByText('Você não tem permissão para excluir')).toBeInTheDocument()
      })
    })
  })

  describe('Validação de permissões', () => {
    it('deve validar permissão única', () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
      
      render(
        <PermissionButton permission="users.edit">
          Editar
        </PermissionButton>
      )
      
      expect(defaultPermissionsReturn.hasPermission).toHaveBeenCalledWith('users.edit')
    })

    it('deve validar array de permissões', () => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
      
      render(
        <PermissionButton permission={['users.edit', 'users.delete']}>
          Editar ou Excluir
        </PermissionButton>
      )
      
      expect(defaultPermissionsReturn.hasPermission).toHaveBeenCalledWith(['users.edit', 'users.delete'])
    })
  })

  describe('Ações onClick', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve executar onClick quando clicado', async () => {
      const mockOnClick = jest.fn()
      const user = userEvent.setup()
      
      render(
        <PermissionButton permission="users.edit" onClick={mockOnClick}>
          Editar
        </PermissionButton>
      )
      
      await user.click(screen.getByRole('button', { name: 'Editar' }))
      
      expect(mockOnClick).toHaveBeenCalledTimes(1)
    })

    it('deve executar onClick assíncrono', async () => {
      const mockOnClick = jest.fn().mockResolvedValue(undefined)
      const user = userEvent.setup()
      
      render(
        <PermissionButton permission="users.edit" onClick={mockOnClick}>
          Editar
        </PermissionButton>
      )
      
      await user.click(screen.getByRole('button', { name: 'Editar' }))
      
      expect(mockOnClick).toHaveBeenCalledTimes(1)
    })

    it('não deve executar onClick quando desabilitado', async () => {
      const mockOnClick = jest.fn()
      const user = userEvent.setup()
      
      render(
        <PermissionButton
          permission="users.edit"
          onClick={mockOnClick}
          disabled={true}
        >
          Editar
        </PermissionButton>
      )
      
      await user.click(screen.getByRole('button', { name: 'Editar' }))
      
      expect(mockOnClick).not.toHaveBeenCalled()
    })
  })

  describe('Navegação Inertia.js', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve navegar com router.get para método GET', async () => {
      const user = userEvent.setup()
      
      render(
        <PermissionButton
          permission="users.view"
          route="/users/1"
          method="get"
        >
          Ver
        </PermissionButton>
      )
      
      await user.click(screen.getByRole('button', { name: 'Ver' }))
      
      expect(mockRouter.get).toHaveBeenCalledWith('/users/1', {}, {
        preserveScroll: false,
        preserveState: false,
        replace: false,
        only: undefined,
        except: undefined,
      })
    })

    it('deve navegar com router.post para método POST', async () => {
      const user = userEvent.setup()
      
      render(
        <PermissionButton
          permission="users.create"
          route="/users"
          method="post"
          data={{ name: 'Test User' }}
        >
          Criar
        </PermissionButton>
      )
      
      await user.click(screen.getByRole('button', { name: 'Criar' }))
      
      expect(mockRouter.post).toHaveBeenCalledWith('/users', { name: 'Test User' }, {
        preserveScroll: false,
        preserveState: false,
        replace: false,
        only: undefined,
        except: undefined,
      })
    })

    it('deve navegar com router.delete para método DELETE', async () => {
      const user = userEvent.setup()
      
      render(
        <PermissionButton
          permission="users.delete"
          route="/users/1"
          method="delete"
        >
          Excluir
        </PermissionButton>
      )
      
      await user.click(screen.getByRole('button', { name: 'Excluir' }))
      
      expect(mockRouter.delete).toHaveBeenCalledWith('/users/1', {}, {
        preserveScroll: false,
        preserveState: false,
        replace: false,
        only: undefined,
        except: undefined,
      })
    })

    it('deve usar opções do Inertia.js', async () => {
      const user = userEvent.setup()
      
      render(
        <PermissionButton
          permission="users.edit"
          route="/users/1"
          method="get"
          preserveScroll={true}
          preserveState={true}
          replace={true}
          only={['users']}
          except={['meta']}
        >
          Editar
        </PermissionButton>
      )
      
      await user.click(screen.getByRole('button', { name: 'Editar' }))
      
      expect(mockRouter.get).toHaveBeenCalledWith('/users/1', {}, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        only: ['users'],
        except: ['meta'],
      })
    })
  })

  describe('Sistema de confirmação', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve mostrar dialog de confirmação quando requireConfirmation é true', async () => {
      const user = userEvent.setup()
      
      render(
        <PermissionButton
          permission="users.delete"
          requireConfirmation={true}
          confirmTitle="Confirmar Exclusão"
          confirmDescription="Tem certeza?"
        >
          Excluir
        </PermissionButton>
      )
      
      await user.click(screen.getByRole('button', { name: 'Excluir' }))
      
      expect(screen.getByText('Confirmar Exclusão')).toBeInTheDocument()
      expect(screen.getByText('Tem certeza?')).toBeInTheDocument()
    })

    it('deve executar ação após confirmação', async () => {
      const mockOnClick = jest.fn()
      const user = userEvent.setup()
      
      render(
        <PermissionButton
          permission="users.delete"
          onClick={mockOnClick}
          requireConfirmation={true}
          confirmButtonText="Sim, excluir"
        >
          Excluir
        </PermissionButton>
      )
      
      // Clicar no botão principal
      await user.click(screen.getByRole('button', { name: 'Excluir' }))
      
      // Confirmar no dialog
      await user.click(screen.getByRole('button', { name: 'Sim, excluir' }))
      
      expect(mockOnClick).toHaveBeenCalledTimes(1)
    })

    it('deve cancelar ação no dialog', async () => {
      const mockOnClick = jest.fn()
      const user = userEvent.setup()
      
      render(
        <PermissionButton
          permission="users.delete"
          onClick={mockOnClick}
          requireConfirmation={true}
          cancelButtonText="Cancelar"
        >
          Excluir
        </PermissionButton>
      )
      
      // Clicar no botão principal
      await user.click(screen.getByRole('button', { name: 'Excluir' }))
      
      // Cancelar no dialog
      await user.click(screen.getByRole('button', { name: 'Cancelar' }))
      
      expect(mockOnClick).not.toHaveBeenCalled()
    })
  })

  describe('Estados de loading', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve mostrar estado de loading', () => {
      render(
        <PermissionButton
          permission="users.edit"
          loading={true}
          loadingText="Salvando..."
        >
          Salvar
        </PermissionButton>
      )
      
      expect(screen.getByRole('button', { name: 'Salvando...' })).toBeDisabled()
    })

    it('deve mostrar loading durante execução assíncrona', async () => {
      const mockOnClick = jest.fn().mockImplementation(
        () => new Promise(resolve => setTimeout(resolve, 100))
      )
      const user = userEvent.setup()
      
      render(
        <PermissionButton
          permission="users.edit"
          onClick={mockOnClick}
          loadingText="Processando..."
        >
          Processar
        </PermissionButton>
      )
      
      const button = screen.getByRole('button', { name: 'Processar' })
      await user.click(button)
      
      // Durante o loading
      expect(screen.getByRole('button', { name: 'Processando...' })).toBeDisabled()
      
      // Aguardar conclusão
      await waitFor(() => {
        expect(screen.getByRole('button', { name: 'Processar' })).not.toBeDisabled()
      })
    })
  })

  describe('usePermissionButton hook', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve fornecer botões pré-configurados', () => {
      const TestComponent = () => {
        const { EditButton, DeleteButton, ViewButton, CreateButton } = usePermissionButton()
        
        return (
          <div>
            <EditButton>Editar</EditButton>
            <DeleteButton>Excluir</DeleteButton>
            <ViewButton>Ver</ViewButton>
            <CreateButton>Criar</CreateButton>
          </div>
        )
      }
      
      render(<TestComponent />)
      
      expect(screen.getByRole('button', { name: 'Editar' })).toBeInTheDocument()
      expect(screen.getByRole('button', { name: 'Excluir' })).toBeInTheDocument()
      expect(screen.getByRole('button', { name: 'Ver' })).toBeInTheDocument()
      expect(screen.getByRole('button', { name: 'Criar' })).toBeInTheDocument()
    })

    it('DeleteButton deve ter confirmação por padrão', async () => {
      const user = userEvent.setup()
      
      const TestComponent = () => {
        const { DeleteButton } = usePermissionButton()
        return <DeleteButton>Excluir</DeleteButton>
      }
      
      render(<TestComponent />)
      
      await user.click(screen.getByRole('button', { name: 'Excluir' }))
      
      expect(screen.getByText('Confirmar Exclusão')).toBeInTheDocument()
    })
  })

  describe('Tratamento de erros', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve lidar com erros em onClick', async () => {
      const consoleErrorSpy = jest.spyOn(console, 'error').mockImplementation()
      const mockOnClick = jest.fn().mockRejectedValue(new Error('Test error'))
      const user = userEvent.setup()
      
      render(
        <PermissionButton permission="users.edit" onClick={mockOnClick}>
          Editar
        </PermissionButton>
      )
      
      await user.click(screen.getByRole('button', { name: 'Editar' }))
      
      await waitFor(() => {
        expect(consoleErrorSpy).toHaveBeenCalledWith(
          'Erro ao executar ação do PermissionButton:',
          expect.any(Error)
        )
      })
      
      consoleErrorSpy.mockRestore()
    })
  })

  describe('Acessibilidade', () => {
    beforeEach(() => {
      defaultPermissionsReturn.hasPermission.mockReturnValue(true)
    })

    it('deve aplicar aria-label corretamente', () => {
      render(
        <PermissionButton
          permission="users.edit"
          aria-label="Editar usuário"
        >
          <span>✏️</span>
        </PermissionButton>
      )
      
      expect(screen.getByRole('button', { name: 'Editar usuário' })).toBeInTheDocument()
    })

    it('deve aplicar title corretamente', () => {
      render(
        <PermissionButton
          permission="users.edit"
          title="Clique para editar"
        >
          Editar
        </PermissionButton>
      )
      
      expect(screen.getByRole('button', { name: 'Editar' })).toHaveAttribute('title', 'Clique para editar')
    })
  })
}) 
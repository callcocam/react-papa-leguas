import { renderHook } from '@testing-library/react'
import { usePage } from '@inertiajs/react'
import {
  usePermissions,
  useCan,
  useIs,
  useIsSuperAdmin,
  useIsAuthenticated,
  validatePermission,
  validateRole,
  type PermissionsData
} from '../usePermissions'

// Mock do Inertia.js
jest.mock('@inertiajs/react', () => ({
  usePage: jest.fn()
}))

const mockUsePage = usePage as jest.MockedFunction<typeof usePage>

describe('usePermissions Hook', () => {
  // Dados de teste
  const mockUser = {
    id: 1,
    name: 'João Silva',
    email: 'joao@example.com'
  }

  const mockPermissionsData: PermissionsData = {
    user_permissions: ['users.view', 'users.edit', 'posts.create', 'posts.view'],
    user_roles: ['editor', 'moderator'],
    is_super_admin: false
  }

  const mockSuperAdminData: PermissionsData = {
    user_permissions: ['users.view', 'users.edit'],
    user_roles: ['super-admin'],
    is_super_admin: true
  }

  const mockEmptyPermissionsData: PermissionsData = {
    user_permissions: [],
    user_roles: [],
    is_super_admin: false
  }

  beforeEach(() => {
    jest.clearAllMocks()
  })

  describe('Configuração básica', () => {
    it('deve retornar dados padrão quando não há permissões', () => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: undefined
        }
      } as any)

      const { result } = renderHook(() => usePermissions())

      expect(result.current.userPermissions).toEqual([])
      expect(result.current.userRoles).toEqual([])
      expect(result.current.isSuperAdmin).toBe(false)
      expect(result.current.isAuthenticated).toBe(true)
      expect(result.current.user).toEqual(mockUser)
      expect(result.current.permissionsCount).toBe(0)
      expect(result.current.rolesCount).toBe(0)
    })

    it('deve retornar dados corretos quando há permissões', () => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockPermissionsData
        }
      } as any)

      const { result } = renderHook(() => usePermissions())

      expect(result.current.userPermissions).toEqual(mockPermissionsData.user_permissions)
      expect(result.current.userRoles).toEqual(mockPermissionsData.user_roles)
      expect(result.current.isSuperAdmin).toBe(false)
      expect(result.current.isAuthenticated).toBe(true)
      expect(result.current.user).toEqual(mockUser)
      expect(result.current.permissionsCount).toBe(4)
      expect(result.current.rolesCount).toBe(2)
    })

    it('deve detectar usuário não autenticado', () => {
      mockUsePage.mockReturnValue({
        props: {
          auth: undefined,
          permissions: mockPermissionsData
        }
      } as any)

      const { result } = renderHook(() => usePermissions())

      expect(result.current.isAuthenticated).toBe(false)
      expect(result.current.user).toBe(null)
    })
  })

  describe('hasPermission - Validação de permissão única', () => {
    beforeEach(() => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockPermissionsData
        }
      } as any)
    })

    it('deve retornar true para permissão existente', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasPermission('users.view')).toBe(true)
      expect(result.current.hasPermission('users.edit')).toBe(true)
      expect(result.current.hasPermission('posts.create')).toBe(true)
    })

    it('deve retornar false para permissão inexistente', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasPermission('users.delete')).toBe(false)
      expect(result.current.hasPermission('admin.access')).toBe(false)
      expect(result.current.hasPermission('nonexistent.permission')).toBe(false)
    })

    it('deve retornar true para array de permissões (OR logic)', () => {
      const { result } = renderHook(() => usePermissions())
      
      // Pelo menos uma existe
      expect(result.current.hasPermission(['users.view', 'users.delete'])).toBe(true)
      expect(result.current.hasPermission(['nonexistent.permission', 'users.edit'])).toBe(true)
    })

    it('deve retornar false para array de permissões inexistentes', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasPermission(['users.delete', 'admin.access'])).toBe(false)
      expect(result.current.hasPermission(['nonexistent.permission'])).toBe(false)
    })
  })

  describe('hasAnyPermission - Validação OR', () => {
    beforeEach(() => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockPermissionsData
        }
      } as any)
    })

    it('deve retornar true se tiver qualquer uma das permissões', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasAnyPermission(['users.view', 'users.delete'])).toBe(true)
      expect(result.current.hasAnyPermission(['nonexistent.permission', 'users.edit'])).toBe(true)
    })

    it('deve retornar false se não tiver nenhuma das permissões', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasAnyPermission(['users.delete', 'admin.access'])).toBe(false)
      expect(result.current.hasAnyPermission([])).toBe(false)
    })
  })

  describe('hasAllPermissions - Validação AND', () => {
    beforeEach(() => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockPermissionsData
        }
      } as any)
    })

    it('deve retornar true se tiver todas as permissões', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasAllPermissions(['users.view', 'users.edit'])).toBe(true)
      expect(result.current.hasAllPermissions(['posts.create', 'posts.view'])).toBe(true)
    })

    it('deve retornar false se não tiver todas as permissões', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasAllPermissions(['users.view', 'users.delete'])).toBe(false)
      expect(result.current.hasAllPermissions(['users.edit', 'admin.access'])).toBe(false)
    })

    it('deve retornar false para array vazio', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasAllPermissions([])).toBe(false)
    })
  })

  describe('hasRole - Validação de roles', () => {
    beforeEach(() => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockPermissionsData
        }
      } as any)
    })

    it('deve retornar true para role existente', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasRole('editor')).toBe(true)
      expect(result.current.hasRole('moderator')).toBe(true)
    })

    it('deve retornar false para role inexistente', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasRole('admin')).toBe(false)
      expect(result.current.hasRole('super-admin')).toBe(false)
    })

    it('deve retornar true para array de roles (OR logic)', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasRole(['editor', 'admin'])).toBe(true)
      expect(result.current.hasRole(['nonexistent.role', 'moderator'])).toBe(true)
    })

    it('deve retornar false para array de roles inexistentes', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasRole(['admin', 'super-admin'])).toBe(false)
    })
  })

  describe('hasAnyRole e hasAllRoles', () => {
    beforeEach(() => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockPermissionsData
        }
      } as any)
    })

    it('hasAnyRole deve funcionar corretamente', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasAnyRole(['editor', 'admin'])).toBe(true)
      expect(result.current.hasAnyRole(['admin', 'super-admin'])).toBe(false)
      expect(result.current.hasAnyRole([])).toBe(false)
    })

    it('hasAllRoles deve funcionar corretamente', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasAllRoles(['editor', 'moderator'])).toBe(true)
      expect(result.current.hasAllRoles(['editor', 'admin'])).toBe(false)
      expect(result.current.hasAllRoles([])).toBe(false)
    })
  })

  describe('Super Admin', () => {
    beforeEach(() => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockSuperAdminData
        }
      } as any)
    })

    it('super admin deve ter todas as permissões', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.isSuperAdmin).toBe(true)
      expect(result.current.hasPermission('any.permission')).toBe(true)
      expect(result.current.hasPermission(['any.permission', 'another.permission'])).toBe(true)
      expect(result.current.hasAnyPermission(['any.permission'])).toBe(true)
      expect(result.current.hasAllPermissions(['any.permission', 'another.permission'])).toBe(true)
    })

    it('super admin deve ter todas as roles', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasRole('any.role')).toBe(true)
      expect(result.current.hasRole(['any.role', 'another.role'])).toBe(true)
      expect(result.current.hasAnyRole(['any.role'])).toBe(true)
      expect(result.current.hasAllRoles(['any.role', 'another.role'])).toBe(true)
    })
  })

  describe('Usuário não autenticado', () => {
    beforeEach(() => {
      mockUsePage.mockReturnValue({
        props: {
          auth: undefined,
          permissions: mockPermissionsData
        }
      } as any)
    })

    it('usuário não autenticado não deve ter permissões', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.isAuthenticated).toBe(false)
      expect(result.current.hasPermission('users.view')).toBe(false)
      expect(result.current.hasAnyPermission(['users.view'])).toBe(false)
      expect(result.current.hasAllPermissions(['users.view'])).toBe(false)
      expect(result.current.hasRole('editor')).toBe(false)
      expect(result.current.hasAnyRole(['editor'])).toBe(false)
      expect(result.current.hasAllRoles(['editor'])).toBe(false)
    })
  })

  describe('Aliases convenientes', () => {
    beforeEach(() => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockPermissionsData
        }
      } as any)
    })

    it('can deve ser alias para hasPermission', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.can('users.view')).toBe(true)
      expect(result.current.can('users.delete')).toBe(false)
      expect(result.current.can(['users.view', 'users.delete'])).toBe(true)
    })

    it('cannot deve ser negação de hasPermission', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.cannot('users.view')).toBe(false)
      expect(result.current.cannot('users.delete')).toBe(true)
      expect(result.current.cannot(['users.view', 'users.delete'])).toBe(false)
    })

    it('is deve ser alias para hasRole', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.is('editor')).toBe(true)
      expect(result.current.is('admin')).toBe(false)
      expect(result.current.is(['editor', 'admin'])).toBe(true)
    })

    it('isNot deve ser negação de hasRole', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.isNot('editor')).toBe(false)
      expect(result.current.isNot('admin')).toBe(true)
      expect(result.current.isNot(['editor', 'admin'])).toBe(false)
    })
  })

  describe('Hooks simplificados', () => {
    beforeEach(() => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockPermissionsData
        }
      } as any)
    })

    it('useCan deve funcionar corretamente', () => {
      const { result } = renderHook(() => useCan('users.view'))
      expect(result.current).toBe(true)

      const { result: result2 } = renderHook(() => useCan('users.delete'))
      expect(result2.current).toBe(false)

      const { result: result3 } = renderHook(() => useCan(['users.view', 'users.delete']))
      expect(result3.current).toBe(true)
    })

    it('useIs deve funcionar corretamente', () => {
      const { result } = renderHook(() => useIs('editor'))
      expect(result.current).toBe(true)

      const { result: result2 } = renderHook(() => useIs('admin'))
      expect(result2.current).toBe(false)

      const { result: result3 } = renderHook(() => useIs(['editor', 'admin']))
      expect(result3.current).toBe(true)
    })

    it('useIsSuperAdmin deve funcionar corretamente', () => {
      const { result } = renderHook(() => useIsSuperAdmin())
      expect(result.current).toBe(false)
    })

    it('useIsAuthenticated deve funcionar corretamente', () => {
      const { result } = renderHook(() => useIsAuthenticated())
      expect(result.current).toBe(true)
    })
  })

  describe('Funções utilitárias', () => {
    it('validatePermission deve funcionar fora de componentes', () => {
      expect(validatePermission(mockPermissionsData, 'users.view')).toBe(true)
      expect(validatePermission(mockPermissionsData, 'users.delete')).toBe(false)
      expect(validatePermission(mockPermissionsData, ['users.view', 'users.delete'])).toBe(true)
      expect(validatePermission(mockPermissionsData, ['users.delete', 'admin.access'])).toBe(false)
      
      // Super admin
      expect(validatePermission(mockSuperAdminData, 'any.permission')).toBe(true)
      
      // Sem dados
      expect(validatePermission(undefined, 'users.view')).toBe(false)
      expect(validatePermission(mockEmptyPermissionsData, 'users.view')).toBe(false)
    })

    it('validateRole deve funcionar fora de componentes', () => {
      expect(validateRole(mockPermissionsData, 'editor')).toBe(true)
      expect(validateRole(mockPermissionsData, 'admin')).toBe(false)
      expect(validateRole(mockPermissionsData, ['editor', 'admin'])).toBe(true)
      expect(validateRole(mockPermissionsData, ['admin', 'super-admin'])).toBe(false)
      
      // Super admin
      expect(validateRole(mockSuperAdminData, 'any.role')).toBe(true)
      
      // Sem dados
      expect(validateRole(undefined, 'editor')).toBe(false)
      expect(validateRole(mockEmptyPermissionsData, 'editor')).toBe(false)
    })
  })

  describe('Edge cases', () => {
    it('deve lidar com permissões vazias', () => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockEmptyPermissionsData
        }
      } as any)

      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasPermission('users.view')).toBe(false)
      expect(result.current.hasRole('editor')).toBe(false)
      expect(result.current.permissionsCount).toBe(0)
      expect(result.current.rolesCount).toBe(0)
    })

    it('deve lidar com dados malformados', () => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: {
            user_permissions: null,
            user_roles: null,
            is_super_admin: null
          }
        }
      } as any)

      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.userPermissions).toEqual([])
      expect(result.current.userRoles).toEqual([])
      expect(result.current.isSuperAdmin).toBe(false)
    })

    it('deve lidar com strings vazias e arrays vazios', () => {
      const { result } = renderHook(() => usePermissions())
      
      expect(result.current.hasPermission('')).toBe(false)
      expect(result.current.hasPermission([])).toBe(false)
      expect(result.current.hasRole('')).toBe(false)
      expect(result.current.hasRole([])).toBe(false)
    })
  })

  describe('debugInfo', () => {
    beforeEach(() => {
      mockUsePage.mockReturnValue({
        props: {
          auth: { user: mockUser },
          permissions: mockPermissionsData
        }
      } as any)
    })

    it('deve executar debugInfo sem erros', () => {
      const consoleSpy = jest.spyOn(console, 'group').mockImplementation()
      const consoleLogSpy = jest.spyOn(console, 'log').mockImplementation()
      const consoleGroupEndSpy = jest.spyOn(console, 'groupEnd').mockImplementation()

      const { result } = renderHook(() => usePermissions())
      
      expect(() => result.current.debugInfo()).not.toThrow()
      
      expect(consoleSpy).toHaveBeenCalledWith('🔐 Papa Leguas Permissions Debug')
      expect(consoleLogSpy).toHaveBeenCalledTimes(6)
      expect(consoleGroupEndSpy).toHaveBeenCalled()

      consoleSpy.mockRestore()
      consoleLogSpy.mockRestore()
      consoleGroupEndSpy.mockRestore()
    })
  })
}) 
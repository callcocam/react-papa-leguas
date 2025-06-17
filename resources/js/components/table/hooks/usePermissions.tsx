import { usePage } from '@inertiajs/react'
import { useMemo } from 'react'

/**
 * Dados de permissões vindos do Laravel via Inertia.js
 */
export interface PermissionsData {
  user_permissions: string[]
  user_roles: string[]
  is_super_admin: boolean
  [key: string]: any
}

/**
 * Props da página Inertia.js que incluem permissões
 */
interface InertiaPageProps {
  auth?: {
    user?: {
      id: number
      name: string
      email: string
      [key: string]: any
    }
  }
  permissions?: PermissionsData
  [key: string]: any
}

/**
 * Resultado do hook usePermissions
 */
export interface UsePermissionsReturn {
  // Funções de validação
  hasPermission: (permission: string | string[]) => boolean
  hasAnyPermission: (permissions: string[]) => boolean
  hasAllPermissions: (permissions: string[]) => boolean
  hasRole: (role: string | string[]) => boolean
  hasAnyRole: (roles: string[]) => boolean
  hasAllRoles: (roles: string[]) => boolean
  
  // Dados do usuário
  userPermissions: string[]
  userRoles: string[]
  isSuperAdmin: boolean
  isAuthenticated: boolean
  user: any | null
  
  // Funções utilitárias
  can: (permission: string | string[]) => boolean
  cannot: (permission: string | string[]) => boolean
  is: (role: string | string[]) => boolean
  isNot: (role: string | string[]) => boolean
  
  // Debug e informações
  permissionsCount: number
  rolesCount: number
  debugInfo: () => void
}

/**
 * Hook para gerenciamento de permissões integrado com Laravel/Spatie
 * 
 * Este hook acessa as permissões do usuário vindas do backend Laravel
 * via Inertia.js props e fornece funções de validação convenientes.
 * 
 * @returns Objeto com funções de validação e dados do usuário
 */
export const usePermissions = (): UsePermissionsReturn => {
  // Acessar dados da página Inertia.js
  const { props } = usePage<InertiaPageProps>()
  
  // Extrair dados de permissões e autenticação
  const permissionsData = props.permissions
  const authData = props.auth
  const user = authData?.user || null
  
  // Dados de permissões com fallbacks seguros
  const userPermissions = useMemo(() => {
    return permissionsData?.user_permissions || []
  }, [permissionsData?.user_permissions])
  
  const userRoles = useMemo(() => {
    return permissionsData?.user_roles || []
  }, [permissionsData?.user_roles])
  
  const isSuperAdmin = useMemo(() => {
    return permissionsData?.is_super_admin || false
  }, [permissionsData?.is_super_admin])
  
  const isAuthenticated = useMemo(() => {
    return !!user
  }, [user])
  
  // Função principal de validação de permissão
  const hasPermission = useMemo(() => {
    return (permission: string | string[]): boolean => {
      // Super admin tem todas as permissões
      if (isSuperAdmin) return true
      
      // Se não está autenticado, não tem permissões
      if (!isAuthenticated) return false
      
      // Se não há permissões definidas, negar acesso
      if (!userPermissions.length) return false
      
      // Validar permissão única
      if (typeof permission === 'string') {
        return userPermissions.includes(permission)
      }
      
      // Validar array de permissões (OR logic - qualquer uma serve)
      if (Array.isArray(permission)) {
        return permission.some(perm => userPermissions.includes(perm))
      }
      
      return false
    }
  }, [isSuperAdmin, isAuthenticated, userPermissions])
  
  // Validar se tem QUALQUER uma das permissões (OR logic)
  const hasAnyPermission = useMemo(() => {
    return (permissions: string[]): boolean => {
      if (isSuperAdmin) return true
      if (!isAuthenticated || !permissions.length) return false
      
      return permissions.some(permission => userPermissions.includes(permission))
    }
  }, [isSuperAdmin, isAuthenticated, userPermissions])
  
  // Validar se tem TODAS as permissões (AND logic)
  const hasAllPermissions = useMemo(() => {
    return (permissions: string[]): boolean => {
      if (isSuperAdmin) return true
      if (!isAuthenticated || !permissions.length) return false
      
      return permissions.every(permission => userPermissions.includes(permission))
    }
  }, [isSuperAdmin, isAuthenticated, userPermissions])
  
  // Função principal de validação de role
  const hasRole = useMemo(() => {
    return (role: string | string[]): boolean => {
      // Super admin tem todas as roles
      if (isSuperAdmin) return true
      
      // Se não está autenticado, não tem roles
      if (!isAuthenticated) return false
      
      // Se não há roles definidas, negar acesso
      if (!userRoles.length) return false
      
      // Validar role única
      if (typeof role === 'string') {
        return userRoles.includes(role)
      }
      
      // Validar array de roles (OR logic - qualquer uma serve)
      if (Array.isArray(role)) {
        return role.some(r => userRoles.includes(r))
      }
      
      return false
    }
  }, [isSuperAdmin, isAuthenticated, userRoles])
  
  // Validar se tem QUALQUER uma das roles (OR logic)
  const hasAnyRole = useMemo(() => {
    return (roles: string[]): boolean => {
      if (isSuperAdmin) return true
      if (!isAuthenticated || !roles.length) return false
      
      return roles.some(role => userRoles.includes(role))
    }
  }, [isSuperAdmin, isAuthenticated, userRoles])
  
  // Validar se tem TODAS as roles (AND logic)
  const hasAllRoles = useMemo(() => {
    return (roles: string[]): boolean => {
      if (isSuperAdmin) return true
      if (!isAuthenticated || !roles.length) return false
      
      return roles.every(role => userRoles.includes(role))
    }
  }, [isSuperAdmin, isAuthenticated, userRoles])
  
  // Aliases convenientes
  const can = hasPermission
  const cannot = useMemo(() => {
    return (permission: string | string[]): boolean => !hasPermission(permission)
  }, [hasPermission])
  
  const is = hasRole
  const isNot = useMemo(() => {
    return (role: string | string[]): boolean => !hasRole(role)
  }, [hasRole])
  
  // Informações de debug
  const debugInfo = useMemo(() => {
    return (): void => {
      console.group('🔐 Papa Leguas Permissions Debug')
      console.log('👤 Usuário:', user)
      console.log('🔑 Autenticado:', isAuthenticated)
      console.log('⭐ Super Admin:', isSuperAdmin)
      console.log('📋 Permissões:', userPermissions)
      console.log('🎭 Roles:', userRoles)
      console.log('📊 Contadores:', {
        permissões: userPermissions.length,
        roles: userRoles.length
      })
      console.log('🔍 Props Inertia:', props)
      console.groupEnd()
    }
  }, [user, isAuthenticated, isSuperAdmin, userPermissions, userRoles, props])
  
  return {
    // Funções de validação
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    hasRole,
    hasAnyRole,
    hasAllRoles,
    
    // Dados do usuário
    userPermissions,
    userRoles,
    isSuperAdmin,
    isAuthenticated,
    user,
    
    // Aliases convenientes
    can,
    cannot,
    is,
    isNot,
    
    // Informações úteis
    permissionsCount: userPermissions.length,
    rolesCount: userRoles.length,
    debugInfo
  }
}

/**
 * Hook simplificado para validação rápida de permissão
 * 
 * @param permission - Permissão ou array de permissões para validar
 * @returns boolean indicando se o usuário tem a permissão
 */
export const useCan = (permission: string | string[]): boolean => {
  const { hasPermission } = usePermissions()
  return hasPermission(permission)
}

/**
 * Hook simplificado para validação rápida de role
 * 
 * @param role - Role ou array de roles para validar
 * @returns boolean indicando se o usuário tem a role
 */
export const useIs = (role: string | string[]): boolean => {
  const { hasRole } = usePermissions()
  return hasRole(role)
}

/**
 * Hook para verificar se usuário é super admin
 * 
 * @returns boolean indicando se é super admin
 */
export const useIsSuperAdmin = (): boolean => {
  const { isSuperAdmin } = usePermissions()
  return isSuperAdmin
}

/**
 * Hook para verificar se usuário está autenticado
 * 
 * @returns boolean indicando se está autenticado
 */
export const useIsAuthenticated = (): boolean => {
  const { isAuthenticated } = usePermissions()
  return isAuthenticated
}

/**
 * Função utilitária para validar permissões fora de componentes React
 * 
 * @param permissionsData - Dados de permissões
 * @param permission - Permissão para validar
 * @returns boolean indicando se tem a permissão
 */
export const validatePermission = (
  permissionsData: PermissionsData | undefined,
  permission: string | string[]
): boolean => {
  // Super admin tem todas as permissões
  if (permissionsData?.is_super_admin) return true
  
  // Se não há dados de permissões, negar acesso
  if (!permissionsData?.user_permissions?.length) return false
  
  const userPermissions = permissionsData.user_permissions
  
  // Validar permissão única
  if (typeof permission === 'string') {
    return userPermissions.includes(permission)
  }
  
  // Validar array de permissões (OR logic)
  if (Array.isArray(permission)) {
    return permission.some(perm => userPermissions.includes(perm))
  }
  
  return false
}

/**
 * Função utilitária para validar roles fora de componentes React
 * 
 * @param permissionsData - Dados de permissões
 * @param role - Role para validar
 * @returns boolean indicando se tem a role
 */
export const validateRole = (
  permissionsData: PermissionsData | undefined,
  role: string | string[]
): boolean => {
  // Super admin tem todas as roles
  if (permissionsData?.is_super_admin) return true
  
  // Se não há dados de roles, negar acesso
  if (!permissionsData?.user_roles?.length) return false
  
  const userRoles = permissionsData.user_roles
  
  // Validar role única
  if (typeof role === 'string') {
    return userRoles.includes(role)
  }
  
  // Validar array de roles (OR logic)
  if (Array.isArray(role)) {
    return role.some(r => userRoles.includes(r))
  }
  
  return false
}

export default usePermissions 
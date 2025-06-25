import React from 'react'
import { Link } from '@inertiajs/react'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { usePermissions } from '../hooks/usePermissions'
import { cn } from '@/lib/utils'

/**
 * Props do PermissionLink
 */
interface PermissionLinkProps {
  // Permissões (opcional - backend já filtrou)
  permission?: string | string[]
  fallbackBehavior?: 'hide' | 'disable' | 'show'
  disabledReason?: string
  showTooltip?: boolean
  
  // Link properties
  href: string
  route?: string
  method?: 'get' | 'post' | 'put' | 'patch' | 'delete'
  data?: Record<string, any>
  
  // Inertia.js options
  preserveScroll?: boolean
  preserveState?: boolean
  replace?: boolean
  only?: string[]
  except?: string[]
  onlyActiveOnIndex?: boolean
  
  // Styling
  className?: string
  activeClassName?: string
  inactiveClassName?: string
  disabledClassName?: string
  
  // Content
  children: React.ReactNode
  
  // Events
  onClick?: (e: React.MouseEvent) => void
  onSuccess?: () => void
  onError?: () => void
  
  // Acessibilidade
  'aria-label'?: string
  title?: string
  target?: string
  rel?: string
  
  // Nova prop para forçar validação no frontend quando necessário
  validatePermissions?: boolean
}

/**
 * Componente de Link com verificação OPCIONAL de permissões
 * 
 * Por padrão, assume que o backend já filtrou as permissões.
 * Use validatePermissions={true} apenas quando necessário validar no frontend.
 */
export function PermissionLink({
  permission,
  href,
  children,
  className = '',
  activeClassName = '',
  fallbackBehavior = 'show',
  validatePermissions = false,
  ...props
}: PermissionLinkProps) {
  const { hasPermission } = usePermissions()
  
  // Se não deve validar permissões OU não há permissão definida, renderizar normalmente
  if (!validatePermissions || !permission) {
    return (
      <Link
        href={href}
        className={cn(className, activeClassName)}
        {...props}
      >
        {children}
      </Link>
    )
  }

  // Validação de permissões (apenas quando solicitado)
  const hasRequiredPermission = Array.isArray(permission)
    ? permission.some(p => hasPermission(p))
    : hasPermission(permission)

  // Comportamento baseado na permissão
  if (!hasRequiredPermission) {
    switch (fallbackBehavior) {
      case 'hide':
        return null
      case 'disable':
        return (
          <span className={cn(className, 'opacity-50 cursor-not-allowed')} {...props}>
            {children}
          </span>
        )
      case 'show':
      default:
        return (
          <Link
            href={href}
            className={cn(className, activeClassName)}
            {...props}
          >
            {children}
          </Link>
        )
    }
  }

  // Usuário tem permissão, renderizar link normal
  return (
    <Link
      href={href}
      className={cn(className, activeClassName)}
      {...props}
    >
      {children}
    </Link>
  )
}

/**
 * Hook para criar PermissionLink com configurações pré-definidas
 */
export const usePermissionLink = () => {
  const { hasPermission } = usePermissions()
  
  return {
    hasPermission,
    
    // Link simples (sem validação - backend já filtrou)
    SimpleLink: (props: Omit<PermissionLinkProps, 'validatePermissions'>) => (
      <PermissionLink
        validatePermissions={false}
        {...props}
      />
    ),
    
    // Link com validação forçada (quando necessário)
    ValidatedLink: (props: Omit<PermissionLinkProps, 'validatePermissions'>) => (
      <PermissionLink
        validatePermissions={true}
        {...props}
      />
    ),
    
    // Link de navegação padrão (sem validação)
    NavLink: (props: Omit<PermissionLinkProps, 'activeClassName' | 'inactiveClassName' | 'validatePermissions'>) => (
      <PermissionLink
        activeClassName="text-primary font-medium"
        validatePermissions={false}
        {...props}
      />
    ),
    
    // Link de menu lateral (sem validação)
    SidebarLink: (props: Omit<PermissionLinkProps, 'className' | 'activeClassName' | 'validatePermissions'>) => (
      <PermissionLink
        className="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary"
        activeClassName="bg-muted text-primary"
        validatePermissions={false}
        {...props}
      />
    ),
  }
}

/**
 * Componente PermissionNavLink - sem validação (backend já filtrou)
 */
export const PermissionNavLink: React.FC<PermissionLinkProps> = (props) => {
  return (
    <PermissionLink
      activeClassName="text-primary font-medium border-b-2 border-primary"
      className="px-4 py-2 transition-colors"
      validatePermissions={false}
      {...props}
    />
  )
}

/**
 * Componente PermissionSidebarLink - sem validação (backend já filtrou)
 */
export const PermissionSidebarLink: React.FC<PermissionLinkProps> = (props) => {
  return (
    <PermissionLink
      className="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary hover:bg-muted"
      activeClassName="bg-muted text-primary"
      validatePermissions={false}
      {...props}
    />
  )
}

/**
 * Componente PermissionBreadcrumbLink - sem validação (backend já filtrou)
 */
export const PermissionBreadcrumbLink: React.FC<PermissionLinkProps> = (props) => {
  return (
    <PermissionLink
      className="text-muted-foreground hover:text-foreground transition-colors"
      fallbackBehavior="show"
      validatePermissions={false}
      {...props}
    />
  )
}

export default PermissionLink 
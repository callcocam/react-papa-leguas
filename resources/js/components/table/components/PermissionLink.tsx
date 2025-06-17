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
export interface PermissionLinkProps {
  // Permissões
  permission: string | string[]
  fallbackBehavior?: 'hide' | 'disable' | 'show'
  disabledReason?: string
  showTooltip?: boolean
  
  // Link properties
  href?: string
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
}

/**
 * Componente PermissionLink
 * 
 * Link que valida automaticamente permissões do usuário antes de permitir navegação.
 * Integrado com Inertia.js Link e sistema de permissões do Papa Leguas.
 */
export const PermissionLink: React.FC<PermissionLinkProps> = ({
  // Permissões
  permission,
  fallbackBehavior = 'hide',
  disabledReason,
  showTooltip = true,
  
  // Link props
  href,
  route,
  method = 'get',
  data,
  
  // Inertia options
  preserveScroll = false,
  preserveState = false,
  replace = false,
  only,
  except,
  onlyActiveOnIndex = false,
  
  // Styling
  className,
  activeClassName,
  inactiveClassName,
  disabledClassName = 'opacity-50 cursor-not-allowed pointer-events-none',
  
  // Content
  children,
  
  // Events
  onClick,
  onSuccess,
  onError,
  
  // Acessibilidade
  'aria-label': ariaLabel,
  title,
  target,
  rel,
}) => {
  const { hasPermission } = usePermissions()
  
  // Verificar se usuário tem permissão
  const hasAccess = hasPermission(permission)
  
  // Determinar comportamento baseado em permissões
  const shouldHide = !hasAccess && fallbackBehavior === 'hide'
  const shouldDisable = !hasAccess && fallbackBehavior === 'disable'
  
  // Se deve esconder, não renderizar nada
  if (shouldHide) {
    return null
  }
  
  // Determinar URL do link
  const linkUrl = href || route || '#'
  
  // Handler do clique
  const handleClick = (e: React.MouseEvent) => {
    // Se desabilitado, prevenir ação
    if (shouldDisable) {
      e.preventDefault()
      e.stopPropagation()
      return
    }
    
    // Executar onClick customizado se fornecido
    if (onClick) {
      onClick(e)
    }
  }
  
  // Determinar classes CSS
  const linkClasses = cn(
    // Classes base
    'transition-colors duration-200',
    
    // Classes quando desabilitado por permissão
    shouldDisable && disabledClassName,
    
    // Classes customizadas
    className,
    
    // Classes de estado ativo/inativo (se fornecidas)
    !shouldDisable && activeClassName,
    !shouldDisable && inactiveClassName
  )
  
  // Determinar motivo da desabilitação
  const getDisabledReason = (): string => {
    if (!hasAccess) return disabledReason || 'Você não tem permissão para acessar este link'
    return ''
  }
  
  // Componente do link
  const LinkComponent = (
    <Link
      href={linkUrl}
      method={method}
      data={data}
      preserveScroll={preserveScroll}
      preserveState={preserveState}
      replace={replace}
      only={only}
      except={except}
      className={linkClasses}
      onClick={handleClick}
      onSuccess={onSuccess}
      onError={onError}
      aria-label={ariaLabel}
      title={title}
      target={target}
      rel={rel}
    >
      {children}
    </Link>
  )
  
  // Se deve mostrar tooltip e está desabilitado, envolver com Tooltip
  if (showTooltip && shouldDisable) {
    return (
      <TooltipProvider>
        <Tooltip>
          <TooltipTrigger asChild>
            {LinkComponent}
          </TooltipTrigger>
          <TooltipContent>
            <p>{getDisabledReason()}</p>
          </TooltipContent>
        </Tooltip>
      </TooltipProvider>
    )
  }
  
  // Renderizar link normal
  return LinkComponent
}

/**
 * Hook para criar PermissionLink com configurações pré-definidas
 */
export const usePermissionLink = () => {
  const { hasPermission } = usePermissions()
  
  return {
    hasPermission,
    
    // Link de edição padrão
    EditLink: (props: Omit<PermissionLinkProps, 'permission' | 'className'>) => (
      <PermissionLink
        permission="edit"
        className="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
        {...props}
      />
    ),
    
    // Link de visualização padrão
    ViewLink: (props: Omit<PermissionLinkProps, 'permission' | 'className'>) => (
      <PermissionLink
        permission="view"
        className="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-300"
        {...props}
      />
    ),
    
    // Link de criação padrão
    CreateLink: (props: Omit<PermissionLinkProps, 'permission' | 'className'>) => (
      <PermissionLink
        permission="create"
        className="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-300"
        {...props}
      />
    ),
    
    // Link de exclusão padrão (geralmente não usado, mas disponível)
    DeleteLink: (props: Omit<PermissionLinkProps, 'permission' | 'className'>) => (
      <PermissionLink
        permission="delete"
        className="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300"
        {...props}
      />
    ),
    
    // Link de navegação padrão
    NavLink: (props: Omit<PermissionLinkProps, 'activeClassName' | 'inactiveClassName'>) => (
      <PermissionLink
        activeClassName="text-primary font-medium"
        inactiveClassName="text-muted-foreground hover:text-foreground"
        {...props}
      />
    ),
    
    // Link de menu lateral
    SidebarLink: (props: Omit<PermissionLinkProps, 'className' | 'activeClassName'>) => (
      <PermissionLink
        className="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary"
        activeClassName="bg-muted text-primary"
        {...props}
      />
    ),
  }
}

/**
 * Componente PermissionNavLink - especializado para navegação
 */
export const PermissionNavLink: React.FC<PermissionLinkProps> = (props) => {
  return (
    <PermissionLink
      activeClassName="text-primary font-medium border-b-2 border-primary"
      inactiveClassName="text-muted-foreground hover:text-foreground"
      className="px-4 py-2 transition-colors"
      {...props}
    />
  )
}

/**
 * Componente PermissionSidebarLink - especializado para sidebar
 */
export const PermissionSidebarLink: React.FC<PermissionLinkProps> = (props) => {
  return (
    <PermissionLink
      className="flex items-center gap-3 rounded-lg px-3 py-2 text-muted-foreground transition-all hover:text-primary hover:bg-muted"
      activeClassName="bg-muted text-primary"
      {...props}
    />
  )
}

/**
 * Componente PermissionBreadcrumbLink - especializado para breadcrumbs
 */
export const PermissionBreadcrumbLink: React.FC<PermissionLinkProps> = (props) => {
  return (
    <PermissionLink
      className="text-muted-foreground hover:text-foreground transition-colors"
      fallbackBehavior="show"
      showTooltip={false}
      {...props}
    />
  )
}

export default PermissionLink 
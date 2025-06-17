import React, { useState } from 'react'
import { router } from '@inertiajs/react'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import { usePermissions } from '../hooks/usePermissions'
import { cn } from '@/lib/utils'

/**
 * Props do PermissionButton
 */
export interface PermissionButtonProps {
  // Permissões
  permission: string | string[]
  fallbackBehavior?: 'hide' | 'disable' | 'show'
  disabledReason?: string
  showTooltip?: boolean
  
  // Propriedades do Button (shadcn/ui)
  variant?: 'default' | 'destructive' | 'outline' | 'secondary' | 'ghost' | 'link'
  size?: 'default' | 'sm' | 'lg' | 'icon'
  className?: string
  disabled?: boolean
  children: React.ReactNode
  
  // Ações
  onClick?: () => void | Promise<void>
  route?: string
  method?: 'get' | 'post' | 'put' | 'patch' | 'delete'
  data?: Record<string, any>
  
  // Opções do Inertia.js
  preserveScroll?: boolean
  preserveState?: boolean
  replace?: boolean
  only?: string[]
  except?: string[]
  
  // Sistema de confirmação
  requireConfirmation?: boolean
  confirmTitle?: string
  confirmDescription?: string
  confirmButtonText?: string
  cancelButtonText?: string
  confirmVariant?: 'default' | 'destructive'
  
  // Loading state
  loading?: boolean
  loadingText?: string
  
  // Acessibilidade
  'aria-label'?: string
  title?: string
}

/**
 * Componente PermissionButton
 * 
 * Botão que valida automaticamente permissões do usuário antes de permitir ação.
 * Integrado com shadcn/ui Button e sistema de permissões do Papa Leguas.
 */
export const PermissionButton: React.FC<PermissionButtonProps> = ({
  // Permissões
  permission,
  fallbackBehavior = 'hide',
  disabledReason,
  showTooltip = true,
  
  // Button props
  variant = 'default',
  size = 'default',
  className,
  disabled = false,
  children,
  
  // Ações
  onClick,
  route,
  method = 'get',
  data,
  
  // Inertia options
  preserveScroll = false,
  preserveState = false,
  replace = false,
  only,
  except,
  
  // Confirmação
  requireConfirmation = false,
  confirmTitle = 'Confirmar Ação',
  confirmDescription = 'Tem certeza que deseja continuar?',
  confirmButtonText = 'Confirmar',
  cancelButtonText = 'Cancelar',
  confirmVariant = 'default',
  
  // Loading
  loading = false,
  loadingText = 'Carregando...',
  
  // Acessibilidade
  'aria-label': ariaLabel,
  title,
  
  ...restProps
}) => {
  const { hasPermission } = usePermissions()
  const [isConfirmOpen, setIsConfirmOpen] = useState(false)
  const [isLoading, setIsLoading] = useState(false)
  
  // Verificar se usuário tem permissão
  const hasAccess = hasPermission(permission)
  
  // Determinar estado do botão baseado em permissões
  const shouldHide = !hasAccess && fallbackBehavior === 'hide'
  const shouldDisable = !hasAccess && fallbackBehavior === 'disable'
  const isButtonDisabled = disabled || shouldDisable || loading || isLoading
  
  // Se deve esconder, não renderizar nada
  if (shouldHide) {
    return null
  }
  
  // Função para executar ação
  const executeAction = async () => {
    if (isButtonDisabled) return
    
    try {
      setIsLoading(true)
      
      // Executar onClick se fornecido
      if (onClick) {
        await onClick()
      }
      
      // Navegar via Inertia se route fornecida
      if (route) {
        const options = {
          method,
          data,
          preserveScroll,
          preserveState,
          replace,
          only,
          except,
        }
        
        if (method === 'get') {
          router.get(route, data || {}, {
            preserveScroll,
            preserveState,
            replace,
            only,
            except,
          })
        } else {
          router[method](route, data || {}, {
            preserveScroll,
            preserveState,
            replace,
            only,
            except,
          })
        }
      }
    } catch (error) {
      console.error('Erro ao executar ação do PermissionButton:', error)
    } finally {
      setIsLoading(false)
    }
  }
  
  // Handler do clique
  const handleClick = () => {
    if (isButtonDisabled) return
    
    if (requireConfirmation) {
      setIsConfirmOpen(true)
    } else {
      executeAction()
    }
  }
  
  // Handler da confirmação
  const handleConfirm = () => {
    setIsConfirmOpen(false)
    executeAction()
  }
  
  // Determinar texto do botão
  const buttonText = (loading || isLoading) ? loadingText : children
  
  // Determinar motivo da desabilitação
  const getDisabledReason = (): string => {
    if (loading || isLoading) return loadingText
    if (!hasAccess) return disabledReason || 'Você não tem permissão para esta ação'
    if (disabled) return 'Ação desabilitada'
    return ''
  }
  
  // Componente do botão
  const ButtonComponent = (
    <Button
      variant={variant}
      size={size}
      className={cn(
        // Classes base
        'relative',
        
        // Classes quando desabilitado por permissão
        shouldDisable && 'opacity-50 cursor-not-allowed',
        
        // Classes customizadas
        className
      )}
      disabled={isButtonDisabled}
      onClick={handleClick}
      aria-label={ariaLabel}
      title={title}
      {...restProps}
    >
      {buttonText}
    </Button>
  )
  
  // Se deve mostrar tooltip e está desabilitado, envolver com Tooltip
  if (showTooltip && isButtonDisabled) {
    return (
      <>
        <TooltipProvider>
          <Tooltip>
            <TooltipTrigger asChild>
              {ButtonComponent}
            </TooltipTrigger>
            <TooltipContent>
              <p>{getDisabledReason()}</p>
            </TooltipContent>
          </Tooltip>
        </TooltipProvider>
        
        {/* Dialog de confirmação */}
        <Dialog open={isConfirmOpen} onOpenChange={setIsConfirmOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>{confirmTitle}</DialogTitle>
              <DialogDescription>
                {confirmDescription}
              </DialogDescription>
            </DialogHeader>
            <DialogFooter>
              <Button variant="outline" onClick={() => setIsConfirmOpen(false)}>
                {cancelButtonText}
              </Button>
              <Button
                variant={confirmVariant}
                onClick={handleConfirm}
              >
                {confirmButtonText}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      </>
    )
  }
  
  // Renderizar botão normal com dialog de confirmação
  return (
    <>
      {ButtonComponent}
      
      {/* Dialog de confirmação */}
      {requireConfirmation && (
        <Dialog open={isConfirmOpen} onOpenChange={setIsConfirmOpen}>
          <DialogContent>
            <DialogHeader>
              <DialogTitle>{confirmTitle}</DialogTitle>
              <DialogDescription>
                {confirmDescription}
              </DialogDescription>
            </DialogHeader>
            <DialogFooter>
              <Button variant="outline" onClick={() => setIsConfirmOpen(false)}>
                {cancelButtonText}
              </Button>
              <Button
                variant={confirmVariant}
                onClick={handleConfirm}
              >
                {confirmButtonText}
              </Button>
            </DialogFooter>
          </DialogContent>
        </Dialog>
      )}
    </>
  )
}

/**
 * Hook para criar PermissionButton com configurações pré-definidas
 */
export const usePermissionButton = () => {
  const { hasPermission } = usePermissions()
  
  return {
    hasPermission,
    
    // Botão de edição padrão
    EditButton: (props: Omit<PermissionButtonProps, 'permission' | 'variant'>) => (
      <PermissionButton
        permission="edit"
        variant="outline"
        size="sm"
        {...props}
      />
    ),
    
    // Botão de exclusão padrão
    DeleteButton: (props: Omit<PermissionButtonProps, 'permission' | 'variant' | 'requireConfirmation'>) => (
      <PermissionButton
        permission="delete"
        variant="destructive"
        size="sm"
        requireConfirmation={true}
        confirmTitle="Confirmar Exclusão"
        confirmDescription="Esta ação não pode ser desfeita."
        confirmButtonText="Excluir"
        confirmVariant="destructive"
        {...props}
      />
    ),
    
    // Botão de visualização padrão
    ViewButton: (props: Omit<PermissionButtonProps, 'permission' | 'variant'>) => (
      <PermissionButton
        permission="view"
        variant="ghost"
        size="sm"
        {...props}
      />
    ),
    
    // Botão de criação padrão
    CreateButton: (props: Omit<PermissionButtonProps, 'permission' | 'variant'>) => (
      <PermissionButton
        permission="create"
        variant="default"
        {...props}
      />
    ),
  }
}

export default PermissionButton 
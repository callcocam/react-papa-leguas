import React from 'react'
import { Edit, Trash2, Eye, Plus, Settings, Users } from 'lucide-react'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Separator } from '@/components/ui/separator'
import { usePermissions, useCan, useIs, useIsSuperAdmin } from '../hooks/usePermissions'
import { PermissionButton, usePermissionButton } from '../components/PermissionButton'
import { 
  PermissionLink, 
  PermissionNavLink, 
  PermissionSidebarLink,
  usePermissionLink 
} from '../components/PermissionLink'

/**
 * Exemplo completo do Sistema de Permissões Papa Leguas
 * 
 * Demonstra todos os componentes e hooks de permissão disponíveis
 */
export const PermissionsExample: React.FC = () => {
  // Hook principal de permissões
  const {
    hasPermission,
    hasAnyPermission,
    hasAllPermissions,
    hasRole,
    userPermissions,
    userRoles,
    isSuperAdmin,
    isAuthenticated,
    user,
    can,
    cannot,
    is,
    isNot,
    permissionsCount,
    rolesCount,
    debugInfo
  } = usePermissions()
  
  // Hooks simplificados
  const canEditUsers = useCan('users.edit')
  const canDeleteUsers = useCan('users.delete')
  const isAdmin = useIs('admin')
  const isSuperAdminUser = useIsSuperAdmin()
  
  // Hooks de componentes pré-configurados
  const { EditButton, DeleteButton, ViewButton, CreateButton } = usePermissionButton()
  const { EditLink, ViewLink, CreateLink, NavLink, SidebarLink } = usePermissionLink()
  
  // Dados de exemplo
  const sampleUser = {
    id: 1,
    name: 'João Silva',
    email: 'joao@example.com',
    status: 'active'
  }
  
  return (
    <div className="space-y-6 p-6">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-3xl font-bold">Sistema de Permissões Papa Leguas</h1>
          <p className="text-muted-foreground">
            Demonstração completa dos componentes e hooks de permissão
          </p>
        </div>
        
        <div className="flex gap-2">
          <Badge variant={isAuthenticated ? 'default' : 'destructive'}>
            {isAuthenticated ? 'Autenticado' : 'Não Autenticado'}
          </Badge>
          {isSuperAdmin && (
            <Badge variant="secondary">Super Admin</Badge>
          )}
        </div>
      </div>
      
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {/* Informações do Usuário */}
        <Card>
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <Users className="w-5 h-5" />
              Informações do Usuário
            </CardTitle>
            <CardDescription>
              Dados do usuário atual e estatísticas
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            <div>
              <strong>Nome:</strong> {user?.name || 'Não disponível'}
            </div>
            <div>
              <strong>Email:</strong> {user?.email || 'Não disponível'}
            </div>
            <div>
              <strong>Permissões:</strong> {permissionsCount}
            </div>
            <div>
              <strong>Roles:</strong> {rolesCount}
            </div>
            
            <Separator />
            
            <div className="space-y-2">
              <div className="text-sm font-medium">Permissões:</div>
              <div className="flex flex-wrap gap-1">
                {userPermissions.slice(0, 5).map(permission => (
                  <Badge key={permission} variant="outline" className="text-xs">
                    {permission}
                  </Badge>
                ))}
                {userPermissions.length > 5 && (
                  <Badge variant="secondary" className="text-xs">
                    +{userPermissions.length - 5} mais
                  </Badge>
                )}
              </div>
            </div>
            
            <div className="space-y-2">
              <div className="text-sm font-medium">Roles:</div>
              <div className="flex flex-wrap gap-1">
                {userRoles.map(role => (
                  <Badge key={role} variant="default" className="text-xs">
                    {role}
                  </Badge>
                ))}
              </div>
            </div>
            
            <button
              onClick={debugInfo}
              className="w-full mt-4 px-3 py-2 text-sm bg-gray-100 dark:bg-gray-800 rounded hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors"
            >
              🔍 Debug no Console
            </button>
          </CardContent>
        </Card>
        
        {/* Validações de Permissão */}
        <Card>
          <CardHeader>
            <CardTitle>Validações de Permissão</CardTitle>
            <CardDescription>
              Exemplos de validação usando hooks
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-3">
            <div className="space-y-2">
              <div className="text-sm font-medium">Permissões Individuais:</div>
              <div className="space-y-1 text-sm">
                <div className="flex justify-between">
                  <span>users.edit:</span>
                  <Badge variant={canEditUsers ? 'default' : 'destructive'}>
                    {canEditUsers ? '✓' : '✗'}
                  </Badge>
                </div>
                <div className="flex justify-between">
                  <span>users.delete:</span>
                  <Badge variant={canDeleteUsers ? 'default' : 'destructive'}>
                    {canDeleteUsers ? '✓' : '✗'}
                  </Badge>
                </div>
                <div className="flex justify-between">
                  <span>users.create:</span>
                  <Badge variant={can('users.create') ? 'default' : 'destructive'}>
                    {can('users.create') ? '✓' : '✗'}
                  </Badge>
                </div>
              </div>
            </div>
            
            <Separator />
            
            <div className="space-y-2">
              <div className="text-sm font-medium">Validações Múltiplas:</div>
              <div className="space-y-1 text-sm">
                <div className="flex justify-between">
                  <span>Qualquer user.*:</span>
                  <Badge variant={hasAnyPermission(['users.edit', 'users.delete', 'users.create']) ? 'default' : 'destructive'}>
                    {hasAnyPermission(['users.edit', 'users.delete', 'users.create']) ? '✓' : '✗'}
                  </Badge>
                </div>
                <div className="flex justify-between">
                  <span>Todas user.*:</span>
                  <Badge variant={hasAllPermissions(['users.edit', 'users.delete', 'users.create']) ? 'default' : 'destructive'}>
                    {hasAllPermissions(['users.edit', 'users.delete', 'users.create']) ? '✓' : '✗'}
                  </Badge>
                </div>
              </div>
            </div>
            
            <Separator />
            
            <div className="space-y-2">
              <div className="text-sm font-medium">Validações de Role:</div>
              <div className="space-y-1 text-sm">
                <div className="flex justify-between">
                  <span>admin:</span>
                  <Badge variant={isAdmin ? 'default' : 'destructive'}>
                    {isAdmin ? '✓' : '✗'}
                  </Badge>
                </div>
                <div className="flex justify-between">
                  <span>super-admin:</span>
                  <Badge variant={isSuperAdminUser ? 'default' : 'destructive'}>
                    {isSuperAdminUser ? '✓' : '✗'}
                  </Badge>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
        
        {/* PermissionButton Examples */}
        <Card>
          <CardHeader>
            <CardTitle>PermissionButton</CardTitle>
            <CardDescription>
              Botões com validação automática de permissões
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <div className="text-sm font-medium">Botões Básicos:</div>
              <div className="flex flex-wrap gap-2">
                <PermissionButton
                  permission="users.edit"
                  variant="outline"
                  size="sm"
                  onClick={() => alert('Editando usuário!')}
                >
                  <Edit className="w-4 h-4 mr-2" />
                  Editar
                </PermissionButton>
                
                <PermissionButton
                  permission="users.delete"
                  variant="destructive"
                  size="sm"
                  requireConfirmation
                  confirmTitle="Excluir Usuário"
                  confirmDescription="Esta ação não pode ser desfeita."
                  onClick={() => alert('Usuário excluído!')}
                >
                  <Trash2 className="w-4 h-4 mr-2" />
                  Excluir
                </PermissionButton>
                
                <PermissionButton
                  permission="users.view"
                  variant="ghost"
                  size="sm"
                  route="/users/1"
                >
                  <Eye className="w-4 h-4 mr-2" />
                  Ver
                </PermissionButton>
              </div>
            </div>
            
            <Separator />
            
            <div className="space-y-2">
              <div className="text-sm font-medium">Botões Pré-configurados:</div>
              <div className="flex flex-wrap gap-2">
                <EditButton onClick={() => alert('Edit button!')}>
                  <Edit className="w-4 h-4 mr-2" />
                  Editar
                </EditButton>
                
                <DeleteButton onClick={() => alert('Delete button!')}>
                  <Trash2 className="w-4 h-4 mr-2" />
                  Excluir
                </DeleteButton>
                
                <ViewButton route="/users/1">
                  <Eye className="w-4 h-4 mr-2" />
                  Visualizar
                </ViewButton>
                
                <CreateButton route="/users/create">
                  <Plus className="w-4 h-4 mr-2" />
                  Criar
                </CreateButton>
              </div>
            </div>
            
            <Separator />
            
            <div className="space-y-2">
              <div className="text-sm font-medium">Comportamentos de Fallback:</div>
              <div className="space-y-2">
                <div className="flex gap-2">
                  <PermissionButton
                    permission="nonexistent.permission"
                    fallbackBehavior="hide"
                    variant="outline"
                    size="sm"
                  >
                    Oculto (hide)
                  </PermissionButton>
                  
                  <PermissionButton
                    permission="nonexistent.permission"
                    fallbackBehavior="disable"
                    variant="outline"
                    size="sm"
                    disabledReason="Você não tem esta permissão específica"
                  >
                    Desabilitado (disable)
                  </PermissionButton>
                  
                  <PermissionButton
                    permission="nonexistent.permission"
                    fallbackBehavior="show"
                    variant="outline"
                    size="sm"
                  >
                    Visível (show)
                  </PermissionButton>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
        
        {/* PermissionLink Examples */}
        <Card>
          <CardHeader>
            <CardTitle>PermissionLink</CardTitle>
            <CardDescription>
              Links com validação automática de permissões
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-2">
              <div className="text-sm font-medium">Links Básicos:</div>
              <div className="space-y-2">
                <PermissionLink
                  permission="users.edit"
                  href="/users/1/edit"
                  className="text-blue-600 hover:text-blue-800 dark:text-blue-400"
                >
                  <Edit className="w-4 h-4 inline mr-2" />
                  Editar Usuário
                </PermissionLink>
                
                <PermissionLink
                  permission="users.view"
                  href="/users/1"
                  className="text-gray-600 hover:text-gray-800 dark:text-gray-400"
                >
                  <Eye className="w-4 h-4 inline mr-2" />
                  Ver Detalhes
                </PermissionLink>
                
                <PermissionLink
                  permission="users.create"
                  href="/users/create"
                  className="text-green-600 hover:text-green-800 dark:text-green-400"
                >
                  <Plus className="w-4 h-4 inline mr-2" />
                  Criar Novo Usuário
                </PermissionLink>
              </div>
            </div>
            
            <Separator />
            
            <div className="space-y-2">
              <div className="text-sm font-medium">Links Pré-configurados:</div>
              <div className="space-y-2">
                <EditLink href="/users/1/edit">
                  <Edit className="w-4 h-4 inline mr-2" />
                  Link de Edição
                </EditLink>
                
                <ViewLink href="/users/1">
                  <Eye className="w-4 h-4 inline mr-2" />
                  Link de Visualização
                </ViewLink>
                
                <CreateLink href="/users/create">
                  <Plus className="w-4 h-4 inline mr-2" />
                  Link de Criação
                </CreateLink>
              </div>
            </div>
            
            <Separator />
            
            <div className="space-y-2">
              <div className="text-sm font-medium">Links Especializados:</div>
              <div className="space-y-2">
                <PermissionNavLink
                  permission="dashboard.view"
                  href="/dashboard"
                >
                  Dashboard
                </PermissionNavLink>
                
                <PermissionSidebarLink
                  permission="users.index"
                  href="/users"
                >
                  <Users className="w-4 h-4" />
                  Usuários
                </PermissionSidebarLink>
                
                <PermissionSidebarLink
                  permission="settings.view"
                  href="/settings"
                >
                  <Settings className="w-4 h-4" />
                  Configurações
                </PermissionSidebarLink>
              </div>
            </div>
          </CardContent>
        </Card>
        
        {/* Validação Condicional */}
        <Card>
          <CardHeader>
            <CardTitle>Renderização Condicional</CardTitle>
            <CardDescription>
              Exemplos de renderização baseada em permissões
            </CardDescription>
          </CardHeader>
          <CardContent className="space-y-4">
            <div className="space-y-3">
              {/* Renderização condicional simples */}
              {hasPermission('users.edit') && (
                <div className="p-3 bg-blue-50 dark:bg-blue-950 rounded-lg">
                  <div className="text-sm font-medium text-blue-900 dark:text-blue-100">
                    ✓ Você pode editar usuários
                  </div>
                  <div className="text-xs text-blue-700 dark:text-blue-300">
                    Este conteúdo só aparece se você tiver a permissão 'users.edit'
                  </div>
                </div>
              )}
              
              {/* Renderização condicional com múltiplas permissões */}
              {hasAnyPermission(['users.edit', 'users.delete']) && (
                <div className="p-3 bg-green-50 dark:bg-green-950 rounded-lg">
                  <div className="text-sm font-medium text-green-900 dark:text-green-100">
                    ✓ Você pode gerenciar usuários
                  </div>
                  <div className="text-xs text-green-700 dark:text-green-300">
                    Visível se tiver 'users.edit' OU 'users.delete'
                  </div>
                </div>
              )}
              
              {/* Renderização condicional com role */}
              {hasRole('admin') && (
                <div className="p-3 bg-purple-50 dark:bg-purple-950 rounded-lg">
                  <div className="text-sm font-medium text-purple-900 dark:text-purple-100">
                    ⭐ Área do Administrador
                  </div>
                  <div className="text-xs text-purple-700 dark:text-purple-300">
                    Conteúdo exclusivo para admins
                  </div>
                </div>
              )}
              
              {/* Renderização condicional negativa */}
              {cannot('users.delete') && (
                <div className="p-3 bg-yellow-50 dark:bg-yellow-950 rounded-lg">
                  <div className="text-sm font-medium text-yellow-900 dark:text-yellow-100">
                    ⚠️ Acesso Limitado
                  </div>
                  <div className="text-xs text-yellow-700 dark:text-yellow-300">
                    Você não pode excluir usuários
                  </div>
                </div>
              )}
              
              {/* Super Admin */}
              {isSuperAdmin && (
                <div className="p-3 bg-red-50 dark:bg-red-950 rounded-lg">
                  <div className="text-sm font-medium text-red-900 dark:text-red-100">
                    🔥 Super Administrador
                  </div>
                  <div className="text-xs text-red-700 dark:text-red-300">
                    Você tem acesso total ao sistema
                  </div>
                </div>
              )}
            </div>
          </CardContent>
        </Card>
        
        {/* Exemplo de Tabela com Ações */}
        <Card className="md:col-span-2 lg:col-span-3">
          <CardHeader>
            <CardTitle>Exemplo: Tabela com Ações Baseadas em Permissões</CardTitle>
            <CardDescription>
              Demonstração de como usar os componentes em uma tabela real
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="border rounded-lg overflow-hidden">
              <table className="w-full">
                <thead className="bg-gray-50 dark:bg-gray-800">
                  <tr>
                    <th className="px-4 py-3 text-left text-sm font-medium">Nome</th>
                    <th className="px-4 py-3 text-left text-sm font-medium">Email</th>
                    <th className="px-4 py-3 text-left text-sm font-medium">Status</th>
                    <th className="px-4 py-3 text-left text-sm font-medium">Ações</th>
                  </tr>
                </thead>
                <tbody className="divide-y divide-gray-200 dark:divide-gray-700">
                  <tr className="hover:bg-gray-50 dark:hover:bg-gray-800">
                    <td className="px-4 py-3">{sampleUser.name}</td>
                    <td className="px-4 py-3">{sampleUser.email}</td>
                    <td className="px-4 py-3">
                      <Badge variant="default">Ativo</Badge>
                    </td>
                    <td className="px-4 py-3">
                      <div className="flex gap-2">
                        <PermissionButton
                          permission="users.view"
                          variant="ghost"
                          size="sm"
                          route={`/users/${sampleUser.id}`}
                        >
                          <Eye className="w-4 h-4" />
                        </PermissionButton>
                        
                        <PermissionButton
                          permission="users.edit"
                          variant="outline"
                          size="sm"
                          route={`/users/${sampleUser.id}/edit`}
                        >
                          <Edit className="w-4 h-4" />
                        </PermissionButton>
                        
                        <PermissionButton
                          permission="users.delete"
                          variant="destructive"
                          size="sm"
                          requireConfirmation
                          confirmTitle="Excluir Usuário"
                          confirmDescription={`Tem certeza que deseja excluir ${sampleUser.name}?`}
                          onClick={() => alert(`Excluindo ${sampleUser.name}`)}
                        >
                          <Trash2 className="w-4 h-4" />
                        </PermissionButton>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
  )
}

export default PermissionsExample 
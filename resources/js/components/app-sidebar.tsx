import React from 'react'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { 
  Home, 
  Users, 
  Settings, 
  Database, 
  BarChart3, 
  FileText, 
  Shield,
  Sun,
  Moon,
  User,
  LogOut,
  X,
  TestTube
} from 'lucide-react'
import { usePermissions } from '../hooks/usePermissions' 
import { PermissionLink } from './PermissionLink'
import { router } from '@inertiajs/react'

interface AppSidebarProps {
  sidebarOpen: boolean
  setSidebarOpen: (open: boolean) => void
  sidebarVisible: boolean
  toggleDarkMode: () => void
}

/**
 * Componente da Sidebar do Papa Leguas
 * 
 * Inclui navegação com permissões, informações do usuário,
 * controles de tema e logout.
 */
export function AppSidebar({ 
  sidebarOpen, 
  setSidebarOpen, 
  sidebarVisible,
  toggleDarkMode
}: AppSidebarProps) {
  const { user, isAuthenticated } = usePermissions()
  
  // Itens de navegação com permissões
  const navigationItems = [
    {
      title: 'Dashboard',
      href: '/dashboard',
      icon: Home,
      permission: 'dashboard.view'
    },
    {
      title: 'CRUD',
      href: '/crud',
      icon: Database,
      permission: 'crud.view'
    },
    {
      title: 'Usuários',
      href: '/users',
      icon: Users,
      permission: 'users.view'
    },
    {
      title: 'Relatórios',
      href: '/reports',
      icon: BarChart3,
      permission: 'reports.view'
    },
    {
      title: 'Documentos',
      href: '/documents',
      icon: FileText,
      permission: 'documents.view'
    },
    {
      title: 'Permissões',
      href: '/permissions',
      icon: Shield,
      permission: 'permissions.view'
    },
    {
      title: 'Testes',
      href: '/tests',
      icon: TestTube,
      permission: 'tests.view'
    },
    {
      title: 'Configurações',
      href: '/settings',
      icon: Settings,
      permission: 'settings.view'
    }
  ]

  // Logout
  const handleLogout = () => {
    router.post('/logout')
  }

  if (!sidebarVisible) {
    return null
  }

  return (
    <div className={cn(
      "fixed inset-y-0 left-0 z-30 w-64 bg-background border-r transform transition-transform duration-200 ease-in-out lg:translate-x-0 flex flex-col",
      sidebarOpen ? "translate-x-0" : "-translate-x-full"
    )}>
      {/* Sidebar Header */}
      <div className="border-b p-4 flex-shrink-0">
        <div className="flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 bg-primary rounded-lg flex items-center justify-center">
              <span className="text-primary-foreground font-bold text-sm">PL</span>
            </div>
            <div>
              <h2 className="font-semibold text-sm">Papa Leguas</h2>
              <p className="text-xs text-muted-foreground">Sistema de Gestão</p>
            </div>
          </div>
          <Button
            variant="ghost"
            size="sm"
            onClick={() => setSidebarOpen(false)}
            className="lg:hidden"
          >
            <X className="w-4 h-4" />
          </Button>
        </div>
      </div>

      {/* Sidebar Content */}
      <div className="flex-1 overflow-y-auto">
        <nav className="p-2 space-y-1">
          {navigationItems.map((item) => (
            <PermissionLink
              key={item.href}
              permission={item.permission}
              href={item.href}
              className="flex items-center gap-3 rounded-lg px-3 py-2 text-sm transition-all hover:bg-accent hover:text-accent-foreground w-full"
              activeClassName="bg-accent text-accent-foreground"
              fallbackBehavior="hide"
            >
              <item.icon className="w-4 h-4" />
              {item.title}
            </PermissionLink>
          ))}
        </nav>
      </div>

      {/* Sidebar Footer */}
      <div className="border-t p-4 flex-shrink-0">
        {isAuthenticated && user && (
          <div className="space-y-3">
            {/* User Info */}
            <div className="flex items-center gap-3 p-2 rounded-lg bg-accent/50">
              <div className="w-8 h-8 bg-primary rounded-full flex items-center justify-center">
                <User className="w-4 h-4 text-primary-foreground" />
              </div>
              <div className="flex-1 min-w-0">
                <p className="text-sm font-medium truncate">{user.name}</p>
                <p className="text-xs text-muted-foreground truncate">{user.email}</p>
              </div>
            </div>

            {/* Actions */}
            <div className="flex gap-2">
              <Button
                variant="outline"
                size="sm"
                onClick={toggleDarkMode}
                className="flex-1"
              >
                <Sun className="w-4 h-4 dark:hidden" />
                <Moon className="w-4 h-4 hidden dark:block" />
              </Button>
              
              <Button
                variant="outline"
                size="sm"
                onClick={handleLogout}
                className="flex-1"
              >
                <LogOut className="w-4 h-4" />
              </Button>
            </div>
          </div>
        )}
      </div>
    </div>
  )
} 
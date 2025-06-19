import React, { useState } from 'react'
import { type BreadcrumbItem } from '../types'
import { type ReactNode } from 'react'
import { usePage } from '@inertiajs/react'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { 
  Breadcrumb, 
  BreadcrumbItem as BreadcrumbItemComponent, 
  BreadcrumbLink, 
  BreadcrumbList, 
  BreadcrumbPage, 
  BreadcrumbSeparator 
} from '@/components/ui/breadcrumb'
import { Separator } from '@/components/ui/separator'
import { 
  Home, 
  Users, 
  Settings, 
  Database, 
  BarChart3, 
  FileText, 
  Shield,
  Menu,
  Sun,
  Moon,
  User,
  LogOut,
  X,
  TestTube
} from 'lucide-react'
import { usePermissions } from '../components/table-old/hooks/usePermissions'
import { PermissionLink } from '../components/table-old/components/PermissionLink'
import { router } from '@inertiajs/react'

interface AppLayoutProps {
  children: ReactNode
  breadcrumbs?: BreadcrumbItem[]
  title?: string
  className?: string
}

/**
 * Layout principal do Papa Leguas
 * 
 * Inclui sidebar com navegação, header com breadcrumbs,
 * sistema de permissões integrado e suporte ao dark mode.
 */
export default function AppLayout({ 
  children, 
  breadcrumbs = [], 
  title,
  className 
}: AppLayoutProps) {
  const { props } = usePage()
  const { user, isAuthenticated, hasPermission } = usePermissions()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  
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

  // Toggle dark mode
  const toggleDarkMode = () => {
    const html = document.documentElement
    const isDark = html.classList.contains('dark')
    
    if (isDark) {
      html.classList.remove('dark')
      localStorage.setItem('theme', 'light')
    } else {
      html.classList.add('dark')
      localStorage.setItem('theme', 'dark')
    }
  }

  // Logout
  const handleLogout = () => {
    router.post('/logout')
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Sidebar */}
      <div className={cn(
        "fixed inset-y-0 left-0 z-30 w-64 bg-background border-r transform transition-transform duration-200 ease-in-out lg:translate-x-0",
        sidebarOpen ? "translate-x-0" : "-translate-x-full"
      )}>
        {/* Sidebar Header */}
        <div className="border-b p-4">
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
        <div className="p-2 flex-1">
          <nav className="space-y-1">
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
        <div className="border-t p-4">
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

      {/* Overlay para mobile */}
      {sidebarOpen && (
        <div 
          className="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Main Content */}
      <div className="min-h-screen flex flex-col lg:ml-64">
        {/* Header */}
        <header className="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 relative z-0">
          <div className="flex h-14 items-center gap-4 px-4">
            <Button
              variant="ghost"
              size="sm"
              onClick={() => setSidebarOpen(true)}
              className="lg:hidden"
            >
              <Menu className="w-4 h-4" />
            </Button>

            {/* Breadcrumbs */}
            {breadcrumbs.length > 0 && (
              <>
                <Separator orientation="vertical" className="h-6 lg:hidden" />
                <Breadcrumb>
                  <BreadcrumbList>
                    {breadcrumbs.map((item, index) => (
                      <React.Fragment key={index}>
                        <BreadcrumbItemComponent>
                          {item.href ? (
                            <BreadcrumbLink href={item.href}>
                              {item.title}
                            </BreadcrumbLink>
                          ) : (
                            <BreadcrumbPage>{item.title}</BreadcrumbPage>
                          )}
                        </BreadcrumbItemComponent>
                        {index < breadcrumbs.length - 1 && <BreadcrumbSeparator />}
                      </React.Fragment>
                    ))}
                  </BreadcrumbList>
                </Breadcrumb>
              </>
            )}

            {/* Title */}
            {title && (
              <>
                <Separator orientation="vertical" className="h-6" />
                <h1 className="text-lg font-semibold">{title}</h1>
              </>
            )}

            {/* Spacer */}
            <div className="flex-1" />

            {/* Header Actions */}
            <div className="flex items-center gap-2">
              <Button
                variant="ghost"
                size="sm"
                onClick={toggleDarkMode}
                className="hidden lg:flex"
              >
                <Sun className="w-4 h-4 dark:hidden" />
                <Moon className="w-4 h-4 hidden dark:block" />
                <span className="sr-only">Toggle theme</span>
              </Button>

              {isAuthenticated && user && (
                <div className="hidden lg:flex items-center gap-2 text-sm text-muted-foreground">
                  <User className="w-4 h-4" />
                  <span>{user.name}</span>
                </div>
              )}
            </div>
          </div>
        </header>

        {/* Page Content */}
        <main className={cn(
          "flex-1 overflow-auto relative z-0",
          className
        )}>
          <div className="container mx-auto p-4 space-y-4">
            {children}
          </div>
        </main>
      </div>
    </div>
  )
}

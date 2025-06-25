import React from 'react'
import { type BreadcrumbItem } from '../types'
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
  Menu,
  Sun,
  Moon,
  User,
  PanelLeft,
  PanelLeftClose
} from 'lucide-react'
import { usePermissions } from '../hooks/usePermissions' 

interface AppHeaderProps {
  breadcrumbs?: BreadcrumbItem[]
  title?: string
  sidebarOpen: boolean
  setSidebarOpen: (open: boolean) => void
  sidebarVisible: boolean
  toggleSidebar: () => void
  toggleDarkMode: () => void
}

/**
 * Componente do Header do Papa Leguas
 * 
 * Inclui breadcrumbs, título, controles da sidebar,
 * toggle de tema e informações do usuário.
 */
export function AppHeader({ 
  breadcrumbs = [],
  title,
  sidebarOpen,
  setSidebarOpen,
  sidebarVisible,
  toggleSidebar,
  toggleDarkMode
}: AppHeaderProps) {
  const { user, isAuthenticated } = usePermissions()

  return (
    <header className="border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60 relative z-0">
      <div className="flex h-14 items-center gap-4 px-4">
        {/* Menu Mobile */}
        <Button
          variant="ghost"
          size="sm"
          onClick={() => setSidebarOpen(true)}
          className="lg:hidden"
        >
          <Menu className="w-4 h-4" />
        </Button>

        {/* Sidebar Toggle para Desktop */}
        <Button
          variant="ghost"
          size="sm"
          onClick={toggleSidebar}
          className="hidden lg:flex"
        >
          {sidebarVisible ? (
            <PanelLeftClose className="w-4 h-4" />
          ) : (
            <PanelLeft className="w-4 h-4" />
          )}
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
          {/* Dark Mode Toggle */}
          <Button
            variant="ghost"
            size="sm"
            onClick={toggleDarkMode}
            className="hidden lg:flex"
          >
            <Sun className="w-4 h-4 dark:hidden" />
            <Moon className="w-4 h-4 hidden dark:block" />
            <span className="sr-only">Alternar tema</span>
          </Button>

          {/* User Info */}
          {isAuthenticated && user && (
            <div className="hidden lg:flex items-center gap-2 text-sm text-muted-foreground">
              <User className="w-4 h-4" />
              <span>{user.name}</span>
            </div>
          )}
        </div>
      </div>
    </header>
  )
} 
import React, { useState } from 'react'
import { type BreadcrumbItem } from '../types'
import { type ReactNode } from 'react'
import { usePage } from '@inertiajs/react'
import { cn } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { usePermissions } from '../hooks/usePermissions'
import { AppSidebar } from '../components/app-sidebar'
import { AppHeader } from '../components/app-header'
import { Toaster } from '../components/ui/toaster'
import { LoadingOverlay } from '../components/ui/loading-overlay'
import { useGlobalLoading } from '../hooks/use-global-loading'
import { router } from '@inertiajs/react'

interface AppLayoutProps {
  children: ReactNode
  breadcrumbs?: BreadcrumbItem[]
  title?: string
  className?: string
  fullWidth?: boolean
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
  className,
  fullWidth = false
}: AppLayoutProps) {
  const { props } = usePage()
  const { user, isAuthenticated, hasPermission } = usePermissions()
  const [sidebarOpen, setSidebarOpen] = useState(false)
  const [sidebarVisible, setSidebarVisible] = useState(true)
  const { isLoading: globalLoading, message: loadingMessage } = useGlobalLoading()

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

  // Toggle sidebar visibility
  const toggleSidebar = () => {
    setSidebarVisible(!sidebarVisible)
  }

  return (
    <div className="min-h-screen bg-background">
      {/* Sidebar */}
      <AppSidebar
        sidebarOpen={sidebarOpen}
        setSidebarOpen={setSidebarOpen}
        sidebarVisible={sidebarVisible}
        toggleDarkMode={toggleDarkMode}
      />

      {/* Overlay para mobile */}
      {sidebarOpen && (
        <div
          className="fixed inset-0 bg-black bg-opacity-50 z-20 lg:hidden"
          onClick={() => setSidebarOpen(false)}
        />
      )}

      {/* Main Content */}
      <div className={cn(
        "min-h-screen flex flex-col transition-all duration-200 ease-in-out",
        (sidebarVisible ? "lg:ml-64" : "lg:ml-0")
      )}>
        {/* Header */}
        <AppHeader
          breadcrumbs={breadcrumbs}
          title={title}
          sidebarOpen={sidebarOpen}
          setSidebarOpen={setSidebarOpen}
          sidebarVisible={sidebarVisible}
          toggleSidebar={toggleSidebar}
          toggleDarkMode={toggleDarkMode}
        />

        {/* Page Content */}
        <main className={cn(
          "flex-1 overflow-auto relative z-0",
          className
        )}>
          <div className={cn(
            " mx-auto p-4 space-y-4",
            fullWidth ? "w-full" : "container"
          )}>
            {children}
          </div>
        </main>
      </div>

      {/* Toast/Notifications Provider */}
      <Toaster />

      {/* Loading Overlay Global */}
      <LoadingOverlay
        isVisible={globalLoading}
        message={loadingMessage}
      />
    </div>
  )
}

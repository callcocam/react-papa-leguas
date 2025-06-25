<?php

namespace Callcocam\ReactPapaLeguas\Navigation;

use App\Navigation\AdminNavigation as NavigationAdminNavigation;

/**
 * AdminNavigation - Configuração de Navegação da Área Administrativa
 * 
 * Define a estrutura de navegação para a área administrativa
 * com permissões e sub menus organizados.
 */
class AdminNavigation
{
    /**
     * Construir navegação administrativa
     */
    public static function build(bool $validatePermissions = true): array
    {
        $navigation = app(config('react-papa-leguas.navigation.navigation_builder'));
        return $navigation

            // Dashboard
            ->item('dashboard')
            ->label('Dashboard')
            ->route('dashboard')
            ->icon('Home')
            ->permission('dashboard.view')
            ->order(1)
            ->build($validatePermissions);
    }
}

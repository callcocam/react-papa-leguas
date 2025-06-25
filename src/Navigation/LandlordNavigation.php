<?php

/**
 * LandlordNavigation - Configuração de Navegação para Landlords
 * 
 * Define a estrutura de navegação para Landlords
 * com permissões e sub menus organizados.
 */

namespace Callcocam\ReactPapaLeguas\Navigation;


class LandlordNavigation
{
    public static function build(bool $validatePermissions = true): array
    {
        $navigation = app(config('react-papa-leguas.navigation.navigation_builder'));
        $navigation
            // Dashboard
            ->item('dashboard')
            ->label('Dashboard')
            ->route('dashboard')
            ->icon('Home')
            ->order(1)

            // Inquilinos
            ->item('tenants')
            ->label('Inquilinos')
            ->icon('Building')
            ->order(2)
            ->submenu(function ($menu) {
                $menu->subitem('tenants-list')
                    ->label('Lista de Inquilinos')
                    ->route('landlord.tenants.index')
                    ->icon('Users')
                    ->permission('tenants.view')
                    ->order(1);

                $menu->subitem('tenants-create')
                    ->label('Novo Inquilino')
                    ->route('landlord.tenants.create')
                    ->icon('UserPlus')
                    ->permission('tenants.create')
                    ->order(2);
            })

            // Propriedades
            ->item('properties')
            ->label('Propriedades')
            ->icon('Home')
            ->order(3)
            ->submenu(function ($menu) {
                $menu->subitem('properties-list')
                    ->label('Minhas Propriedades')
                    ->route('landlord.properties.index')
                    ->icon('Building2')
                    ->permission('properties.view')
                    ->order(1);

                $menu->subitem('properties-create')
                    ->label('Nova Propriedade')
                    ->route('landlord.properties.create')
                    ->icon('Plus')
                    ->permission('properties.create')
                    ->order(2);
            })

            // Contratos
            ->item('contracts')
            ->label('Contratos')
            ->route('landlord.contracts.index')
            ->icon('FileText')
            ->permission('contracts.view')
            ->order(4)

            // Financeiro
            ->item('financial')
            ->label('Financeiro')
            ->icon('DollarSign')
            ->order(5)
            ->submenu(function ($menu) {
                $menu->subitem('income')
                    ->label('Recebimentos')
                    ->route('landlord.financial.income')
                    ->icon('TrendingUp')
                    ->permission('financial.view')
                    ->order(1);

                $menu->subitem('expenses')
                    ->label('Despesas')
                    ->route('landlord.financial.expenses')
                    ->icon('TrendingDown')
                    ->permission('financial.view')
                    ->order(2);
            })

            // Relatórios
            ->item('reports')
            ->label('Relatórios')
            ->icon('BarChart3')
            ->order(6)
            ->submenu(function ($menu) {
                $menu->subitem('occupancy')
                    ->label('Ocupação')
                    ->route('landlord.reports.occupancy')
                    ->icon('PieChart')
                    ->permission('reports.view')
                    ->order(1);

                $menu->subitem('revenue')
                    ->label('Receita')
                    ->route('landlord.reports.revenue')
                    ->icon('DollarSign')
                    ->permission('reports.view')
                    ->order(2);
            });

        return $navigation->build($validatePermissions);
    }
}

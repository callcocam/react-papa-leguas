<?php

namespace Callcocam\ReactPapaLeguas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Callcocam\ReactPapaLeguas\Navigation\AdminNavigation;

/**
 * NavigationController - Fornece navegação dinâmica com permissões
 * 
 * Retorna estrutura de navegação filtrada pelas permissões
 * do usuário logado.
 */
class NavigationController extends Controller
{
    /**
     * Retornar navegação administrativa
     */
    public function admin(Request $request): JsonResponse
    {
        $navigation = AdminNavigation::build();
        
        return response()->json([
            'navigation' => $navigation,
            'meta' => [
                'total_items' => count($navigation),
                'user' => $request->user()?->only(['id', 'name', 'email']),
                'generated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Retornar navegação do landlord (exemplo)
     */
    public function landlord(Request $request): JsonResponse
    {
        // Aqui você pode criar LandlordNavigation::build()
        $navigation = [
            [
                'key' => 'dashboard',
                'title' => 'Dashboard',
                'href' => route('landlord.dashboard'),
                'icon' => 'Home',
                'permission' => 'landlord.dashboard.view',
                'order' => 1
            ],
            [
                'key' => 'tenants',
                'title' => 'Inquilinos',
                'icon' => 'Building',
                'order' => 2,
                'subitems' => [
                    [
                        'key' => 'tenants-list',
                        'title' => 'Lista de Inquilinos',
                        'href' => route('landlord.tenants.index'),
                        'icon' => 'Users',
                        'permission' => 'tenants.view',
                        'order' => 1
                    ],
                    [
                        'key' => 'tenants-create',
                        'title' => 'Novo Inquilino',
                        'href' => route('landlord.tenants.create'),
                        'icon' => 'UserPlus',
                        'permission' => 'tenants.create',
                        'order' => 2
                    ]
                ]
            ]
        ];
        
        return response()->json([
            'navigation' => $navigation,
            'meta' => [
                'total_items' => count($navigation),
                'user' => $request->user()?->only(['id', 'name', 'email']),
                'generated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Retornar navegação baseada no contexto do usuário
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['navigation' => []], 401);
        }

        // Determinar tipo de navegação baseado no usuário
        // Aqui você pode implementar lógica para determinar
        // se é admin, landlord, tenant, etc.
        
        $navigation = AdminNavigation::build();
        
        return response()->json([
            'navigation' => $navigation,
            'meta' => [
                'total_items' => count($navigation),
                'user_type' => $this->getUserType($user),
                'user' => $user->only(['id', 'name', 'email']),
                'generated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Determinar tipo de usuário
     */
    protected function getUserType($user): string
    {
        // Implementar lógica para determinar tipo de usuário
        // Por exemplo, verificar roles, tabela específica, etc.
        
        if (method_exists($user, 'hasRole')) {
            if ($user->hasRole('admin')) {
                return 'admin';
            }
            
            if ($user->hasRole('landlord')) {
                return 'landlord';
            }
            
            if ($user->hasRole('tenant')) {
                return 'tenant';
            }
        }
        
        return 'user';
    }
} 
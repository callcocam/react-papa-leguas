<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Callcocam\ReactPapaLeguas\Http\Controllers\Controller;
use Callcocam\ReactPapaLeguas\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/**
 * Class LandlordController
 * @package Callcocam\ReactPapaLeguas\Http\Controllers\Landlord
 */
class DashboardController extends Controller
{
    /**
     * Display the landlord dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return Inertia::render('landlord/dashboard', $this->getViewData());
    }

    /**
     * Get view data for the landlord dashboard.
     *
     * @return array
     */
    protected function getViewData()
    {
        $landlord = Auth::guard('landlord')->user();
        return [
            'title' => 'Landlord Dashboard',
            'description' => 'Welcome to the Landlord Dashboard',
            'auth' => [
                'landlord' => $landlord
            ],
            'stats' => $this->getDashboardStats(),
        ];
    }


    /**
     * Get dashboard statistics.
     */
    protected function getDashboardStats(): array
    {
        try {
            $totalTenants = app(config('tenant.models.tenant', Tenant::class))->count();

            // Try to get user count across all tenants
            $totalUsers = 0;
            try {
                $userModel = config('auth.providers.users.model', \App\Models\User::class);
                $totalUsers = app($userModel)->count();
            } catch (\Exception $e) {
                // If user table doesn't exist or has issues, default to 0
                $totalUsers = 0;
            }

            return [
                'totalTenants' => $totalTenants,
                'totalUsers' => $totalUsers,
                'activeConnections' => 0, // This could be implemented with session tracking
            ];
        } catch (\Exception $e) {
            // If any database issues, return safe defaults
            return [
                'totalTenants' => 0,
                'totalUsers' => 0,
                'activeConnections' => 0,
            ];
        }
    }
}

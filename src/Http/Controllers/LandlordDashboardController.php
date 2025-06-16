<?php

namespace Callcocam\ReactPapaLeguas\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LandlordDashboardController extends Controller
{
    /**
     * Show the landlord dashboard.
     */
    public function index(): Response
    {
        $landlord = Auth::guard('landlord')->user();

        return Inertia::render('Landlord/Dashboard', [
            'landlord' => $landlord,
            'stats' => $this->getDashboardStats($landlord),
        ]);
    }

    /**
     * Get dashboard statistics for the landlord.
     */
    protected function getDashboardStats($landlord): array
    {
        // Aqui você pode implementar estatísticas específicas do landlord
        return [
            'total_properties' => 0,
            'active_leases' => 0,
            'monthly_revenue' => 0,
            'pending_maintenance' => 0,
        ];
    }
}

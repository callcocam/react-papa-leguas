<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Callcocam\ReactPapaLeguas\Http\Controllers\Controller;
use Callcocam\ReactPapaLeguas\Models\Tenant;
use Callcocam\ReactPapaLeguas\Enums\TenantStatus;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TenantController extends Controller
{
    /**
     * Display a listing of tenants.
     */
    public function index(Request $request): Response
    {
        $query = Tenant::query()
            ->with(['users', 'addresses']) ;

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('document', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        // Sort
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        if (in_array($sortField, ['name', 'email', 'created_at', 'status'])) {
            $query->orderBy($sortField, $sortDirection);
        }

        $tenants = $query->paginate($request->get('per_page', 15))
            ->withQueryString();

        return Inertia::render('landlord/tenants/index', [
            'tenants' => $tenants,
            'filters' => $request->only(['search', 'status', 'sort', 'direction']),
            'stats' => [
                'total' => Tenant::count(),
                'active' => Tenant::where('status', TenantStatus::Published)->count(),
                'draft' => Tenant::where('status', TenantStatus::Draft)->count(),
            ],
            'status_options' => collect(TenantStatus::cases())->map(fn($status) => [
                'value' => $status->value,
                'label' => $status->label(),
                'color' => $status->color(),
            ]),
        ]);
    }

    /**
     * Show the form for creating a new tenant.
     */
    public function create(): Response
    {
        return Inertia::render('landlord/tenants/create', [
            'status_options' => collect(TenantStatus::cases())->map(fn($status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
        ]);
    }

    /**
     * Store a newly created tenant.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:tenants,email'],
            'document' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'domain' => ['nullable', 'url', 'max:255'],
            'status' => ['required', Rule::enum(TenantStatus::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'settings' => ['nullable', 'array'],
            'is_primary' => ['boolean'],
            
            // Address fields
            'address' => ['nullable', 'array'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.number' => ['nullable', 'string', 'max:20'],
            'address.complement' => ['nullable', 'string', 'max:100'],
            'address.neighborhood' => ['nullable', 'string', 'max:100'],
            'address.city' => ['nullable', 'string', 'max:100'],
            'address.state' => ['nullable', 'string', 'max:2'],
            'address.zip_code' => ['nullable', 'string', 'max:10'],
            'address.country' => ['nullable', 'string', 'max:2'],
        ]);

        $tenant = new Tenant();
        $tenant->fill($validated);
        $tenant->user_id = auth('landlord')->id();
        $tenant->save();

        // Create address if provided
        if ($request->has('address') && !empty(array_filter($validated['address']))) {
            $tenant->addresses()->create([
                ...$validated['address'],
                'is_default' => true,
                'type' => 'commercial',
            ]);
        }

        return redirect()
            ->route('landlord.tenants.index')
            ->with('success', 'Tenant criado com sucesso!');
    }

    /**
     * Display the specified tenant.
     */
    public function show(Tenant $tenant): Response
    {
        $tenant->load([
            'user',
            'addresses',
            'users' => fn($query) => $query->limit(10),
            'roles' => fn($query) => $query->limit(10),
        ]);

        return Inertia::render('landlord/tenants/show', [
            'tenant' => $tenant,
            'stats' => [
                'users_count' => $tenant->users()->count(),
                'roles_count' => $tenant->roles()->count(),
                'addresses_count' => $tenant->addresses()->count(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified tenant.
     */
    public function edit(Tenant $tenant): Response
    {
        $tenant->load(['addresses']);

        return Inertia::render('landlord/tenants/edit', [
            'tenant' => $tenant,
            'status_options' => collect(TenantStatus::cases())->map(fn($status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ]),
        ]);
    }

    /**
     * Update the specified tenant.
     */
    public function update(Request $request, Tenant $tenant): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('tenants', 'email')->ignore($tenant->id)],
            'document' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:20'],
            'domain' => ['nullable', 'string', 'max:255'],
            'status' => ['required', Rule::enum(TenantStatus::class)],
            'description' => ['nullable', 'string', 'max:1000'],
            'settings' => ['nullable', 'array'],
            'is_primary' => ['boolean'],
            
            // Address fields
            'address' => ['nullable', 'array'],
            'address.street' => ['nullable', 'string', 'max:255'],
            'address.number' => ['nullable', 'string', 'max:20'],
            'address.complement' => ['nullable', 'string', 'max:100'],
            'address.neighborhood' => ['nullable', 'string', 'max:100'],
            'address.city' => ['nullable', 'string', 'max:100'],
            'address.state' => ['nullable', 'string', 'max:2'],
            'address.zip_code' => ['nullable', 'string', 'max:10'],
            'address.country' => ['nullable', 'string', 'max:2'],
        ]);

        $tenant->fill($validated);
        $tenant->save();

        // Update or create default address
        if ($request->has('address') && !empty(array_filter($validated['address']))) {
            $defaultAddress = $tenant->defaultAddress;
            
            if ($defaultAddress) {
                $defaultAddress->update($validated['address']);
            } else {
                $tenant->addresses()->create([
                    ...$validated['address'],
                    'is_default' => true,
                    'type' => 'commercial',
                ]);
            }
        }

        return redirect()
            ->route('landlord.tenants.index')
            ->with('success', 'Tenant atualizado com sucesso!');
    }

    /**
     * Remove the specified tenant.
     */
    public function destroy(Tenant $tenant): RedirectResponse
    {
        // Check if tenant has active users
        if ($tenant->users()->exists()) {
            return redirect()
                ->back()
                ->with('error', 'Não é possível excluir um tenant que possui usuários ativos.');
        }

        $tenant->delete();

        return redirect()
            ->route('landlord.tenants.index')
            ->with('success', 'Tenant excluído com sucesso!');
    }

    /**
     * Bulk delete selected tenants.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['exists:tenants,id'],
        ]);

        $tenantsWithUsers = Tenant::whereIn('id', $request->ids)
            ->whereHas('users')
            ->count();

        if ($tenantsWithUsers > 0) {
            return redirect()
                ->back()
                ->with('error', "Não é possível excluir {$tenantsWithUsers} tenant(s) que possuem usuários ativos.");
        }

        $deletedCount = Tenant::whereIn('id', $request->ids)->delete();

        return redirect()
            ->route('landlord.tenants.index')
            ->with('success', "{$deletedCount} tenant(s) excluído(s) com sucesso!");
    }

    /**
     * Toggle tenant status.
     */
    public function toggleStatus(Tenant $tenant): RedirectResponse
    {
        $newStatus = $tenant->status === TenantStatus::Published 
            ? TenantStatus::Draft 
            : TenantStatus::Published;

        $tenant->update(['status' => $newStatus]);

        return redirect()
            ->back()
            ->with('success', "Status do tenant alterado para {$newStatus->label()}!");
    }

    /**
     * Export tenants data.
     */
    public function export(Request $request)
    {
        $request->validate([
            'format' => ['required', 'in:csv,xlsx'],
            'ids' => ['nullable', 'array'],
            'ids.*' => ['exists:tenants,id'],
        ]);

        // TODO: Implement export functionality
        // This would typically use Laravel Excel or similar package
        
        return redirect()
            ->back()
            ->with('info', 'Funcionalidade de exportação será implementada em breve.');
    }
}
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToModel;
use Callcocam\ReactPapaLeguas\Http\Controllers\Controller;
use Callcocam\ReactPapaLeguas\Support\Concerns\ResolvesModel;
use Callcocam\ReactPapaLeguas\Support\Concerns\ModelQueries;
use Callcocam\ReactPapaLeguas\Support\Concerns\HasPermissionChecks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

/**
 * Class LandlordController
 * @package Callcocam\ReactPapaLeguas\Http\Controllers\Landlord
 */
class LandlordController extends Controller
{
    use BelongsToModel, ResolvesModel, ModelQueries, HasPermissionChecks;

    /**
     * Display the landlord dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Verificar permissão de visualização (opcional - não quebra se não configurado)
        if ($this->shouldCheckPermissions()) {
            $this->authorizePermission('viewAny', $this->getModelClass());
        }

        if ($request->has('debug')) {
            return Inertia::render('crud/debug', $this->getDataForViewsIndex($request));
        }
        return Inertia::render($this->getViewIndex(), $this->getDataForViewsIndex($request));
    }

    public function test(Request $request)
    {
        return Inertia::render('crud/debug', $this->getDataForViews($request));
    }

    protected function getDataForViewsIndex(Request $request)
    {
        $table = $this->getTable();
        $data = $table->toArray(); 
        return array_merge($this->getDataForViews($request), $data);
    }

    /**
     * Determina se as verificações de permissão devem ser executadas
     * 
     * @return bool
     */
    protected function shouldCheckPermissions(): bool
    {
        // Verificar se o sistema de permissões está habilitado
        $permissionsConfig = config('react-papa-leguas.permissions', []);
        
        // Se não há configuração, não verificar (manter compatibilidade)
        if (empty($permissionsConfig)) {
            return false;
        }
        
        // Verificar se há usuário autenticado
        if (!auth()->check()) {
            return false;
        }
        
        // Verificar se há modelo configurado para este controller
        if (!$this->getModelClass()) {
            return false;
        }
        
        // Por padrão, verificar permissões se tudo estiver configurado
        return true;
    }

    /**
     * Obtém dados de permissões para o frontend (opcional)
     * 
     * @return array
     */
    protected function getPermissionsData(): array
    {
        if (!$this->shouldCheckPermissions()) {
            return [];
        }

        $modelClass = $this->getModelClass();
        if (!$modelClass) {
            return [];
        }

        return [
            'can_view_any' => $this->checkPermission('viewAny', $modelClass),
            'can_create' => $this->checkPermission('create', $modelClass),
            'permissions_system_enabled' => true,
            'model_class' => class_basename($modelClass),
        ];
    }
}

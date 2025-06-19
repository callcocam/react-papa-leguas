<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use App\Models\User;
use App\Tables\UserTable;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToRoutes;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends LandlordController
{
    use BelongsToRoutes;
    
    public function __construct()
    {
         
        // Configurar prefixo de rotas personalizado
        $this->setRoutePrefix('landlord.users');
    }
    /**
     * Exibir listagem de usuários
     */
    public function index(Request $request): Response
    {
        try {
            // Criar instância da tabela Papa Leguas
            $table = new UserTable();
            
            // Obter dados da tabela
            $tableData = $table->toArray();
            
            // Adicionar informações de rotas do controller
            $tableData['controller_routes'] = [
                'index' => $this->getRouteNameIndex(),
                'create' => $this->getRouteNameCreate(),
                'export' => $this->getRouteNameExport(),
            ];
            
            return Inertia::render('crud/index', $tableData);
            
        } catch (\Exception $e) {
            // Log do erro
            Log::error('Erro ao carregar UserTable: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all(),
            ]);
            
            // Retornar página com erro
            return Inertia::render('crud/index', [
                'table' => [
                    'data' => [],
                    'columns' => [],
                    'filters' => [],
                    'actions' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'per_page' => 15,
                        'total' => 0,
                        'last_page' => 1,
                    ],
                    'meta' => [
                        'title' => 'Usuários',
                        'description' => 'Lista de usuários',
                        'searchable' => false,
                        'sortable' => false,
                        'filterable' => false,
                    ]
                ],
                'config' => [
                    'model_name' => 'User',
                    'page_title' => 'Usuários Landlord',
                    'page_description' => 'Erro ao carregar dados',
                    'route_prefix' => 'landlord.users',
                    'can_create' => false,
                    'can_edit' => false,
                    'can_delete' => false,
                    'can_export' => false,
                    'can_bulk_delete' => false,
                ],
                'routes' => [],
                'error' => 'Erro ao carregar dados da tabela. Verifique os logs.'
            ]);
        }
    }
}
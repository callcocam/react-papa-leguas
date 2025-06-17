<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Callcocam\ReactPapaLeguas\Examples\CompleteTableExample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TenantController extends LandlordController
{
    public function index()
    {
        try {
            // Criar a tabela usando o exemplo completo
            $table = CompleteTableExample::createUsersTable();
            
            // Renderizar os dados da tabela
            $request = request();
            $tableData = $table->render($request); 
            
            // Retornar como JSON para API ou view para web
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'data' => $tableData,
                    'message' => 'Tabela carregada com sucesso'
                ]);
            }
            
            // Para requisições web, retornar view com Inertia
            return inertia('tenants/index', [
                'table' => $tableData,
                'title' => 'Gerenciamento de Tenants',
                'description' => 'Sistema completo de gerenciamento de tenants'
            ]);
            
        } catch (\Exception $e) {
            // Log do erro
            Log::error('Erro ao carregar tabela de tenants: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $request = request();
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao carregar tabela: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('error', 'Erro ao carregar tabela: ' . $e->getMessage());
        }
    }

    /**
     * Teste simples para verificar se o sistema está funcionando
     */
    public function test(Request $request)
    {
        try {
            // Criar tabela simples para teste
            $table = CompleteTableExample::createUsersTable();
            
            // Retornar informações básicas da tabela
            return response()->json([
                'success' => true,
                'message' => 'Sistema Papa Leguas funcionando!',
                'table_info' => [
                    'id' => $table->getId(),
                    'columns_count' => count($table->getColumns()),
                    'filters_count' => count($table->getFilters()),
                    'actions_count' => count($table->getActions()),
                    'bulk_actions_count' => count($table->getBulkActions()),
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro no teste: ' . $e->getMessage(),
                'error_details' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]
            ], 500);
        }
    }
}
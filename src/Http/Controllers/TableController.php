<?php

namespace Callcocam\ReactPapaLeguas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Callcocam\ReactPapaLeguas\ReactPapaLeguas;

class TableController extends Controller
{
    /**
     * Encontra e instancia a classe da tabela com base na sua chave.
     */
    private function getTableInstance(string $tableName)
    {
        try {
            // ✅ Usar o método estático para encontrar a classe da tabela de forma flexível.
            $classPath = ReactPapaLeguas::getTableClass($tableName);

            if (!$classPath || !class_exists($classPath)) {
                return response()->json([
                    'success' => false, 
                    'message' => "Tabela '{$tableName}' não encontrada. Classe: " . ($classPath ?? 'null')
                ], 404);
            }

            return App::make($classPath);
            
        } catch (\Exception $e) {
            Log::error("Erro ao instanciar tabela: {$e->getMessage()}", [
                'table_name' => $tableName,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro ao carregar tabela: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Executa uma ação de callback individual.
     */
    public function executeAction(Request $request, string $tableName, string $actionKey)
    {
        try {
            $table = $this->getTableInstance($tableName);
            
            if ($table instanceof \Illuminate\Http\JsonResponse) {
                return $table; // Retorna erro se tabela não encontrada
            }

            $action = $table->getAction($actionKey);

            if (!$action) {
                return response()->json([
                    'success' => false, 
                    'message' => "Ação '{$actionKey}' não encontrada na tabela '{$tableName}'."
                ], 404);
            }

            $itemId = $request->input('item_id');
            $modelClass = $table->getModelClass();
            $item = $modelClass::find($itemId);

            if (!$item) {
                return response()->json([
                    'success' => false, 
                    'message' => "Item com ID '{$itemId}' não encontrado."
                ], 404);
            }

            $result = $table->executeAction($actionKey, $item, $request->input('data', []));

            // Se o resultado for null, significa que a ação não foi executada
            if ($result === null) {
                return response()->json([
                    'success' => false,
                    'message' => "Falha ao executar a ação '{$actionKey}'. Verifique se é uma CallbackAction."
                ], 500);
            }

            return response()->json($result);
            
        } catch (\Exception $e) {
            Log::error("Erro ao executar ação: {$e->getMessage()}", [
                'table' => $tableName,
                'action' => $actionKey,
                'item_id' => $request->input('item_id'),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Executa uma ação em lote.
     */
    public function executeBulkAction(Request $request, string $tableName, string $actionKey)
    {
        $table = $this->getTableInstance($tableName);
        $action = $table->getAction($actionKey);

        if (!$action) {
            return response()->json(['success' => false, 'message' => 'Ação não encontrada.'], 404);
        }

        $modelClass = $table->getModelClass();
        $itemIds = $request->input('item_ids', []);
        
        if (empty($itemIds)) {
            return response()->json(['success' => false, 'message' => 'Nenhum item selecionado.'], 400);
        }

        $items = $modelClass::whereIn('id', $itemIds)->get();

        $result = $table->executeBulkAction($actionKey, $items, $request->input('data', []));

        return response()->json($result);
    }
} 
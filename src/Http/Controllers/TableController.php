<?php

namespace Callcocam\ReactPapaLeguas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\App;
use Callcocam\ReactPapaLeguas\ReactPapaLeguas;

class TableController extends Controller
{
    /**
     * Encontra e instancia a classe da tabela com base na sua chave.
     */
    private function getTableInstance(string $tableName)
    {
        // ✅ Usar o método estático para encontrar a classe da tabela de forma flexível.
        $classPath = ReactPapaLeguas::getTableClass($tableName);

        if (!$classPath || !class_exists($classPath)) {
            return response()->json(['success' => false, 'message' => 'Tabela não encontrada.'], 404);
        }

        return App::make($classPath);
    }

    /**
     * Executa uma ação de callback individual.
     */
    public function executeAction(Request $request, string $tableName, string $actionKey)
    {
        $table = $this->getTableInstance($tableName);
        $action = $table->getAction($actionKey);

        if (!$action) {
            return response()->json(['success' => false, 'message' => 'Ação não encontrada.'], 404);
        }

        $modelClass = $table->getModelClass();
        $item = $modelClass::find($request->input('item_id'));

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Item não encontrado.'], 404);
        }

        $result = $table->executeAction($actionKey, $item, $request->input('data', []));

        return response()->json($result);
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
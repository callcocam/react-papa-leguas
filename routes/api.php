<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

use Callcocam\ReactPapaLeguas\Facades\ReactPapaLeguas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('api')
    ->middleware(config('react-papa-leguas.api.middleware', ['api'])) // Usar middleware de API padrão
    ->group(function () {

        // Rota unificada e inteligente para executar ações
        Route::post('/{table}/actions/{actionKey}/execute', function (Request $request, string $table, string $actionKey) {
            try {
                // Decodifica o nome da classe da tabela a partir da URL
                $tableClass = ReactPapaLeguas::getTableClass($table);
                if (!$tableClass || !class_exists($tableClass)) {
                    throw new \Exception("Tabela '{$table}' não encontrada.");
                }

                // Instancia a tabela
                $tableInstance = new $tableClass();
                
                // Encontra a ação (CallbackAction ou Action) pelo seu ID
                $action = $tableInstance->getAction($actionKey);

                if (!$action) {
                    throw new \Exception("Ação '{$actionKey}' não encontrada na tabela '{$table}'.");
                }

                $itemId = $request->input('item_id');
                $item = $tableInstance->getDataSource()->getBuilder()->find($itemId);

                if (!$item) {
                    throw new \Exception("Item com ID '{$itemId}' não encontrado.");
                }

                // Executa a ação
                $result = $action->execute($item, $request->input());

                return response()->json($result);

            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erro ao executar ação: ' . $e->getMessage(),
                ], 500);
            }
        })->name('actions.execute');
    });

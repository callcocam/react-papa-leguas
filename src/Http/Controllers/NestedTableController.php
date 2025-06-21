<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;

/**
 * Controller para gerenciar dados de sub-tabelas aninhadas/hierárquicas.
 * 
 * Funcionalidades:
 * - Carregar dados de sub-tabelas via AJAX
 * - Suporte a lazy loading
 * - Paginação, busca e ordenação
 * - Filtros baseados no item pai
 */
class NestedTableController extends Controller
{
    /**
     * Obtém dados de uma sub-tabela específica
     * 
     * @param Request $request
     * @param string $parentId ID do item pai
     * @param string $nestedTableClass Classe da sub-tabela
     * @return JsonResponse
     */
    public function getData(Request $request, string $parentId, string $nestedTableClass): JsonResponse
    {
        try {
            // Validar se a classe existe e é válida
            if (!class_exists($nestedTableClass)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Classe de sub-tabela não encontrada: ' . $nestedTableClass
                ], 404);
            }

            // Verificar se é uma sub-tabela válida
            $reflection = new \ReflectionClass($nestedTableClass);
            if (!$reflection->isSubclassOf(\Callcocam\ReactPapaLeguas\Support\Table\NestedTable::class)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Classe deve estender NestedTable: ' . $nestedTableClass
                ], 400);
            }

            // Instanciar a sub-tabela
            $nestedTable = new $nestedTableClass();

            // Configurar para o item pai específico
            $nestedTable->forParent($parentId);

            // Obter dados com filtros da requisição
            $data = $nestedTable->getNestedData($request);

            return response()->json([
                'success' => true,
                'data' => $data['data'],
                'pagination' => $data['pagination'],
                'config' => $data['config'],
                'columns' => $data['columns'],
                'actions' => $data['actions'],
                'parent_id' => $parentId,
                'nested_table_class' => $nestedTableClass,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados da sub-tabela', [
                'parent_id' => $parentId,
                'nested_table_class' => $nestedTableClass,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => app()->environment('local') ? $e->getMessage() : 'Erro ao carregar dados'
            ], 500);
        }
    }

    /**
     * Obtém configuração inicial de uma sub-tabela (sem dados)
     * 
     * @param Request $request
     * @param string $nestedTableClass Classe da sub-tabela
     * @return JsonResponse
     */
    public function getConfig(Request $request, string $nestedTableClass): JsonResponse
    {
        try {
            // Validar se a classe existe e é válida
            if (!class_exists($nestedTableClass)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Classe de sub-tabela não encontrada: ' . $nestedTableClass
                ], 404);
            }

            // Verificar se é uma sub-tabela válida
            $reflection = new \ReflectionClass($nestedTableClass);
            if (!$reflection->isSubclassOf(\Callcocam\ReactPapaLeguas\Support\Table\NestedTable::class)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Classe deve estender NestedTable: ' . $nestedTableClass
                ], 400);
            }

            // Instanciar a sub-tabela
            $nestedTable = new $nestedTableClass();

            return response()->json([
                'success' => true,
                'config' => $nestedTable->getConfig(),
                'columns' => $nestedTable->getNestedColumnsConfig(),
                'actions' => $nestedTable->getActionsConfig(),
                'nested_table_class' => $nestedTableClass,
            ]);

        } catch (\Exception $e) {
            Log::error('Erro ao obter configuração da sub-tabela', [
                'nested_table_class' => $nestedTableClass,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Erro interno do servidor',
                'message' => app()->environment('local') ? $e->getMessage() : 'Erro ao obter configuração'
            ], 500);
        }
    }

    /**
     * Valida se uma classe de sub-tabela é válida e segura
     * 
     * @param string $nestedTableClass
     * @return array
     */
    protected function validateNestedTableClass(string $nestedTableClass): array
    {
        // Lista de classes permitidas (por segurança)
        $allowedClasses = config('react-papa-leguas.allowed_nested_tables', []);
        
        if (!empty($allowedClasses) && !in_array($nestedTableClass, $allowedClasses)) {
            return [
                'valid' => false,
                'error' => 'Classe de sub-tabela não permitida: ' . $nestedTableClass
            ];
        }

        if (!class_exists($nestedTableClass)) {
            return [
                'valid' => false,
                'error' => 'Classe não encontrada: ' . $nestedTableClass
            ];
        }

        $reflection = new \ReflectionClass($nestedTableClass);
        if (!$reflection->isSubclassOf(\Callcocam\ReactPapaLeguas\Support\Table\NestedTable::class)) {
            return [
                'valid' => false,
                'error' => 'Classe deve estender NestedTable: ' . $nestedTableClass
            ];
        }

        return ['valid' => true];
    }
} 
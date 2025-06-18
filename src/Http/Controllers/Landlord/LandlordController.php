<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Landlord;

use Callcocam\ReactPapaLeguas\Core\Concerns\BelongsToModel;
use Callcocam\ReactPapaLeguas\Http\Controllers\Controller; 
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
    use BelongsToModel;

    /**
     * Instância da tabela Papa Leguas
     */
    protected $table;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // Obter instância da tabela
            $table = $this->getTable();
            
            // Obter query base da tabela
            $query = $table->getBaseQuery();
            
            // Obter dados paginados
            $paginatedData = $query->paginate($request->get('per_page', 15));
            
            return Inertia::render($this->getViewIndex(), [
                'table' => [
                    'data' => $paginatedData->items(),
                    'columns' => $table->getColumnsForFrontend(),
                    'filters' => $table->getFiltersForFrontend(),
                    'actions' => $table->getActionsForFrontend(),
                    'pagination' => [
                        'currentPage' => $paginatedData->currentPage(),
                        'lastPage' => $paginatedData->lastPage(),
                        'perPage' => $paginatedData->perPage(),
                        'total' => $paginatedData->total(),
                        'from' => $paginatedData->firstItem(),
                        'to' => $paginatedData->lastItem(),
                        'hasPages' => $paginatedData->hasPages(),
                        'hasMorePages' => $paginatedData->hasMorePages(),
                        'onFirstPage' => $paginatedData->onFirstPage(),
                        'onLastPage' => $paginatedData->onLastPage(),
                    ],
                    'meta' => [
                        'title' => $this->getPageTitle(),
                        'description' => $this->getPageDescription(),
                        'searchable' => true,
                        'sortable' => true,
                        'filterable' => true,
                    ],
                ],
                ...$this->getDataForViews($request)
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar dados da tabela: ' . $e->getMessage());
            
            return Inertia::render($this->getViewIndex(), [
                'table' => [
                    'data' => [],
                    'columns' => [],
                    'filters' => [],
                    'actions' => [],
                    'pagination' => null,
                    'meta' => [
                        'title' => $this->getPageTitle(),
                        'description' => $this->getPageDescription(),
                        'searchable' => true,
                        'sortable' => true,
                        'filterable' => true,
                    ],
                ],
                'error' => 'Erro ao carregar dados. Tente novamente.',
                ...$this->getDataForViews($request)
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render($this->getViewCreate(), [
            'model' => new ($this->getModelClass()),
            ...$this->getDataForViews(request())
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validação usando as regras da tabela se disponível
            $rules = $this->getValidationRules('store');
            $validated = $request->validate($rules);

            DB::beginTransaction();

            $model = $this->getModelClass()::create($validated);

            DB::commit();

            return redirect()
                ->route($this->getRouteIndex())
                ->with('success', 'Registro criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao criar registro: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao criar registro. Tente novamente.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $model = $this->getModelClass()::findOrFail($id);
            
            return Inertia::render($this->getViewShow(), [
                'model' => $model,
                ...$this->getDataForViews(request())
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route($this->getRouteIndex())
                ->withErrors(['error' => 'Registro não encontrado.']);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $model = $this->getModelClass()::findOrFail($id);
            
            return Inertia::render($this->getViewEdit(), [
                'model' => $model,
                ...$this->getDataForViews(request())
            ]);
        } catch (\Exception $e) {
            return redirect()
                ->route($this->getRouteIndex())
                ->withErrors(['error' => 'Registro não encontrado.']);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $model = $this->getModelClass()::findOrFail($id);
            
            // Validação usando as regras da tabela se disponível
            $rules = $this->getValidationRules('update', $model);
            $validated = $request->validate($rules);

            DB::beginTransaction();

            $model->update($validated);

            DB::commit();

            return redirect()
                ->route($this->getRouteIndex())
                ->with('success', 'Registro atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao atualizar registro: ' . $e->getMessage());
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Erro ao atualizar registro. Tente novamente.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $model = $this->getModelClass()::findOrFail($id);
            
            DB::beginTransaction();
            
            $model->delete();
            
            DB::commit();

            return redirect()
                ->route($this->getRouteIndex())
                ->with('success', 'Registro excluído com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao excluir registro: ' . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Erro ao excluir registro. Tente novamente.']);
        }
    }

    /**
     * Export data to CSV
     */
    public function export(Request $request)
    {
        try {
            $table = $this->getTable();
            $query = $table->query($request);
            
            // Obter todos os dados para export
            $data = $query->get();
            
            $filename = Str::slug($this->getPageTitle()) . '-' . now()->format('Y-m-d-H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            ];

            $callback = function() use ($data, $table) {
                $file = fopen('php://output', 'w');
                
                // Cabeçalhos do CSV
                $columns = $table->getColumns();
                $headers = array_map(fn($col) => $col['label'], $columns);
                fputcsv($file, $headers);
                
                // Dados
                foreach ($data as $row) {
                    $csvRow = [];
                    foreach ($columns as $column) {
                        $csvRow[] = $row->{$column['key']} ?? '';
                    }
                    fputcsv($file, $csvRow);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            Log::error('Erro ao exportar dados: ' . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Erro ao exportar dados. Tente novamente.']);
        }
    }

    /**
     * Bulk delete records
     */
    public function bulkDestroy(Request $request)
    {
        try {
            $ids = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'integer|exists:' . (new ($this->getModelClass()))->getTable() . ',id'
            ])['ids'];

            DB::beginTransaction();

            $this->getModelClass()::whereIn('id', $ids)->delete();

            DB::commit();

            return redirect()
                ->route($this->getRouteIndex())
                ->with('success', count($ids) . ' registro(s) excluído(s) com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro na exclusão em lote: ' . $e->getMessage());
            
            return back()
                ->withErrors(['error' => 'Erro na exclusão em lote. Tente novamente.']);
        }
    }

    /**
     * Get table instance
     */
    protected function getTable()
    {
        if (!$this->table) {
            $tableClass = $this->getTableClass();
            $this->table = new $tableClass();
            
            // Chamar setUp se não foi chamado automaticamente
            if (method_exists($this->table, 'setUp') && !$this->table->getModel()) {
                $this->table->setUp();
            }
        }
        
        return $this->table;
    }

    /**
     * Get table class name
     */
    protected function getTableClass(): string
    {
        $controllerName = class_basename(static::class);
        $tableName = str_replace('Controller', 'Table', $controllerName);
        
        // Tentar diferentes namespaces
        $possibleClasses = [
            "App\\Tables\\{$tableName}",
            "Callcocam\\ReactPapaLeguas\\Tables\\{$tableName}",
        ];
        
        foreach ($possibleClasses as $class) {
            if (class_exists($class)) {
                return $class;
            }
        }
        
        throw new \Exception("Table class not found for {$controllerName}");
    }

    /**
     * Get validation rules
     */
    protected function getValidationRules(string $operation, $model = null): array
    {
        try {
            $table = $this->getTable();
            if (method_exists($table, 'getValidationRules')) {
                return $table->getValidationRules($operation, $model);
            }
        } catch (\Exception $e) {
            // Fallback para regras básicas se a tabela não tiver validação
        }
        
        return $this->getDefaultValidationRules($operation, $model);
    }

    /**
     * Get default validation rules (pode ser sobrescrito)
     */
    protected function getDefaultValidationRules(string $operation, $model = null): array
    {
        return [];
    }

    /**
     * Get page title
     */
    protected function getPageTitle(): string
    {
        $modelName = class_basename($this->getModelClass());
        return Str::plural($modelName);
    }

    /**
     * Get page description
     */
    protected function getPageDescription(): string
    {
        return "Gerencie " . strtolower($this->getPageTitle()) . " do sistema";
    }

    /**
     * Get route names
     */
    protected function getRouteIndex(): string
    {
        $controllerName = Str::snake(str_replace('Controller', '', class_basename(static::class)));
        return "{$controllerName}.index";
    }

    /**
     * Get view names
     */
    protected function getViewIndex(): string
    {
        //Vamos usar o crud/index para todos os controllers por default
        return 'crud/index';
            // $controllerName = str_replace('Controller', '', class_basename(static::class));
            // return "Landlord/{$controllerName}/Index";
    }

    protected function getViewCreate(): string
    {
        //Vamos usar o crud/create para todos os controllers por default
        return 'crud/create';
        // $controllerName = str_replace('Controller', '', class_basename(static::class));
        // return "Landlord/{$controllerName}/Create";
    }

    protected function getViewShow(): string
    {
        //Vamos usar o crud/show para todos os controllers por default
        return 'crud/show';
        // $controllerName = str_replace('Controller', '', class_basename(static::class));
        // return "Landlord/{$controllerName}/Show";
    }

    protected function getViewEdit(): string
    {
        //Vamos usar o crud/edit para todos os controllers por default
        return 'crud/edit';
        // $controllerName = str_replace('Controller', '', class_basename(static::class));
        // return "Landlord/{$controllerName}/Edit";
    }

    /**
     * Get data for views.
     */
    protected function getDataForViews(Request $request)
    {
        $routePrefix = $this->getRoutePrefix();
        
        return [
            'user' => auth()->user(),
            'permissions' => [],
            'request' => $request->query(),
            'routes' => [
                'index' => route($routePrefix . '.index'),
                'create' => route($routePrefix . '.create'),
                'store' => route($routePrefix . '.store'),
                'show' => fn($id) => route($routePrefix . '.show', $id),
                'edit' => fn($id) => route($routePrefix . '.edit', $id),
                'update' => fn($id) => route($routePrefix . '.update', $id),
                'destroy' => fn($id) => route($routePrefix . '.destroy', $id),
                'export' => route($routePrefix . '.export'),
                'bulk_destroy' => route($routePrefix . '.bulk-destroy'),
            ],
            'config' => [
                'model_name' => class_basename($this->getModelClass()),
                'page_title' => $this->getPageTitle(),
                'page_description' => $this->getPageDescription(),
                'route_prefix' => $routePrefix,
                'can_create' => true, // TODO: implementar permissões
                'can_edit' => true,
                'can_delete' => true,
                'can_export' => true,
                'can_bulk_delete' => true,
            ]
        ];
    }

    /**
     * Get route prefix for current controller
     */
    protected function getRoutePrefix(): string
    {
        $controllerName = Str::snake(str_replace('Controller', '', class_basename(static::class)));
        
        // Para controllers que herdam do LandlordController, usar apenas o nome
        // As rotas já estão registradas com prefixo 'landlord.' no arquivo de rotas
        return "landlord.{$controllerName}";
    }
}

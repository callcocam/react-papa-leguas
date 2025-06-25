<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Http\Controllers\Admin;

use Callcocam\ReactPapaLeguas\Http\Controllers\Controller;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToModel;
use Callcocam\ReactPapaLeguas\Support\Concerns\ModelQueries;
use Callcocam\ReactPapaLeguas\Support\Concerns\ResolvesModel;
use Callcocam\ReactPapaLeguas\Support\Concerns\HasPermissionChecks;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Class AdminController
 * @package Callcocam\ReactPapaLeguas\Http\Controllers\Admin
 */
class AdminController extends Controller
{
    use BelongsToModel, ResolvesModel, ModelQueries, HasPermissionChecks;

    /**
     * Configurações de eager loading específicas do controller
     * 
     * @var array
     */
    protected array $eagerLoadConfig = [
        'auto_configure' => true,
        'custom_relations' => [],
    ];

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
            Storage::put('debug.json', json_encode($this->getDataForViewsIndex($request)));
            return Inertia::render('crud/debug', $this->getDataForViewsIndex($request));
        }
        return Inertia::render($this->getViewIndex(), $this->getDataForViewsIndex($request));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Verificar permissão de criação (opcional - não quebra se não configurado)
        if ($this->shouldCheckPermissions()) {
            $this->authorizePermission('create', $this->getModelClass());
        }

        return Inertia::render($this->getViewCreate(), $this->getDataForViewsCreate($request));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Verificar permissão de criação (opcional - não quebra se não configurado)
        if ($this->shouldCheckPermissions()) {
            $this->authorizePermission('create', $this->getModelClass());
        }

        $this->createRecord($request->all());
        return redirect()->route($this->getRouteIndex());
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, string $id)
    {
        // Buscar o registro primeiro para verificação de permissão
        $record = $this->findRecord($id);
        
        // Verificar permissão de visualização (opcional - não quebra se não configurado)
        if ($this->shouldCheckPermissions() && $record) {
            $this->authorizePermission('view', $record);
        }

        return Inertia::render($this->getViewShow(), $this->getDataForViewsShow($request, $id));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    { 
        // Buscar o registro primeiro para verificação de permissão
        $record = $this->findRecord($id);
        
        // Verificar permissão de edição (opcional - não quebra se não configurado)
        if ($this->shouldCheckPermissions() && $record) {
            $this->authorizePermission('update', $record);
        }

        return Inertia::render($this->getViewEdit(), $this->getDataForViewsEdit($request, $id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // Buscar o registro primeiro para verificação de permissão
        $record = $this->findRecord($id);
        
        // Verificar permissão de atualização (opcional - não quebra se não configurado)
        if ($this->shouldCheckPermissions() && $record) {
            $this->authorizePermission('update', $record);
        }

        $this->updateRecord($id, $request->all());
        return redirect()->route($this->getRouteIndex());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        // Buscar o registro primeiro para verificação de permissão
        $record = $this->findRecord($id);
        
        // Verificar permissão de exclusão (opcional - não quebra se não configurado)
        if ($this->shouldCheckPermissions() && $record) {
            $this->authorizePermission('delete', $record);
        }

        $this->deleteRecord($id);
        return redirect()->route($this->getRouteIndex());
    }

    /**
     * Test method for debugging.
     */
    public function test(Request $request)
    {
        return Inertia::render('crud/debug', $this->getDataForViews($request));
    }

    /**
     * Get data for index view with table configuration e eager loading inteligente.
     */
    protected function getDataForViewsIndex(Request $request)
    {
        // Configurar eager loading para o contexto index
        $this->configureEagerLoadingForController();
        
        // 🎯 SIMPLIFICADO: Usar apenas toArray() da Table que já faz TUDO
        $table = $this->getTable();
        
        // Configurar eager loading na Table baseado no controller
        $customRelations = $this->getCustomRelationsForContext('index');
        if (!empty($customRelations)) {
            $table->getDataSource()->with($customRelations);
        }
        
        // O trait InteractsWithTable já processa automaticamente:
        // - request('search')
        // - request('filters') 
        // - request('sort_column') e request('sort_direction')
        // - paginação
        $tableData = $table->toArray();
        
        // Adicionar informações de relacionamentos para debugging
        if (config('app.debug')) {
            $tableData['debug_relations'] = [
                'detected' => $this->getDetectedRelationsInfo(),
                'loaded_for_index' => $this->getRelationsForContext('index'),
                'custom_config' => $customRelations,
                'table_data_source' => $table->getDataSource()->getType(),
            ];
        }
        
        return array_merge($this->getDataForViews($request), $tableData);
    }

    /**
     * Get data for create view com eager loading para formulários.
     */
    protected function getDataForViewsCreate(Request $request)
    {
        $data = parent::getDataForViewsCreate($request);
        
        // Configurar eager loading para formulários
        $this->configureEagerLoadingForController();
        
        // Carregar dados relacionados necessários para formulários
        $relatedData = $this->loadRelatedDataForForms('create');
        
        // Adicionar dados específicos para criação
        $data['mode'] = 'create';
        $data['form_data'] = $this->getDefaultFormData();
        $data['related_data'] = $relatedData;
        
        // Debug de relacionamentos carregados
        if (config('app.debug')) {
            $data['debug_relations'] = [
                'loaded_for_create' => $this->getRelationsForContext('create'),
                'related_data_keys' => array_keys($relatedData),
            ];
        }
        
        return $data;
    }

    /**
     * Get data for edit view with record data e eager loading contextual.
     */
    protected function getDataForViewsEdit(Request $request, string $id)
    {
        $data = parent::getDataForViewsEdit($request, $id);
        
        // Configurar eager loading para formulários
        $this->configureEagerLoadingForController();
        
        // Buscar registro para edição com relacionamentos contextuais
        $customRelations = $this->getCustomRelationsForContext('edit');
        $record = $this->findById($id, $customRelations, 'edit');
        
        // Carregar dados relacionados para formulários
        $relatedData = $this->loadRelatedDataForForms('edit');
        
        $data['mode'] = 'edit';
        $data['record'] = $record;
        $data['form_data'] = $record ? $record->toArray() : $this->getDefaultFormData();
        $data['related_data'] = $relatedData;
        
        // Debug de relacionamentos carregados
        if (config('app.debug')) {
            $data['debug_relations'] = [
                'loaded_for_edit' => $this->getRelationsForContext('edit'),
                'custom_config' => $customRelations,
                'record_relations' => $record ? array_keys($record->getRelations()) : [],
            ];
        }
        
        return $data;
    }

    /**
     * Get data for show view with record data e eager loading completo.
     */
    protected function getDataForViewsShow(Request $request, string $id)
    {
        $data = parent::getDataForViewsShow($request, $id);
        
        // Configurar eager loading para visualização completa
        $this->configureEagerLoadingForController();
        
        // Buscar registro para visualização com todos relacionamentos necessários
        $customRelations = $this->getCustomRelationsForContext('show');
        $record = $this->findById($id, $customRelations, 'show');
        
        $data['mode'] = 'show';
        $data['record'] = $record;
        
        // Debug de relacionamentos carregados
        if (config('app.debug')) {
            $data['debug_relations'] = [
                'loaded_for_show' => $this->getRelationsForContext('show'),
                'custom_config' => $customRelations,
                'record_relations' => $record ? array_keys($record->getRelations()) : [],
                'detected_relations' => $this->getDetectedRelationsInfo(),
            ];
        }
        
        return $data;
    }

    /**
     * Get the route name for index action.
     */
    protected function getRouteIndex(): string
    {
        return $this->getRouteName('index');
    }

    /**
     * Get default form data for create/edit forms.
     */
    protected function getDefaultFormData(): array
    {
        // Pode ser sobrescrito nas classes filhas para dados específicos
        return [];
    }



    /**
     * Find a record by ID using the resolved model.
     */
    protected function findRecord(string $id)
    {
        try {
            $modelClass = $this->resolveModelClass();
            return $modelClass::findOrFail($id);
        } catch (\Exception $e) {
            // Log do erro para debugging
            Log::warning("Registro não encontrado", [
                'id' => $id,
                'model' => $this->resolveModelClass(),
                'controller' => static::class,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Configura eager loading específico do controller
     * 
     * @return $this
     */
    protected function configureEagerLoadingForController(): static
    {
        if (!$this->eagerLoadConfig['auto_configure']) {
            return $this;
        }

        // Auto-configurar relacionamentos BelongsTo se disponível
        if (method_exists($this, 'autoConfigureBelongsToRelations')) {
            $this->autoConfigureBelongsToRelations();
        }

        // Se temos configurações customizadas, aplicá-las
        if (!empty($this->eagerLoadConfig['custom_relations'])) {
            foreach ($this->eagerLoadConfig['custom_relations'] as $context => $relations) {
                $this->setEagerLoadForContext($context, $relations);
            }
        }

        // Aplicar configurações específicas do controller filho
        $this->configureControllerSpecificRelations();

        return $this;
    }

    /**
     * Configura relacionamentos específicos do controller (para sobrescrever)
     * 
     * @return void
     */
    protected function configureControllerSpecificRelations(): void
    {
        // Este método pode ser sobrescrito nos controllers filhos
        // para configurar relacionamentos específicos do modelo
        
        // Exemplo de uso nos controllers filhos:
        // $this->addEagerLoadToContext('index', 'category');
        // $this->addEagerLoadToContext('show', ['category', 'user', 'tenant']);
    }

    /**
     * Obtém relacionamentos customizados para um contexto específico
     * 
     * @param string $context
     * @return array
     */
    protected function getCustomRelationsForContext(string $context): array
    {
        return $this->eagerLoadConfig['custom_relations'][$context] ?? [];
    }

    /**
     * Carrega dados relacionados necessários para formulários
     * 
     * @param string $context
     * @return array
     */
    protected function loadRelatedDataForForms(string $context): array
    {
        // Se a trait BelongsToModel está disponível, usar método otimizado
        if (method_exists($this, 'getRelatedDataForSelects')) {
            try {
                return $this->getRelatedDataForSelects();
            } catch (\Exception $e) {
                Log::warning("Erro ao usar BelongsToModel para carregar dados relacionados", [
                    'context' => $context,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Fallback para método tradicional
        $relatedData = [];
        $detectedRelations = $this->getDetectedRelationsInfo();

        foreach ($detectedRelations as $relationName => $relationInfo) {
            // Carregar apenas relacionamentos BelongsTo para formulários (dropdowns/selects)
            if ($relationInfo['type'] === 'BelongsTo') {
                try {
                    $relatedModelClass = $relationInfo['related_model'];
                    
                    // Carregar dados básicos do modelo relacionado
                    $relatedData[$relationName] = $relatedModelClass::select(['id', 'name', 'title', 'email'])
                        ->take(100) // Limitar para evitar sobrecarga
                        ->get()
                        ->map(function ($item) {
                            // Retornar dados básicos para selects
                            return [
                                'id' => $item->id,
                                'name' => $item->name ?? $item->title ?? $item->email ?? "#{$item->id}",
                            ];
                        });
                        
                } catch (\Exception $e) {
                    Log::warning("Erro ao carregar dados relacionados", [
                        'relation' => $relationName,
                        'model' => $relationInfo['related_model'],
                        'context' => $context,
                        'error' => $e->getMessage()
                    ]);
                    
                    $relatedData[$relationName] = [];
                }
            }
        }

        return $relatedData;
    }

    /**
     * Configura relacionamentos customizados para um contexto
     * 
     * @param string $context
     * @param array $relations
     * @return $this
     */
    public function setCustomRelationsForContext(string $context, array $relations): static
    {
        $this->eagerLoadConfig['custom_relations'][$context] = $relations;
        return $this;
    }

    /**
     * Adiciona relacionamento customizado a um contexto
     * 
     * @param string $context
     * @param string $relation
     * @return $this
     */
    public function addCustomRelationToContext(string $context, string $relation): static
    {
        if (!isset($this->eagerLoadConfig['custom_relations'][$context])) {
            $this->eagerLoadConfig['custom_relations'][$context] = [];
        }
        
        if (!in_array($relation, $this->eagerLoadConfig['custom_relations'][$context])) {
            $this->eagerLoadConfig['custom_relations'][$context][] = $relation;
        }
        
        return $this;
    }

    /**
     * Habilita ou desabilita configuração automática
     * 
     * @param bool $enabled
     * @return $this
     */
    public function setAutoConfigureEagerLoad(bool $enabled): static
    {
        $this->eagerLoadConfig['auto_configure'] = $enabled;
        return $this;
    }

    /**
     * Obtém informações sobre configuração de eager loading do controller
     * 
     * @return array
     */
    public function getEagerLoadConfig(): array
    {
        return [
            'controller_config' => $this->eagerLoadConfig,
            'detected_relations' => $this->getDetectedRelationsInfo(),
            'current_contexts' => [
                'index' => $this->getRelationsForContext('index'),
                'show' => $this->getRelationsForContext('show'),
                'edit' => $this->getRelationsForContext('edit'),
                'create' => $this->getRelationsForContext('create'),
            ],
        ];
    }

    /**
     * Obtém informações completas sobre relacionamentos (debugging avançado)
     * 
     * @return array
     */
    public function getCompleteRelationshipsInfo(): array
    {
        $info = [
            'model' => $this->resolveModelClass(),
            'controller' => static::class,
            'eager_loading' => $this->getEagerLoadConfig(),
        ];

        // Se BelongsToModel está disponível, adicionar informações específicas
        if (method_exists($this, 'getBelongsToStats')) {
            $info['belongs_to'] = $this->getBelongsToStats();
        }

        // Auto-configurar relacionamentos para obter informações completas
        if (method_exists($this, 'ensureAutoConfiguration')) {
            $this->ensureAutoConfiguration();
            
            if (method_exists($this, 'getAutoConfiguredRelations')) {
                $info['auto_configured_relations'] = array_keys($this->getAutoConfiguredRelations());
            }
            
            if (method_exists($this, 'getManuallyConfiguredRelations')) {
                $info['manually_configured_relations'] = array_keys($this->getManuallyConfiguredRelations());
            }
        }

        return $info;
    }

    /**
     * Endpoint para debugging de relacionamentos (apenas em desenvolvimento)
     * 
     * @return \Inertia\Response|\Illuminate\Http\JsonResponse
     */
    public function debugRelationships()
    {
        if (!config('app.debug')) {
            abort(404);
        }

        $info = $this->getCompleteRelationshipsInfo();
        
        // Teste prático carregando dados
        try {
            $testData = $this->loadRelatedDataForForms('debug');
            $info['test_related_data'] = [
                'loaded_relations' => array_keys($testData),
                'data_counts' => array_map('count', $testData),
            ];
        } catch (\Exception $e) {
            $info['test_related_data'] = [
                'error' => $e->getMessage()
            ];
        }

        return response()->json($info, 200, [], JSON_PRETTY_PRINT);
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

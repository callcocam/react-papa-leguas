<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use ReflectionClass;
use ReflectionMethod;

trait ModelQueries
{
    use ResolvesModel;

    /**
     * Configurações de query padrão
     * 
     * @var array
     */
    protected array $queryConfig = [
        'per_page' => 15,
        'order_by' => 'created_at',
        'order_direction' => 'desc',
        'with_relations' => [],
        'searchable_columns' => ['name', 'email'],
        'filterable_columns' => ['status'],
        'auto_detect_relations' => true,
        'eager_load_context' => [
            'index' => [],
            'show' => [],
            'edit' => [],
            'create' => [],
        ],
    ];

    /**
     * Cache de relacionamentos detectados automaticamente
     * 
     * @var array|null
     */
    protected ?array $detectedRelations = null;

    /**
     * Auto-detecta relacionamentos do modelo usando reflexão
     * 
     * @return array
     */
    public function detectModelRelationships(): array
    {
        if ($this->detectedRelations !== null) {
            return $this->detectedRelations;
        }

        $modelClass = $this->getResolvedModelClass();
        if (!$modelClass) {
            $this->detectedRelations = [];
            return [];
        }

        $cacheKey = "papa_leguas_relations_{$modelClass}";
        
        // Tentar do cache primeiro
        $cached = Cache::get($cacheKey);
        if ($cached) {
            $this->detectedRelations = $cached;
            return $cached;
        }

        try {
            $reflection = new ReflectionClass($modelClass);
            $relations = [];

            foreach ($reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                // Pular métodos herdados de classes base
                if ($method->getDeclaringClass()->getName() !== $modelClass) {
                    continue;
                }

                // Pular métodos com parâmetros obrigatórios
                if ($method->getNumberOfRequiredParameters() > 0) {
                    continue;
                }

                $methodName = $method->getName();
                
                // Pular getters, setters e outros métodos especiais
                if (str_starts_with($methodName, 'get') || 
                    str_starts_with($methodName, 'set') || 
                    str_starts_with($methodName, 'is') ||
                    str_starts_with($methodName, 'has') ||
                    in_array($methodName, ['toArray', 'toJson', 'fresh', 'refresh', 'replicate'])) {
                    continue;
                }

                try {
                    $instance = new $modelClass();
                    $result = $instance->$methodName();
                    
                    // Verificar se retorna um relacionamento Eloquent
                    if ($result instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                        $relationType = class_basename(get_class($result));
                        $relations[$methodName] = [
                            'type' => $relationType,
                            'related_model' => get_class($result->getRelated()),
                            'is_single' => in_array($relationType, ['BelongsTo', 'HasOne', 'MorphOne']),
                            'is_multiple' => in_array($relationType, ['HasMany', 'BelongsToMany', 'MorphMany', 'HasManyThrough']),
                        ];
                    }
                } catch (\Exception $e) {
                    // Ignorar métodos que causam erro
                    continue;
                }
            }

            // Salvar no cache por 1 hora
            Cache::put($cacheKey, $relations, 3600);
            $this->detectedRelations = $relations;
            
            return $relations;
            
        } catch (\Exception $e) {
            Log::warning("Erro ao detectar relacionamentos do modelo", [
                'model' => $modelClass,
                'error' => $e->getMessage()
            ]);
            
            $this->detectedRelations = [];
            return [];
        }
    }

    /**
     * Configura eager loading baseado no contexto
     * 
     * @param string $context ('index', 'show', 'edit', 'create')
     * @param array $customRelations Relacionamentos customizados para sobrescrever
     * @return array
     */
    public function configureEagerLoading(string $context = 'index', array $customRelations = []): array
    {
        // Se foram passados relacionamentos customizados, usar eles
        if (!empty($customRelations)) {
            return $customRelations;
        }

        // Se já temos configuração específica para o contexto, usar ela
        if (!empty($this->queryConfig['eager_load_context'][$context])) {
            return $this->queryConfig['eager_load_context'][$context];
        }

        // Se temos relacionamentos configurados manualmente, usar eles
        if (!empty($this->queryConfig['with_relations'])) {
            return $this->queryConfig['with_relations'];
        }

        // Se auto-detecção está desabilitada, retornar vazio
        if (!$this->queryConfig['auto_detect_relations']) {
            return [];
        }

        // Auto-detectar relacionamentos e aplicar regras baseadas no contexto
        $detectedRelations = $this->detectModelRelationships();
        $relations = [];

        foreach ($detectedRelations as $relationName => $relationInfo) {
            $shouldLoad = match ($context) {
                'index' => $this->shouldLoadRelationForIndex($relationName, $relationInfo),
                'show' => $this->shouldLoadRelationForShow($relationName, $relationInfo),
                'edit', 'create' => $this->shouldLoadRelationForForm($relationName, $relationInfo),
                default => false,
            };

            if ($shouldLoad) {
                $relations[] = $relationName;
            }
        }

        return $relations;
    }

    /**
     * Determina se um relacionamento deve ser carregado na listagem (index)
     * 
     * @param string $relationName
     * @param array $relationInfo
     * @return bool
     */
    protected function shouldLoadRelationForIndex(string $relationName, array $relationInfo): bool
    {
        // Carregar relacionamentos belongsTo comuns para exibição
        if ($relationInfo['type'] === 'BelongsTo') {
            return in_array($relationName, ['user', 'tenant', 'category', 'priority', 'status', 'parent', 'author', 'assignee']);
        }

        // Não carregar relacionamentos hasMany por padrão (performance)
        return false;
    }

    /**
     * Determina se um relacionamento deve ser carregado na visualização (show)
     * 
     * @param string $relationName
     * @param array $relationInfo
     * @return bool
     */
    protected function shouldLoadRelationForShow(string $relationName, array $relationInfo): bool
    {
        // Na visualização, carregar a maioria dos relacionamentos belongsTo e hasOne
        if ($relationInfo['is_single']) {
            return true;
        }

        // Para hasMany, carregar apenas alguns específicos importantes
        if ($relationInfo['is_multiple']) {
            return in_array($relationName, ['children', 'items', 'attachments', 'comments', 'history']);
        }

        return false;
    }

    /**
     * Determina se um relacionamento deve ser carregado nos formulários (edit/create)
     * 
     * @param string $relationName
     * @param array $relationInfo
     * @return bool
     */
    protected function shouldLoadRelationForForm(string $relationName, array $relationInfo): bool
    {
        // Nos formulários, carregar principalmente belongsTo para selects/dropdowns
        if ($relationInfo['type'] === 'BelongsTo') {
            return in_array($relationName, ['user', 'tenant', 'category', 'priority', 'status', 'parent', 'author']);
        }

        return false;
    }

    /**
     * Obtém relacionamentos para um contexto específico
     * 
     * @param string $context
     * @return array
     */
    public function getRelationsForContext(string $context): array
    {
        return $this->configureEagerLoading($context);
    }

    /**
     * Obtém todos os registros
     * 
     * @param array $columns
     * @return Collection
     */
    public function getAll(array $columns = ['*']): Collection
    {
        $modelClass = $this->getResolvedModelClass();
        return $modelClass::all($columns);
    }

    /**
     * Obtém registros paginados
     * 
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = null, array $columns = ['*']): LengthAwarePaginator
    {
        $perPage = $perPage ?? $this->queryConfig['per_page'];
        $modelClass = $this->getResolvedModelClass();
        
        return $modelClass::query()
            ->orderBy($this->queryConfig['order_by'], $this->queryConfig['order_direction'])
            ->paginate($perPage, $columns);
    }

    /**
     * Obtém registros com relacionamentos
     * 
     * @param array $relations
     * @param array $columns
     * @return Collection
     */
    public function getWithRelations(array $relations = [], array $columns = ['*']): Collection
    {
        $relations = empty($relations) ? $this->queryConfig['with_relations'] : $relations;
        $modelClass = $this->getResolvedModelClass();
        
        return $modelClass::with($relations)->get($columns);
    }

    /**
     * Obtém registros paginados com relacionamentos
     * 
     * @param array $relations
     * @param int $perPage
     * @param array $columns
     * @return LengthAwarePaginator
     */
    public function getPaginatedWithRelations(array $relations = [], int $perPage = null, array $columns = ['*']): LengthAwarePaginator
    {
        $relations = empty($relations) ? $this->queryConfig['with_relations'] : $relations;
        $perPage = $perPage ?? $this->queryConfig['per_page'];
        $modelClass = $this->getResolvedModelClass();
        
        return $modelClass::with($relations)
            ->orderBy($this->queryConfig['order_by'], $this->queryConfig['order_direction'])
            ->paginate($perPage, $columns);
    }

    /**
     * Busca registros por termo de pesquisa
     * 
     * @param string $search
     * @param array $columns
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function search(string $search, array $columns = null, int $perPage = null): LengthAwarePaginator
    {
        $columns = $columns ?? $this->queryConfig['searchable_columns'];
        $perPage = $perPage ?? $this->queryConfig['per_page'];
        $modelClass = $this->getResolvedModelClass();
        
        $query = $modelClass::query();
        
        if (!empty($search)) {
            $query->where(function (Builder $q) use ($search, $columns) {
                foreach ($columns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }
        
        return $query->orderBy($this->queryConfig['order_by'], $this->queryConfig['order_direction'])
                    ->paginate($perPage);
    }

    /**
     * Filtra registros por critérios
     * 
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function filter(array $filters, int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? $this->queryConfig['per_page'];
        $modelClass = $this->getResolvedModelClass();
        
        $query = $modelClass::query();
        
        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }
        
        return $query->orderBy($this->queryConfig['order_by'], $this->queryConfig['order_direction'])
                    ->paginate($perPage);
    }

    /**
     * Busca e filtra registros
     * 
     * @param string $search
     * @param array $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function searchAndFilter(string $search, array $filters = [], int $perPage = null): LengthAwarePaginator
    {
        $perPage = $perPage ?? $this->queryConfig['per_page'];
        $modelClass = $this->getResolvedModelClass();
        $searchableColumns = $this->queryConfig['searchable_columns'];
        
        $query = $modelClass::query();
        
        // Aplicar busca
        if (!empty($search)) {
            $query->where(function (Builder $q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }
        
        // Aplicar filtros
        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }
        
        return $query->orderBy($this->queryConfig['order_by'], $this->queryConfig['order_direction'])
                    ->paginate($perPage);
    }

    /**
     * Obtém registro por ID com eager loading inteligente
     * 
     * @param string $id
     * @param array $relations Relacionamentos customizados (opcional)
     * @param string $context Contexto da operação ('show', 'edit', etc.)
     * @return Model|null
     */
    public function findById(string $id, array $relations = [], string $context = 'show'): ?Model
    {
        $modelClass = $this->getResolvedModelClass();
        
        // Configurar eager loading inteligente
        $eagerLoadRelations = $this->configureEagerLoading($context, $relations);
        
        if (empty($eagerLoadRelations)) {
            return $modelClass::find($id);
        }
        
        return $modelClass::with($eagerLoadRelations)->find($id);
    }

    /**
     * Obtém registro por ID ou falha com eager loading inteligente
     * 
     * @param string $id
     * @param array $relations Relacionamentos customizados (opcional)
     * @param string $context Contexto da operação ('show', 'edit', etc.)
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByIdOrFail(string $id, array $relations = [], string $context = 'show'): Model
    {
        $modelClass = $this->getResolvedModelClass();
        
        // Configurar eager loading inteligente
        $eagerLoadRelations = $this->configureEagerLoading($context, $relations);
        
        if (empty($eagerLoadRelations)) {
            return $modelClass::findOrFail($id);
        }
        
        return $modelClass::with($eagerLoadRelations)->findOrFail($id);
    }

    /**
     * Cria novo registro
     * 
     * @param array $data
     * @return Model
     */
    public function createRecord(array $data): Model
    {
        $modelClass = $this->getResolvedModelClass();
        
        try {
            DB::beginTransaction();
            
            $model = $modelClass::create($data);
            
            DB::commit();
            
            Log::info("Registro criado com sucesso", [
                'model' => $modelClass,
                'id' => $model->id,
                'data' => $data
            ]);
            
            return $model;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Erro ao criar registro", [
                'model' => $modelClass,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Atualiza registro
     * 
     * @param string $id
     * @param array $data
     * @return Model
     */
    public function updateRecord(string $id, array $data): Model
    {
        $modelClass = $this->getResolvedModelClass();
        $model = $this->findByIdOrFail($id);
        
        try {
            DB::beginTransaction();
            
            $model->update($data);
            
            DB::commit();
            
            Log::info("Registro atualizado com sucesso", [
                'model' => $modelClass,
                'id' => $id,
                'data' => $data
            ]);
            
            return $model;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Erro ao atualizar registro", [
                'model' => $modelClass,
                'id' => $id,
                'data' => $data,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Exclui registro
     * 
     * @param string $id
     * @return bool
     */
    public function deleteRecord(string $id): bool
    {
        $modelClass = $this->getResolvedModelClass();
        $model = $this->findByIdOrFail($id);
        
        try {
            DB::beginTransaction();
            
            $deleted = $model->delete();
            
            DB::commit();
            
            Log::info("Registro excluído com sucesso", [
                'model' => $modelClass,
                'id' => $id
            ]);
            
            return $deleted;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Erro ao excluir registro", [
                'model' => $modelClass,
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Exclui múltiplos registros
     * 
     * @param array $ids
     * @return int
     */
    public function deleteRecords(array $ids): int
    {
        $modelClass = $this->getResolvedModelClass();
        
        try {
            DB::beginTransaction();
            
            $deleted = $modelClass::whereIn('id', $ids)->delete();
            
            DB::commit();
            
            Log::info("Múltiplos registros excluídos com sucesso", [
                'model' => $modelClass,
                'ids' => $ids,
                'count' => $deleted
            ]);
            
            return $deleted;
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error("Erro ao excluir múltiplos registros", [
                'model' => $modelClass,
                'ids' => $ids,
                'error' => $e->getMessage()
            ]);
            
            throw $e;
        }
    }

    /**
     * Processa requisição e retorna dados paginados com eager loading inteligente
     * 
     * @param Request $request
     * @param array $relations Relacionamentos customizados (opcional)
     * @param string $context Contexto da operação ('index', 'show', 'edit', 'create')
     * @return LengthAwarePaginator
     */
    public function processRequest(Request $request, array $relations = [], string $context = 'index'): LengthAwarePaginator
    {
        $search = $request->get('search', '');
        $filters = $request->only($this->queryConfig['filterable_columns']);
        $perPage = $request->get('per_page', $this->queryConfig['per_page']);
        
        // Configurar eager loading inteligente
        $eagerLoadRelations = $this->configureEagerLoading($context, $relations);
        
        $modelClass = $this->getResolvedModelClass();
        $query = $modelClass::query();
        
        // Aplicar eager loading se houver relacionamentos
        if (!empty($eagerLoadRelations)) {
            $query->with($eagerLoadRelations);
        }
        
        // Aplicar busca
        if (!empty($search)) {
            $query->where(function (Builder $q) use ($search) {
                foreach ($this->queryConfig['searchable_columns'] as $column) {
                    $q->orWhere($column, 'like', "%{$search}%");
                }
            });
        }
        
        // Aplicar filtros
        foreach ($filters as $column => $value) {
            if ($value !== null && $value !== '') {
                if (is_array($value)) {
                    $query->whereIn($column, $value);
                } else {
                    $query->where($column, $value);
                }
            }
        }
        
        $result = $query->orderBy($this->queryConfig['order_by'], $this->queryConfig['order_direction'])
                       ->paginate($perPage);
        
        // Log para debugging (apenas em desenvolvimento)
        if (config('app.debug')) {
            Log::debug("Papa Leguas Query processada", [
                'model' => $modelClass,
                'context' => $context,
                'eager_relations' => $eagerLoadRelations,
                'search' => $search,
                'filters' => $filters,
                'total' => $result->total()
            ]);
        }
        
        return $result;
    }

    /**
     * Configura opções de query
     * 
     * @param array $config
     * @return $this
     */
    public function configureQuery(array $config): static
    {
        $this->queryConfig = array_merge($this->queryConfig, $config);
        return $this;
    }

    /**
     * Obtém configurações de query
     * 
     * @return array
     */
    public function getQueryConfig(): array
    {
        return $this->queryConfig;
    }

    /**
     * Configura relacionamentos específicos para um contexto
     * 
     * @param string $context
     * @param array $relations
     * @return $this
     */
    public function setEagerLoadForContext(string $context, array $relations): static
    {
        $this->queryConfig['eager_load_context'][$context] = $relations;
        return $this;
    }

    /**
     * Adiciona relacionamento a um contexto específico
     * 
     * @param string $context
     * @param string $relation
     * @return $this
     */
    public function addEagerLoadToContext(string $context, string $relation): static
    {
        if (!isset($this->queryConfig['eager_load_context'][$context])) {
            $this->queryConfig['eager_load_context'][$context] = [];
        }
        
        if (!in_array($relation, $this->queryConfig['eager_load_context'][$context])) {
            $this->queryConfig['eager_load_context'][$context][] = $relation;
        }
        
        return $this;
    }

    /**
     * Remove relacionamento de um contexto específico
     * 
     * @param string $context
     * @param string $relation
     * @return $this
     */
    public function removeEagerLoadFromContext(string $context, string $relation): static
    {
        if (isset($this->queryConfig['eager_load_context'][$context])) {
            $this->queryConfig['eager_load_context'][$context] = array_filter(
                $this->queryConfig['eager_load_context'][$context],
                fn($r) => $r !== $relation
            );
        }
        
        return $this;
    }

    /**
     * Habilita ou desabilita a auto-detecção de relacionamentos
     * 
     * @param bool $enabled
     * @return $this
     */
    public function setAutoDetectRelations(bool $enabled): static
    {
        $this->queryConfig['auto_detect_relations'] = $enabled;
        return $this;
    }

    /**
     * Limpa o cache de relacionamentos detectados
     * 
     * @return $this
     */
    public function clearRelationsCache(): static
    {
        $this->detectedRelations = null;
        
        $modelClass = $this->getResolvedModelClass();
        if ($modelClass) {
            $cacheKey = "papa_leguas_relations_{$modelClass}";
            Cache::forget($cacheKey);
        }
        
        return $this;
    }

    /**
     * Obtém informações sobre os relacionamentos detectados
     * 
     * @return array
     */
    public function getDetectedRelationsInfo(): array
    {
        return $this->detectModelRelationships();
    }

    /**
     * Verifica se um relacionamento específico foi detectado
     * 
     * @param string $relationName
     * @return bool
     */
    public function hasDetectedRelation(string $relationName): bool
    {
        $relations = $this->detectModelRelationships();
        return isset($relations[$relationName]);
    }

    /**
     * Obtém informações sobre um relacionamento específico
     * 
     * @param string $relationName
     * @return array|null
     */
    public function getRelationInfo(string $relationName): ?array
    {
        $relations = $this->detectModelRelationships();
        return $relations[$relationName] ?? null;
    }
} 
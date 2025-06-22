<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model; 
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

trait BelongsToModel
{
    use \Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;

    /**
     * Array de relacionamentos belongsTo configurados
     * 
     * @var array<string, array>
     */
    protected array $belongsToRelations = [];

    /**
     * Array de configurações de pesquisa para colunas relacionadas
     * 
     * @var array<string, array>
     */
    protected array $relatedSearchConfig = [];

    /**
     * Array de configurações de ordenação para colunas relacionadas
     * 
     * @var array<string, array>
     */
    protected array $relatedSortConfig = [];

    /**
     * Cache de relacionamentos auto-detectados e configurados
     * 
     * @var array|null
     */
    protected ?array $autoConfiguredRelations = null;

    /**
     * Flag para indicar se auto-configuração já foi executada
     * 
     * @var bool
     */
    protected bool $autoConfigurationExecuted = false;

    /**
     * Define um relacionamento belongsTo com configurações avançadas
     * 
     * @param string $relation Nome do relacionamento
     * @param string $modelClass Classe do modelo relacionado
     * @param string $foreignKey Chave estrangeira (opcional)
     * @param array $config Configurações adicionais
     * @return $this
     */
    public function belongsToModel(string $relation, string $modelClass, string $foreignKey = null, array $config = []): static
    {
        $this->belongsToRelations[$relation] = [
            'model' => $modelClass,
            'foreign_key' => $foreignKey ?? Str::snake($relation) . '_id',
            'config' => $config,
        ];

        return $this;
    }

    /**
     * Auto-configura relacionamentos baseado na detecção do ModelQueries
     * 
     * @param bool $force Forçar reconfiguração mesmo se já executada
     * @return $this
     */
    public function autoConfigureBelongsToRelations(bool $force = false): static
    {
        if ($this->autoConfigurationExecuted && !$force) {
            return $this;
        }

        // Verificar se o trait ModelQueries está disponível
        if (!method_exists($this, 'detectModelRelationships')) {
            return $this;
        }

        try {
            $detectedRelations = $this->detectModelRelationships();
            
            foreach ($detectedRelations as $relationName => $relationInfo) {
                // Configurar apenas relacionamentos BelongsTo
                if ($relationInfo['type'] === 'BelongsTo') {
                    // Se não foi configurado manualmente, auto-configurar
                    if (!isset($this->belongsToRelations[$relationName])) {
                        $this->belongsToModel(
                            $relationName,
                            $relationInfo['related_model'],
                            $this->guessForeignKey($relationName),
                            ['auto_configured' => true]
                        );

                        // Auto-configurar pesquisa se possível
                        $this->autoConfigureSearchForRelation($relationName, $relationInfo);

                        // Auto-configurar ordenação se possível
                        $this->autoConfigureSortForRelation($relationName, $relationInfo);
                    }
                }
            }

            $this->autoConfiguredRelations = $detectedRelations;
            $this->autoConfigurationExecuted = true;

            // Log da auto-configuração em desenvolvimento
            if (config('app.debug') && method_exists($this, 'resolveModelClass')) {
                Log::info('BelongsToModel: Auto-configuração executada', [
                    'model' => $this->resolveModelClass(),
                    'detected_relations' => array_keys($detectedRelations),
                    'configured_belongs_to' => array_keys(array_filter($detectedRelations, fn($r) => $r['type'] === 'BelongsTo')),
                ]);
            }

        } catch (\Exception $e) {
            Log::warning('Erro na auto-configuração de relacionamentos BelongsTo', [
                'model' => method_exists($this, 'resolveModelClass') ? $this->resolveModelClass() : static::class,
                'error' => $e->getMessage()
            ]);
        }

        return $this;
    }

    /**
     * Auto-configura pesquisa para um relacionamento
     * 
     * @param string $relationName
     * @param array $relationInfo
     * @return void
     */
    protected function autoConfigureSearchForRelation(string $relationName, array $relationInfo): void
    {
        // Tentar detectar colunas comuns para pesquisa
        $searchColumns = $this->guessSearchableColumns($relationInfo['related_model']);
        
        if (!empty($searchColumns)) {
            // Usar a primeira coluna encontrada
            $this->configureRelatedSearch($relationName, $searchColumns[0], [
                'auto_configured' => true,
            ]);
        }
    }

    /**
     * Auto-configura ordenação para um relacionamento
     * 
     * @param string $relationName
     * @param array $relationInfo
     * @return void
     */
    protected function autoConfigureSortForRelation(string $relationName, array $relationInfo): void
    {
        // Tentar detectar colunas comuns para ordenação
        $sortColumns = $this->guessSortableColumns($relationInfo['related_model']);
        
        if (!empty($sortColumns)) {
            // Usar a primeira coluna encontrada
            $this->configureRelatedSort($relationName, $sortColumns[0], [
                'auto_configured' => true,
                'direction' => 'asc',
            ]);
        }
    }

    /**
     * Detecta colunas possíveis para pesquisa em um modelo
     * 
     * @param string $modelClass
     * @return array
     */
    protected function guessSearchableColumns(string $modelClass): array
    {
        try {
            $model = new $modelClass();
            $fillable = $model->getFillable();
            
            // Priorizar colunas comuns para pesquisa
            $searchablePriority = ['name', 'title', 'email', 'description', 'slug'];
            $searchableColumns = [];
            
            foreach ($searchablePriority as $column) {
                if (in_array($column, $fillable)) {
                    $searchableColumns[] = $column;
                }
            }
            
            return $searchableColumns;
            
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Detecta colunas possíveis para ordenação em um modelo
     * 
     * @param string $modelClass
     * @return array
     */
    protected function guessSortableColumns(string $modelClass): array
    {
        try {
            $model = new $modelClass();
            $fillable = $model->getFillable();
            
            // Priorizar colunas comuns para ordenação
            $sortablePriority = ['name', 'title', 'sort_order', 'created_at'];
            $sortableColumns = [];
            
            foreach ($sortablePriority as $column) {
                if (in_array($column, $fillable) || $column === 'created_at') {
                    $sortableColumns[] = $column;
                }
            }
            
            return $sortableColumns;
            
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Detecta chave estrangeira baseada no nome do relacionamento
     * 
     * @param string $relationName
     * @return string
     */
    protected function guessForeignKey(string $relationName): string
    {
        // Casos especiais comuns
        $specialCases = [
            'assignee' => 'assigned_to',
            'author' => 'author_id',
            'creator' => 'created_by',
            'updater' => 'updated_by',
        ];

        if (isset($specialCases[$relationName])) {
            return $specialCases[$relationName];
        }

        // Padrão: snake_case do nome + '_id'
        return Str::snake($relationName) . '_id';
    }

    /**
     * Obtém o modelo relacionado
     * 
     * @param string $relation Nome do relacionamento
     * @return Model|null
     */
    public function getRelatedModel(string $relation): ?Model
    {
        if (!isset($this->belongsToRelations[$relation])) {
            return null;
        }

        $config = $this->belongsToRelations[$relation];
        $foreignKey = $config['foreign_key'];

        if (!$this->$foreignKey) {
            return null;
        }

        return app($config['model'])->find($this->$foreignKey);
    }

    /**
     * Obtém dados formatados do modelo relacionado
     * 
     * @param string $relation Nome do relacionamento
     * @param string|Closure $format Campo ou callback para formatação
     * @param mixed $default Valor padrão se não encontrar
     * @return mixed
     */
    public function getRelatedData(string $relation, string|Closure $format = 'name', mixed $default = null): mixed
    {
        $relatedModel = $this->getRelatedModel($relation);

        if (!$relatedModel) {
            return $this->evaluate($default);
        }

        if ($format instanceof Closure) {
            return $this->evaluate($format, ['model' => $relatedModel]);
        }

        return $relatedModel->$format ?? $default;
    }

    /**
     * Verifica se tem modelo relacionado
     * 
     * @param string $relation Nome do relacionamento
     * @return bool
     */
    public function hasRelated(string $relation): bool
    {
        return $this->getRelatedModel($relation) !== null;
    }

    /**
     * Configura pesquisa para coluna relacionada
     * 
     * @param string $relation Nome do relacionamento
     * @param string $column Coluna no modelo relacionado
     * @param array $config Configurações de pesquisa
     * @return $this
     */
    public function configureRelatedSearch(string $relation, string $column, array $config = []): static
    {
        $this->relatedSearchConfig[$relation] = [
            'column' => $column,
            'config' => array_merge([
                'operator' => 'like',
                'case_sensitive' => false,
            ], $config),
        ];

        return $this;
    }

    /**
     * Configura ordenação para coluna relacionada
     * 
     * @param string $relation Nome do relacionamento
     * @param string $column Coluna no modelo relacionado
     * @param array $config Configurações de ordenação
     * @return $this
     */
    public function configureRelatedSort(string $relation, string $column, array $config = []): static
    {
        $this->relatedSortConfig[$relation] = [
            'column' => $column,
            'config' => array_merge([
                'direction' => 'asc',
                'nulls_last' => true,
            ], $config),
        ];

        return $this;
    }

    /**
     * Aplica pesquisa em colunas relacionadas
     * 
     * @param Builder $query Query builder
     * @param string $search Termo de pesquisa
     * @return Builder
     */
    public function scopeSearchRelated(Builder $query, string $search): Builder
    {
        foreach ($this->relatedSearchConfig as $relation => $config) {
            if (!isset($this->belongsToRelations[$relation])) {
                continue;
            }

            $relationConfig = $this->belongsToRelations[$relation];
            $modelClass = $relationConfig['model'];
            $foreignKey = $relationConfig['foreign_key'];

            $query->whereHas($relation, function (Builder $subQuery) use ($config, $search) {
                $column = $config['column'];
                $operator = $config['config']['operator'];
                $caseSensitive = $config['config']['case_sensitive'];

                if ($operator === 'like') {
                    $searchTerm = $caseSensitive ? "%{$search}%" : "%" . Str::lower($search) . "%";
                    $subQuery->whereRaw($caseSensitive ? "LOWER({$column}) LIKE ?" : "{$column} LIKE ?", [$searchTerm]);
                } else {
                    $subQuery->where($column, $operator, $search);
                }
            });
        }

        return $query;
    }

    /**
     * Aplica ordenação em colunas relacionadas
     * 
     * @param Builder $query Query builder
     * @param string $relation Nome do relacionamento
     * @param string $direction Direção da ordenação
     * @return Builder
     */
    public function scopeOrderByRelated(Builder $query, string $relation, string $direction = 'asc'): Builder
    {
        if (!isset($this->relatedSortConfig[$relation]) || !isset($this->belongsToRelations[$relation])) {
            return $query;
        }

        $sortConfig = $this->relatedSortConfig[$relation];
        $relationConfig = $this->belongsToRelations[$relation];
        $column = $sortConfig['column'];
        $foreignKey = $relationConfig['foreign_key'];
        $nullsLast = $sortConfig['config']['nulls_last'];

        $modelClass = $relationConfig['model'];
        $tableName = (new $modelClass)->getTable();

        $query->leftJoin($tableName, "{$this->getTable()}.{$foreignKey}", '=', "{$tableName}.id")
              ->orderBy("{$tableName}.{$column}", $direction);

        if ($nullsLast) {
            $query->orderByRaw("CASE WHEN {$tableName}.{$column} IS NULL THEN 1 ELSE 0 END");
        }

        return $query;
    }

    /**
     * Executa callback em dados relacionados
     * 
     * @param string $relation Nome do relacionamento
     * @param Closure $callback Callback a ser executado
     * @param mixed $default Valor padrão se não encontrar
     * @return mixed
     */
    public function evaluateRelated(string $relation, Closure $callback, mixed $default = null): mixed
    {
        $relatedModel = $this->getRelatedModel($relation);

        if (!$relatedModel) {
            return $this->evaluate($default);
        }

        return $this->evaluate($callback, ['model' => $relatedModel]);
    }

    /**
     * Obtém dados relacionados formatados usando callback
     * 
     * @param string $relation Nome do relacionamento
     * @param Closure $formatter Callback para formatação
     * @param mixed $default Valor padrão se não encontrar
     * @return mixed
     */
    public function formatRelatedData(string $relation, Closure $formatter, mixed $default = null): mixed
    {
        return $this->evaluateRelated($relation, $formatter, $default);
    }

    /**
     * Obtém todos os relacionamentos configurados
     * 
     * @return array
     */
    public function getBelongsToRelations(): array
    {
        return $this->belongsToRelations;
    }

    /**
     * Obtém configurações de pesquisa relacionada
     * 
     * @return array
     */
    public function getRelatedSearchConfig(): array
    {
        return $this->relatedSearchConfig;
    }

    /**
     * Obtém configurações de ordenação relacionada
     * 
     * @return array
     */
    public function getRelatedSortConfig(): array
    {
        return $this->relatedSortConfig;
    }

    /**
     * Carrega relacionamentos para múltiplos modelos
     * 
     * @param array $models Array de modelos
     * @param array $relations Relacionamentos para carregar
     * @return array
     */
    public function loadRelatedForModels(array $models, array $relations = []): array
    {
        if (empty($models)) {
            return $models;
        }

        $relations = empty($relations) ? array_keys($this->belongsToRelations) : $relations;
        
        // Agrupar modelos por tipo para otimizar queries
        $groupedModels = [];
        foreach ($models as $model) {
            $modelClass = get_class($model);
            $groupedModels[$modelClass][] = $model;
        }

        // Carregar relacionamentos para cada grupo
        foreach ($groupedModels as $modelClass => $modelGroup) {
            $modelClass::with($relations)->whereIn('id', collect($modelGroup)->pluck('id'))->get();
        }

        return $models;
    }

    /**
     * Obtém dados relacionados em lote
     * 
     * @param array $models Array de modelos
     * @param string $relation Nome do relacionamento
     * @param string $column Coluna para extrair
     * @return array
     */
    public function getRelatedDataBatch(array $models, string $relation, string $column = 'name'): array
    {
        $result = [];
        
        foreach ($models as $model) {
            $result[$model->id] = $this->getRelatedData($relation, $column, 'N/A');
        }
        
        return $result;
    }

    /**
     * Executa auto-configuração se ainda não foi executada
     * 
     * @return $this
     */
    public function ensureAutoConfiguration(): static
    {
        if (!$this->autoConfigurationExecuted) {
            $this->autoConfigureBelongsToRelations();
        }
        
        return $this;
    }

    /**
     * Obtém relacionamentos BelongsTo auto-configurados
     * 
     * @return array
     */
    public function getAutoConfiguredRelations(): array
    {
        $this->ensureAutoConfiguration();
        
        return array_filter($this->belongsToRelations, function ($config) {
            return isset($config['config']['auto_configured']) && $config['config']['auto_configured'];
        });
    }

    /**
     * Obtém relacionamentos BelongsTo configurados manualmente
     * 
     * @return array
     */
    public function getManuallyConfiguredRelations(): array
    {
        return array_filter($this->belongsToRelations, function ($config) {
            return !isset($config['config']['auto_configured']) || !$config['config']['auto_configured'];
        });
    }

    /**
     * Verifica se um relacionamento foi auto-configurado
     * 
     * @param string $relation
     * @return bool
     */
    public function isAutoConfiguredRelation(string $relation): bool
    {
        if (!isset($this->belongsToRelations[$relation])) {
            return false;
        }
        
        $config = $this->belongsToRelations[$relation];
        return isset($config['config']['auto_configured']) && $config['config']['auto_configured'];
    }

    /**
     * Limpa configurações auto-detectadas e força nova detecção
     * 
     * @return $this
     */
    public function refreshAutoConfiguration(): static
    {
        // Remover relacionamentos auto-configurados
        $this->belongsToRelations = array_filter($this->belongsToRelations, function ($config) {
            return !isset($config['config']['auto_configured']) || !$config['config']['auto_configured'];
        });

        // Limpar configurações de pesquisa auto-configuradas
        $this->relatedSearchConfig = array_filter($this->relatedSearchConfig, function ($config) {
            return !isset($config['config']['auto_configured']) || !$config['config']['auto_configured'];
        });

        // Limpar configurações de ordenação auto-configuradas
        $this->relatedSortConfig = array_filter($this->relatedSortConfig, function ($config) {
            return !isset($config['config']['auto_configured']) || !$config['config']['auto_configured'];
        });

        // Resetar flags
        $this->autoConfiguredRelations = null;
        $this->autoConfigurationExecuted = false;

        // Forçar nova auto-configuração
        $this->autoConfigureBelongsToRelations(true);

        return $this;
    }

    /**
     * Obtém estatísticas dos relacionamentos configurados
     * 
     * @return array
     */
    public function getBelongsToStats(): array
    {
        $this->ensureAutoConfiguration();
        
        $autoConfigured = $this->getAutoConfiguredRelations();
        $manuallyConfigured = $this->getManuallyConfiguredRelations();
        
        return [
            'total_relations' => count($this->belongsToRelations),
            'auto_configured' => count($autoConfigured),
            'manually_configured' => count($manuallyConfigured),
            'search_configured' => count($this->relatedSearchConfig),
            'sort_configured' => count($this->relatedSortConfig),
            'relations_list' => [
                'auto' => array_keys($autoConfigured),
                'manual' => array_keys($manuallyConfigured),
                'searchable' => array_keys($this->relatedSearchConfig),
                'sortable' => array_keys($this->relatedSortConfig),
            ],
        ];
    }

    /**
     * Aplica busca em relacionamentos com melhor performance
     * 
     * @param Builder $query
     * @param string $search
     * @param array $relations Relacionamentos específicos para pesquisar (opcional)
     * @return Builder
     */
    public function scopeSearchRelatedOptimized(Builder $query, string $search, array $relations = []): Builder
    {
        $this->ensureAutoConfiguration();
        
        $searchConfigs = empty($relations) 
            ? $this->relatedSearchConfig 
            : array_intersect_key($this->relatedSearchConfig, array_flip($relations));

        if (empty($searchConfigs)) {
            return $query;
        }

        $query->where(function (Builder $mainQuery) use ($search, $searchConfigs) {
            foreach ($searchConfigs as $relation => $config) {
                if (!isset($this->belongsToRelations[$relation])) {
                    continue;
                }

                $mainQuery->orWhereHas($relation, function (Builder $subQuery) use ($config, $search) {
                    $column = $config['column'];
                    $operator = $config['config']['operator'];
                    $caseSensitive = $config['config']['case_sensitive'];

                    if ($operator === 'like') {
                        if ($caseSensitive) {
                            $subQuery->where($column, 'LIKE', "%{$search}%");
                        } else {
                            $subQuery->whereRaw("LOWER({$column}) LIKE ?", ['%' . strtolower($search) . '%']);
                        }
                    } else {
                        $subQuery->where($column, $operator, $search);
                    }
                });
            }
        });

        return $query;
    }

    /**
     * Aplica eager loading otimizado para relacionamentos BelongsTo
     * 
     * @param Builder $query
     * @param array $relations Relacionamentos específicos (opcional)
     * @return Builder
     */
    public function scopeWithBelongsToOptimized(Builder $query, array $relations = []): Builder
    {
        $this->ensureAutoConfiguration();
        
        $belongsToRelations = empty($relations) 
            ? array_keys($this->belongsToRelations)
            : array_intersect(array_keys($this->belongsToRelations), $relations);

        if (!empty($belongsToRelations)) {
            $query->with($belongsToRelations);
        }

        return $query;
    }

    /**
     * Obtém dados de relacionamentos para uso em selects/dropdowns
     * 
     * @param array $relations Relacionamentos específicos (opcional)
     * @param int $limit Limite de registros por relacionamento
     * @return array
     */
    public function getRelatedDataForSelects(array $relations = [], int $limit = 100): array
    {
        $this->ensureAutoConfiguration();
        
        $targetRelations = empty($relations) 
            ? array_keys($this->belongsToRelations)
            : array_intersect(array_keys($this->belongsToRelations), $relations);

        $result = [];

        foreach ($targetRelations as $relationName) {
            $relationConfig = $this->belongsToRelations[$relationName];
            $modelClass = $relationConfig['model'];

            try {
                // Detectar colunas para select
                $searchColumns = $this->guessSearchableColumns($modelClass);
                $selectColumns = ['id'];
                
                if (!empty($searchColumns)) {
                    $selectColumns[] = $searchColumns[0]; // Primeira coluna como 'name'
                }

                $relatedData = $modelClass::select($selectColumns)
                    ->limit($limit)
                    ->get()
                    ->map(function ($item) use ($searchColumns) {
                        $nameColumn = $searchColumns[0] ?? 'id';
                        return [
                            'id' => $item->id,
                            'name' => $item->$nameColumn ?? "#{$item->id}",
                        ];
                    });

                $result[$relationName] = $relatedData;

            } catch (\Exception $e) {
                Log::warning("Erro ao carregar dados para select", [
                    'relation' => $relationName,
                    'model' => $modelClass,
                    'error' => $e->getMessage()
                ]);
                
                $result[$relationName] = [];
            }
        }

        return $result;
    }
}

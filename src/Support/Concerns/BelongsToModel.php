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

trait BelongsToModel
{

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
}

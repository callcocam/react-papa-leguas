<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filtro específico para relacionamentos (React Frontend)
 */
class RelationFilter extends Filter
{
    /**
     * Tipo do filtro
     */
    protected string $type = 'relation';

    /**
     * Nome do relacionamento
     */
    protected string $relationName;

    /**
     * Campo do relacionamento para filtrar
     */
    protected string $relationField = 'id';

    /**
     * Campo para exibição
     */
    protected string $displayField = 'name';

    /**
     * Tipo de relacionamento
     */
    protected string $relationType = 'belongsTo';

    /**
     * Definir relacionamento
     */
    public function relationship(string $name, string $field = 'id', string $displayField = 'name'): static
    {
        $this->relationName = $name;
        $this->relationField = $field;
        $this->displayField = $displayField;
        return $this;
    }

    /**
     * Definir tipo de relacionamento
     */
    public function relationType(string $type): static
    {
        $this->relationType = $type;
        return $this;
    }

    /**
     * Filtro de categoria
     */
    public static function category(string $model = 'App\\Models\\Category'): static
    {
        return static::make('category')
            ->label('Categoria')
            ->relationship('category', 'id', 'name')
            ->relationType('belongsTo')
            ->placeholder('Selecione uma categoria...')
            ->icon('Tag')
            ->searchable()
            ->reactConfig([
                'component' => 'CategoryRelationFilter',
                'model' => $model,
                'remote' => true,
                'searchEndpoint' => '/api/categories/search',
                'showHierarchy' => true,
                'hierarchyField' => 'parent_id',
            ]);
    }

    /**
     * Filtro de usuário
     */
    public static function user(string $model = 'App\\Models\\User'): static
    {
        return static::make('user')
            ->label('Usuário')
            ->relationship('user', 'id', 'name')
            ->relationType('belongsTo')
            ->placeholder('Selecione um usuário...')
            ->icon('User')
            ->searchable()
            ->reactConfig([
                'component' => 'UserRelationFilter',
                'model' => $model,
                'remote' => true,
                'searchEndpoint' => '/api/users/search',
                'showAvatars' => true,
                'avatarField' => 'avatar',
                'showStatus' => true,
                'statusField' => 'status',
            ]);
    }

    /**
     * Filtro de tags (BelongsToMany)
     */
    public static function tags(string $model = 'App\\Models\\Tag'): static
    {
        return static::make('tags')
            ->label('Tags')
            ->relationship('tags', 'id', 'name')
            ->relationType('belongsToMany')
            ->placeholder('Selecione as tags...')
            ->icon('Tag')
            ->multiple()
            ->searchable()
            ->reactConfig([
                'component' => 'TagsRelationFilter',
                'model' => $model,
                'remote' => true,
                'searchEndpoint' => '/api/tags/search',
                'creatable' => true,
                'colorful' => true,
                'colorField' => 'color',
                'maxSelected' => 10,
            ]);
    }

    /**
     * Filtro de autor
     */
    public static function author(string $model = 'App\\Models\\User'): static
    {
        return static::make('author')
            ->label('Autor')
            ->relationship('author', 'id', 'name')
            ->relationType('belongsTo')
            ->placeholder('Selecione um autor...')
            ->icon('User')
            ->searchable()
            ->reactConfig([
                'component' => 'AuthorRelationFilter',
                'model' => $model,
                'remote' => true,
                'searchEndpoint' => '/api/authors/search',
                'showAvatars' => true,
                'showPostCount' => true,
                'showLastActivity' => true,
            ]);
    }

    /**
     * Filtro de departamento
     */
    public static function department(string $model = 'App\\Models\\Department'): static
    {
        return static::make('department')
            ->label('Departamento')
            ->relationship('department', 'id', 'name')
            ->relationType('belongsTo')
            ->placeholder('Selecione um departamento...')
            ->icon('Building')
            ->searchable()
            ->reactConfig([
                'component' => 'DepartmentRelationFilter',
                'model' => $model,
                'remote' => true,
                'searchEndpoint' => '/api/departments/search',
                'showHierarchy' => true,
                'showEmployeeCount' => true,
            ]);
    }

    /**
     * Filtro de produtos (HasMany)
     */
    public static function products(string $model = 'App\\Models\\Product'): static
    {
        return static::make('products')
            ->label('Produtos')
            ->relationship('products', 'id', 'name')
            ->relationType('hasMany')
            ->placeholder('Selecione os produtos...')
            ->icon('Package')
            ->multiple()
            ->searchable()
            ->reactConfig([
                'component' => 'ProductsRelationFilter',
                'model' => $model,
                'remote' => true,
                'searchEndpoint' => '/api/products/search',
                'showImages' => true,
                'showPrice' => true,
                'showStock' => true,
                'groupByCategory' => true,
            ]);
    }

    /**
     * Filtro de relacionamento personalizado
     */
    public static function custom(string $relation, string $model, string $displayField = 'name'): static
    {
        return static::make($relation)
            ->label(ucfirst($relation))
            ->relationship($relation, 'id', $displayField)
            ->placeholder("Selecione {$relation}...")
            ->icon('Link')
            ->searchable()
            ->reactConfig([
                'component' => 'CustomRelationFilter',
                'model' => $model,
                'remote' => true,
                'searchEndpoint' => "/api/{$relation}/search",
                'displayField' => $displayField,
            ]);
    }

    /**
     * Filtro com busca em múltiplos campos do relacionamento
     */
    public function searchIn(array $fields): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'searchFields' => $fields,
            'multiFieldSearch' => true,
        ]);
        return $this;
    }

    /**
     * Filtro com agrupamento
     */
    public function groupBy(string $field): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'groupBy' => $field,
            'showGroups' => true,
        ]);
        return $this;
    }

    /**
     * Filtro com hierarquia (para categorias, departamentos, etc.)
     */
    public function hierarchical(string $parentField = 'parent_id'): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'hierarchical' => true,
            'parentField' => $parentField,
            'showHierarchy' => true,
            'expandable' => true,
        ]);
        return $this;
    }

    /**
     * Aplicar filtro à query
     */
    protected function applyFilter(Builder $query, mixed $value): Builder
    {
        if (!$this->relationName) {
            return $query;
        }

        return match($this->relationType) {
            'belongsTo' => $this->applyBelongsToFilter($query, $value),
            'hasMany', 'hasOne' => $this->applyHasFilter($query, $value),
            'belongsToMany' => $this->applyBelongsToManyFilter($query, $value),
            'morphMany', 'morphOne' => $this->applyMorphFilter($query, $value),
            default => $query,
        };
    }

    /**
     * Aplicar filtro BelongsTo
     */
    protected function applyBelongsToFilter(Builder $query, mixed $value): Builder
    {
        if (is_array($value)) {
            return $query->whereIn("{$this->relationName}_{$this->relationField}", $value);
        }

        return $query->where("{$this->relationName}_{$this->relationField}", $value);
    }

    /**
     * Aplicar filtro Has (HasMany, HasOne)
     */
    protected function applyHasFilter(Builder $query, mixed $value): Builder
    {
        return $query->whereHas($this->relationName, function (Builder $q) use ($value) {
            if (is_array($value)) {
                $q->whereIn($this->relationField, $value);
            } else {
                $q->where($this->relationField, $value);
            }
        });
    }

    /**
     * Aplicar filtro BelongsToMany
     */
    protected function applyBelongsToManyFilter(Builder $query, mixed $value): Builder
    {
        return $query->whereHas($this->relationName, function (Builder $q) use ($value) {
            if (is_array($value)) {
                $q->whereIn($this->relationField, $value);
            } else {
                $q->where($this->relationField, $value);
            }
        });
    }

    /**
     * Aplicar filtro Morph
     */
    protected function applyMorphFilter(Builder $query, mixed $value): Builder
    {
        return $query->whereHasMorph($this->relationName, '*', function (Builder $q) use ($value) {
            if (is_array($value)) {
                $q->whereIn($this->relationField, $value);
            } else {
                $q->where($this->relationField, $value);
            }
        });
    }

    /**
     * Converter para array (com configurações React)
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        return array_merge($data, [
            'relationName' => $this->relationName,
            'relationField' => $this->relationField,
            'displayField' => $this->displayField,
            'relationType' => $this->relationType,
            'frontend' => [
                'component' => 'RelationFilter',
                'type' => $this->type,
                'config' => array_merge($this->reactConfig, [
                    'relationName' => $this->relationName,
                    'relationField' => $this->relationField,
                    'displayField' => $this->displayField,
                    'relationType' => $this->relationType,
                ]),
            ],
        ]);
    }
} 
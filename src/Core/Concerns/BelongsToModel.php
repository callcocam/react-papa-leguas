<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Model;

trait BelongsToModel
{
    /**
     * The model class for the component.
     *
     * @var string|null
     */
    protected ?string $modelClass = null;

    /**
     * The model instance for the component.
     *
     * @var Model|null
     */
    protected ?Model $model = null;

    /**
     * The model relationship to load.
     *
     * @var array
     */
    protected array $modelWith = [];

    /**
     * The model attributes to append.
     *
     * @var array
     */
    protected array $modelAppends = [];

    /**
     * Set the model class for the component.
     *
     * @param string $modelClass
     * @return $this
     */
    public function model(string $modelClass): static
    {
        $this->modelClass = $modelClass;

        return $this;
    }

    /**
     * Set the model instance for the component.
     *
     * @param Model $model
     * @return $this
     */
    public function record(Model $model): static
    {
        $this->model = $model;
        $this->modelClass = get_class($model);

        return $this;
    }

    /**
     * Set the relationships to eager load.
     *
     * @param array|string $relations
     * @return $this
     */
    public function with(array|string $relations): static
    {
        $this->modelWith = is_string($relations) ? [$relations] : $relations;

        return $this;
    }

    /**
     * Set the attributes to append to the model.
     *
     * @param array|string $attributes
     * @return $this
     */
    public function appends(array|string $attributes): static
    {
        $this->modelAppends = is_string($attributes) ? [$attributes] : $attributes;

        return $this;
    }

    /**
     * Get the model class for the component.
     *
     * @return string|null
     */
    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    /**
     * Get the model instance for the component.
     *
     * @return Model|null
     */
    public function getModel(): ?Model
    {
        return $this->model;
    }

    /**
     * Get a new instance of the model.
     *
     * @return Model|null
     */
    public function getNewModelInstance(): ?Model
    {
        if (!$this->modelClass) {
            return null;
        }

        return new $this->modelClass;
    }

    /**
     * Get the model query builder.
     *
     * @return \Illuminate\Database\Eloquent\Builder|null
     */
    public function getModelQuery(): ?\Illuminate\Database\Eloquent\Builder
    {
        if (!$this->modelClass) {
            return null;
        }

        $query = $this->modelClass::query();

        if (!empty($this->modelWith)) {
            $query->with($this->modelWith);
        }

        return $query;
    }

    /**
     * Get the relationships to eager load.
     *
     * @return array
     */
    public function getModelWith(): array
    {
        return $this->modelWith;
    }

    /**
     * Get the attributes to append.
     *
     * @return array
     */
    public function getModelAppends(): array
    {
        return $this->modelAppends;
    }

    /**
     * Get the model's table name.
     *
     * @return string|null
     */
    public function getModelTable(): ?string
    {
        if (!$this->modelClass) {
            return null;
        }

        return (new $this->modelClass)->getTable();
    }

    /**
     * Get the model's primary key.
     *
     * @return string|null
     */
    public function getModelKeyName(): ?string
    {
        if (!$this->modelClass) {
            return null;
        }

        return (new $this->modelClass)->getKeyName();
    }

    /**
     * Get the model's fillable attributes.
     *
     * @return array
     */
    public function getModelFillable(): array
    {
        if (!$this->modelClass) {
            return [];
        }

        return (new $this->modelClass)->getFillable();
    }

    /**
     * Get the model's casts.
     *
     * @return array
     */
    public function getModelCasts(): array
    {
        if (!$this->modelClass) {
            return [];
        }

        return (new $this->modelClass)->getCasts();
    }
}

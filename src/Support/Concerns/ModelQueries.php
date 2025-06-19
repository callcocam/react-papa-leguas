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
    ];

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
     * Obtém registro por ID
     * 
     * @param string $id
     * @param array $relations
     * @return Model|null
     */
    public function findById(string $id, array $relations = []): ?Model
    {
        $modelClass = $this->getResolvedModelClass();
        
        if (empty($relations)) {
            return $modelClass::find($id);
        }
        
        return $modelClass::with($relations)->find($id);
    }

    /**
     * Obtém registro por ID ou falha
     * 
     * @param string $id
     * @param array $relations
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function findByIdOrFail(string $id, array $relations = []): Model
    {
        $modelClass = $this->getResolvedModelClass();
        
        if (empty($relations)) {
            return $modelClass::findOrFail($id);
        }
        
        return $modelClass::with($relations)->findOrFail($id);
    }

    /**
     * Cria novo registro
     * 
     * @param array $data
     * @return Model
     */
    public function create(array $data): Model
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
    public function update(string $id, array $data): Model
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
    public function delete(string $id): bool
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
    public function deleteMultiple(array $ids): int
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
     * Processa requisição e retorna dados paginados
     * 
     * @param Request $request
     * @param array $relations
     * @return LengthAwarePaginator
     */
    public function processRequest(Request $request, array $relations = []): LengthAwarePaginator
    {
        $search = $request->get('search', '');
        $filters = $request->only($this->queryConfig['filterable_columns']);
        $perPage = $request->get('per_page', $this->queryConfig['per_page']);
        $relations = empty($relations) ? $this->queryConfig['with_relations'] : $relations;
        
        $modelClass = $this->getResolvedModelClass();
        $query = $modelClass::with($relations);
        
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
        
        return $query->orderBy($this->queryConfig['order_by'], $this->queryConfig['order_direction'])
                    ->paginate($perPage);
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
} 
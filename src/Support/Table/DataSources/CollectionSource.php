<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\DataSources;

use Callcocam\ReactPapaLeguas\Support\Table\DataSources\DataSource;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Closure;

class CollectionSource extends DataSource
{
    protected Collection $collection;
    protected ?Closure $dataCallback = null;

    public function __construct(Collection|array|Closure $data, array $config = [])
    {
        parent::__construct($config);
        
        if ($data instanceof Closure) {
            $this->dataCallback = $data;
            $this->collection = collect();
        } elseif (is_array($data)) {
            $this->collection = collect($data);
        } else {
            $this->collection = $data;
        }
    }

    /**
     * Definir callback para obter dados dinamicamente
     */
    public function data(Closure $callback): static
    {
        $this->dataCallback = $callback;
        $this->clearCache();
        return $this;
    }

    /**
     * Obter dados da fonte
     */
    public function getData(): Collection
    {
        return $this->getCachedData('getProcessedData');
    }

    /**
     * Obter dados paginados
     */
    public function getPaginatedData(int $page = 1, int $perPage = 15): LengthAwarePaginator
    {
        $data = $this->getProcessedData();
        return $this->paginateCollection($data, $page, $perPage);
    }

    /**
     * Contar total de registros
     */
    public function count(): int
    {
        return $this->getProcessedData()->count();
    }



    /**
     * Obter tipo da fonte de dados
     */
    public function getType(): string
    {
        return 'collection';
    }

    /**
     * Verificar se a fonte está disponível
     */
    public function isAvailable(): bool
    {
        try {
            $data = $this->getRawData();
            return $data instanceof Collection;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obter configuração padrão da fonte
     */
    protected function getDefaultConfig(): array
    {
        return array_merge(parent::getDefaultConfig(), [
            'cache_enabled' => false, // Collections são geralmente em memória
            'cache_ttl' => 60, // Cache menor para collections
            'transform_items' => null, // Callback para transformar cada item
        ]);
    }

    /**
     * Obter dados brutos da collection
     */
    protected function getRawData(): Collection
    {
        if ($this->dataCallback) {
            $result = $this->evaluate($this->dataCallback);
            
            if (is_array($result)) {
                return collect($result);
            }
            
            if ($result instanceof Collection) {
                return $result;
            }
            
            throw new \InvalidArgumentException(
                'Data callback deve retornar array ou Collection'
            );
        }

        return $this->collection;
    }

    /**
     * Obter dados processados (com filtros, busca e ordenação)
     */
    protected function getProcessedData(): Collection
    {
        $data = $this->getRawData();
        
        // Aplicar transformação de itens se configurado
        if ($this->config['transform_items']) {
            $data = $data->map($this->config['transform_items']);
        }
        
        // Aplicar filtros
        $data = $this->applyFiltersToCollection($data);
        
        // Aplicar busca
        $data = $this->applySearchToCollection($data);
        
        // Aplicar ordenação
        $data = $this->applySortingToCollection($data);
        
        return $data;
    }

    /**
     * Adicionar item à collection
     */
    public function add($item): static
    {
        if ($this->dataCallback) {
            throw new \RuntimeException(
                'Não é possível adicionar itens quando usando callback de dados'
            );
        }
        
        $this->collection->push($item);
        $this->clearCache();
        return $this;
    }

    /**
     * Remover item da collection
     */
    public function remove($key): static
    {
        if ($this->dataCallback) {
            throw new \RuntimeException(
                'Não é possível remover itens quando usando callback de dados'
            );
        }
        
        $this->collection->forget($key);
        $this->clearCache();
        return $this;
    }

    /**
     * Limpar todos os dados da collection
     */
    public function clear(): static
    {
        if ($this->dataCallback) {
            throw new \RuntimeException(
                'Não é possível limpar dados quando usando callback de dados'
            );
        }
        
        $this->collection = collect();
        $this->clearCache();
        return $this;
    }

    /**
     * Definir transformação de itens
     */
    public function transform(Closure $callback): static
    {
        $this->config['transform_items'] = $callback;
        $this->clearCache();
        return $this;
    }

    /**
     * Obter informações de debug específicas da collection
     */
    public function getDebugInfo(): array
    {
        $baseInfo = parent::getDebugInfo();
        
        return array_merge($baseInfo, [
            'has_data_callback' => $this->dataCallback !== null,
            'raw_count' => $this->getRawData()->count(),
            'processed_count' => $this->getProcessedData()->count(),
            'has_transform' => isset($this->config['transform_items']),
            'sample_data' => $this->getRawData()->take(3)->toArray(),
        ]);
    }

    /**
     * Criar CollectionSource a partir de array
     */
    public static function fromArray(array $data, array $config = []): static
    {
        return new static(collect($data), $config);
    }

    /**
     * Criar CollectionSource a partir de callback
     */
    public static function fromCallback(Closure $callback, array $config = []): static
    {
        return new static($callback, $config);
    }

    /**
     * Criar CollectionSource vazia
     */
    public static function empty(array $config = []): static
    {
        return new static(collect(), $config);
    }
}
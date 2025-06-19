<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use ReflectionClass;
use Illuminate\Support\Facades\Cache;

trait ResolvesModel
{
    /**
     * Modelo resolvido automaticamente
     * 
     * @var string|null
     */
    protected ?string $resolvedModelClass = null;

    /**
     * Instância do modelo
     * 
     * @var Model|null
     */
    protected ?Model $modelInstance = null;

    /**
     * Define o modelo manualmente
     * 
     * @param string $modelClass
     * @return $this
     */
    public function model(string $modelClass): static
    {
        $this->resolvedModelClass = $modelClass;
        return $this;
    }

    /**
     * Resolve automaticamente o modelo baseado no nome do controller
     * 
     * @return string|null
     */
    public function resolveModelClass(): ?string
    {
        if ($this->resolvedModelClass) {
            return $this->resolvedModelClass;
        }

        $cacheKey = $this->getCacheKey();
        
        // Tentar buscar do cache primeiro
        if ($this->isCacheEnabled()) {
            $cached = Cache::get($cacheKey);
            if ($cached) {
                $this->resolvedModelClass = $cached;
                return $cached;
            }
        }

        // Tentar resolver usando diferentes estratégias
        $modelClass = $this->resolveModelUsingStrategies();
        
        if ($modelClass) {
            $this->resolvedModelClass = $modelClass;
            
            // Salvar no cache
            if ($this->isCacheEnabled()) {
                Cache::put($cacheKey, $modelClass, $this->getCacheTtl());
            }
            
            return $modelClass;
        }

        return null;
    }

    /**
     * Resolve modelo usando múltiplas estratégias
     * 
     * @return string|null
     */
    protected function resolveModelUsingStrategies(): ?string
    {
        $controllerName = class_basename($this);
        $namespace = $this->getControllerNamespace();

        // 1. Tentar mapeamento direto do controller
        $mapping = $this->getDirectMapping($controllerName);
        if ($mapping) {
            return $mapping;
        }

        // 2. Tentar mapeamento por namespace
        $namespaceMapping = $this->getNamespaceMapping($namespace, $controllerName);
        if ($namespaceMapping) {
            return $namespaceMapping;
        }

        // 3. Tentar auto-descoberta nos namespaces configurados
        if ($this->isAutoDiscoveryEnabled()) {
            $discovered = $this->discoverModelInNamespaces($controllerName);
            if ($discovered) {
                return $discovered;
            }
        }

        return null;
    }

    /**
     * Obtém mapeamento direto do controller
     * 
     * @param string $controllerName
     * @return string|null
     */
    protected function getDirectMapping(string $controllerName): ?string
    {
        $mappings = config('react-papa-leguas.resolves_model.mappings', []);
        return $mappings[$controllerName] ?? null;
    }

    /**
     * Obtém mapeamento por namespace
     * 
     * @param string $namespace
     * @param string $controllerName
     * @return string|null
     */
    protected function getNamespaceMapping(string $namespace, string $controllerName): ?string
    {
        $namespaceMappings = config('react-papa-leguas.resolves_model.namespace_mappings', []);
        
        if (!isset($namespaceMappings[$namespace])) {
            return null;
        }

        $modelName = $this->extractModelNameFromController($controllerName);
        return $namespaceMappings[$namespace][$modelName] ?? null;
    }

    /**
     * Descobre modelo nos namespaces configurados
     * 
     * @param string $controllerName
     * @return string|null
     */
    protected function discoverModelInNamespaces(string $controllerName): ?string
    {
        $modelName = $this->extractModelNameFromController($controllerName);
        $namespaces = config('react-papa-leguas.resolves_model.namespaces', []);

        foreach ($namespaces as $namespace) {
            $fullClassName = $namespace . '\\' . $modelName;
            
            if (class_exists($fullClassName)) {
                return $fullClassName;
            }
        }

        return null;
    }

    /**
     * Extrai o nome do modelo do nome do controller
     * 
     * @param string $controllerName
     * @return string
     */
    protected function extractModelNameFromController(string $controllerName): string
    {
        // Remove "Controller" do final
        $name = Str::replaceLast('Controller', '', $controllerName);
        
        // Remove prefixos comuns
        $name = Str::replaceFirst('Admin', '', $name);
        $name = Str::replaceFirst('Landlord', '', $name);
        
        // Converte para singular se estiver no plural
        return Str::singular($name);
    }

    /**
     * Obtém a instância do modelo
     * 
     * @return Model|null
     */
    public function getModelInstance(): ?Model
    {
        if ($this->modelInstance) {
            return $this->modelInstance;
        }

        $modelClass = $this->resolveModelClass();
        
        if (!$modelClass || !class_exists($modelClass)) {
            return null;
        }

        $this->modelInstance = new $modelClass;
        return $this->modelInstance;
    }

    /**
     * Obtém o nome da tabela do modelo
     * 
     * @return string|null
     */
    public function getModelTable(): ?string
    {
        $model = $this->getModelInstance();
        return $model ? $model->getTable() : null;
    }

    /**
     * Obtém o nome do modelo (sem namespace)
     * 
     * @return string|null
     */
    public function getModelName(): ?string
    {
        $modelClass = $this->resolveModelClass();
        return $modelClass ? class_basename($modelClass) : null;
    }

    /**
     * Obtém o namespace do controller
     * 
     * @return string
     */
    protected function getControllerNamespace(): string
    {
        return (new ReflectionClass($this))->getNamespaceName();
    }

    /**
     * Verifica se o modelo foi resolvido com sucesso
     * 
     * @return bool
     */
    public function hasResolvedModel(): bool
    {
        return $this->resolveModelClass() !== null;
    }

    /**
     * Obtém o modelo resolvido ou lança exceção
     * 
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getResolvedModelClass(): string
    {
        $modelClass = $this->resolveModelClass();
        
        if (!$modelClass) {
            throw new \InvalidArgumentException(
                'Não foi possível resolver o modelo para o controller: ' . get_class($this)
            );
        }
        
        return $modelClass;
    }

    /**
     * Obtém a instância do modelo ou lança exceção
     * 
     * @return Model
     * @throws \InvalidArgumentException
     */
    public function getModelInstanceOrFail(): Model
    {
        $model = $this->getModelInstance();
        
        if (!$model) {
            throw new \InvalidArgumentException(
                'Não foi possível criar instância do modelo: ' . $this->resolveModelClass()
            );
        }
        
        return $model;
    }

    /**
     * Verifica se o cache está habilitado
     * 
     * @return bool
     */
    protected function isCacheEnabled(): bool
    {
        return config('react-papa-leguas.resolves_model.cache.enabled', false);
    }

    /**
     * Obtém o TTL do cache
     * 
     * @return int
     */
    protected function getCacheTtl(): int
    {
        return config('react-papa-leguas.resolves_model.cache.ttl', 3600);
    }

    /**
     * Verifica se a auto-descoberta está habilitada
     * 
     * @return bool
     */
    protected function isAutoDiscoveryEnabled(): bool
    {
        return config('react-papa-leguas.resolves_model.auto_discovery', true);
    }

    /**
     * Gera chave única para cache
     * 
     * @return string
     */
    protected function getCacheKey(): string
    {
        return 'resolves_model:' . md5(get_class($this));
    }

    /**
     * Adiciona namespace para busca de models
     * 
     * @param string $namespace
     * @return $this
     */
    public function addModelNamespace(string $namespace): static
    {
        $namespaces = config('react-papa-leguas.resolves_model.namespaces', []);
        
        if (!in_array($namespace, $namespaces)) {
            $namespaces[] = $namespace;
            config(['react-papa-leguas.resolves_model.namespaces' => $namespaces]);
        }
        
        return $this;
    }

    /**
     * Adiciona mapeamento personalizado
     * 
     * @param string $controllerName
     * @param string $modelClass
     * @return $this
     */
    public function addModelMapping(string $controllerName, string $modelClass): static
    {
        $mappings = config('react-papa-leguas.resolves_model.mappings', []);
        $mappings[$controllerName] = $modelClass;
        config(['react-papa-leguas.resolves_model.mappings' => $mappings]);
        
        return $this;
    }

    /**
     * Limpa cache de resolução de modelo
     * 
     * @return $this
     */
    public function clearModelCache(): static
    {
        if ($this->isCacheEnabled()) {
            Cache::forget($this->getCacheKey());
        }
        
        return $this;
    }
} 
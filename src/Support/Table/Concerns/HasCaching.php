<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Trait para sistema de cache avançado da tabela
 */
trait HasCaching
{
    /**
     * Configurações de cache
     */
    protected array $cacheConfig = [
        'enabled' => false,
        'ttl' => 3600, // 1 hora em segundos
        'store' => 'default', // Store do cache a usar
        'prefix' => 'papa_leguas_table',
        'tags' => [],
        'invalidate_on' => ['create', 'update', 'delete'], // Eventos que invalidam cache
        'strategy' => 'full', // 'full', 'partial', 'smart'
    ];

    /**
     * Chave de cache atual
     */
    protected ?string $currentCacheKey = null;

    /**
     * Habilitar cache
     */
    public function cache(bool $enabled = true, int $ttl = 3600): static
    {
        $this->cacheConfig['enabled'] = $enabled;
        $this->cacheConfig['ttl'] = $ttl;
        
        return $this;
    }

    /**
     * Definir TTL do cache
     */
    public function cacheTtl(int $ttl): static
    {
        $this->cacheConfig['ttl'] = $ttl;
        
        return $this;
    }

    /**
     * Definir store do cache
     */
    public function cacheStore(string $store): static
    {
        $this->cacheConfig['store'] = $store;
        
        return $this;
    }

    /**
     * Definir prefixo do cache
     */
    public function cachePrefix(string $prefix): static
    {
        $this->cacheConfig['prefix'] = $prefix;
        
        return $this;
    }

    /**
     * Definir tags do cache
     */
    public function cacheTags(array $tags): static
    {
        $this->cacheConfig['tags'] = $tags;
        
        return $this;
    }

    /**
     * Definir eventos que invalidam cache
     */
    public function cacheInvalidateOn(array $events): static
    {
        $this->cacheConfig['invalidate_on'] = $events;
        
        return $this;
    }

    /**
     * Definir estratégia de cache
     */
    public function cacheStrategy(string $strategy): static
    {
        $this->cacheConfig['strategy'] = $strategy;
        
        return $this;
    }

    /**
     * Verificar se cache está habilitado
     */
    public function isCacheEnabled(): bool
    {
        return $this->cacheConfig['enabled'] && config('cache.default') !== 'array';
    }

    /**
     * Obter dados do cache ou executar callback
     */
    public function getCachedData(Request $request): array
    {
        if (!$this->isCacheEnabled()) {
            return $this->buildTableData($request);
        }

        $cacheKey = $this->generateCacheKey($request);
        $this->currentCacheKey = $cacheKey;

        return $this->getCacheStore()->remember(
            $cacheKey,
            $this->cacheConfig['ttl'],
            function () use ($request) {
                return $this->buildTableData($request);
            }
        );
    }

    /**
     * Gerar chave de cache baseada na requisição
     */
    protected function generateCacheKey(Request $request): string
    {
        $keyParts = [
            $this->cacheConfig['prefix'],
            $this->getId(),
            $this->getModel(),
            md5(serialize([
                'filters' => $request->get('filters', []),
                'search' => $request->get('search', ''),
                'sort' => $request->get('sort', []),
                'page' => $request->get('page', 1),
                'per_page' => $request->get('per_page', 15),
                'user_id' => auth()->id(),
                'columns' => array_keys($this->getColumns()),
                'permissions' => $this->getUserPermissions(),
            ])),
        ];

        return implode(':', $keyParts);
    }

    /**
     * Obter store de cache
     */
    protected function getCacheStore()
    {
        $store = Cache::store($this->cacheConfig['store']);
        
        if (!empty($this->cacheConfig['tags'])) {
            return $store->tags($this->cacheConfig['tags']);
        }
        
        return $store;
    }

    /**
     * Invalidar cache da tabela
     */
    public function invalidateCache(): static
    {
        if (!$this->isCacheEnabled()) {
            return $this;
        }

        // Invalidar cache específico
        if ($this->currentCacheKey) {
            $this->getCacheStore()->forget($this->currentCacheKey);
        }

        // Invalidar por tags se definidas
        if (!empty($this->cacheConfig['tags'])) {
            Cache::tags($this->cacheConfig['tags'])->flush();
        }

        // Invalidar por padrão de chave
        $pattern = $this->cacheConfig['prefix'] . ':' . $this->getId() . ':*';
        $this->forgetByPattern($pattern);

        return $this;
    }

    /**
     * Invalidar cache por padrão
     */
    protected function forgetByPattern(string $pattern): void
    {
        try {
            $keys = Cache::getRedis()->keys($pattern);
            if (!empty($keys)) {
                Cache::getRedis()->del($keys);
            }
        } catch (\Exception $e) {
            // Fallback para outros drivers de cache
            // Para drivers que não suportam pattern, invalida tudo
            if (method_exists(Cache::getStore(), 'flush')) {
                Cache::flush();
            }
        }
    }

    /**
     * Aquecer cache (pre-load)
     */
    public function warmCache(Request $request = null): static
    {
        if (!$this->isCacheEnabled()) {
            return $this;
        }

        $request = $request ?? request();
        $this->getCachedData($request);

        return $this;
    }

    /**
     * Obter estatísticas de cache
     */
    public function getCacheStats(): array
    {
        if (!$this->isCacheEnabled()) {
            return ['enabled' => false];
        }

        return [
            'enabled' => true,
            'ttl' => $this->cacheConfig['ttl'],
            'store' => $this->cacheConfig['store'],
            'prefix' => $this->cacheConfig['prefix'],
            'tags' => $this->cacheConfig['tags'],
            'strategy' => $this->cacheConfig['strategy'],
            'current_key' => $this->currentCacheKey,
            'hit' => $this->currentCacheKey ? Cache::has($this->currentCacheKey) : false,
        ];
    }

    /**
     * Configurações de cache para diferentes cenários
     */
    public function cacheForDashboard(): static
    {
        return $this->cache(true, 300) // 5 minutos
                   ->cacheTags(['dashboard', 'tables'])
                   ->cacheStrategy('smart');
    }

    public function cacheForReports(): static
    {
        return $this->cache(true, 1800) // 30 minutos
                   ->cacheTags(['reports', 'tables'])
                   ->cacheStrategy('full');
    }

    public function cacheForApi(): static
    {
        return $this->cache(true, 600) // 10 minutos
                   ->cacheTags(['api', 'tables'])
                   ->cacheStrategy('partial');
    }

    /**
     * Cache inteligente baseado no tamanho dos dados
     */
    public function smartCache(): static
    {
        // Determinar TTL baseado no número de registros
        $model = $this->getModel();
        if ($model) {
            $count = $model::count();
            
            if ($count < 100) {
                $ttl = 1800; // 30 min para tabelas pequenas
            } elseif ($count < 1000) {
                $ttl = 900; // 15 min para tabelas médias
            } else {
                $ttl = 300; // 5 min para tabelas grandes
            }
            
            $this->cacheTtl($ttl);
        }

        return $this->cache(true);
    }

    /**
     * Obter permissões do usuário para cache
     */
    protected function getUserPermissions(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            return [];
        }

        // Se usar Spatie Permission
        if (method_exists($user, 'getAllPermissions')) {
            return $user->getAllPermissions()->pluck('name')->toArray();
        }

        // Se usar sistema customizado
        if (method_exists($user, 'getPermissions')) {
            return $user->getPermissions();
        }

        return [];
    }
} 
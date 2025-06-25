<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use ReflectionClass;

/**
 * Trait HasPermissionChecks
 * 
 * Implementa sistema dinâmico de verificação de permissões que:
 * - Integra com o sistema Shinobi existente
 * - Busca automaticamente por Policies em múltiplos namespaces
 * - Suporta regras hardcoded como fallback
 * - Oferece cache para performance
 * 
 * @package Callcocam\ReactPapaLeguas\Support\Concerns
 */
trait HasPermissionChecks
{
    /**
     * Cache de políticas resolvidas em memória durante a requisição
     * 
     * @var array
     */
    protected static array $resolvedPoliciesCache = [];

    /**
     * Cache de verificações de permissão durante a requisição
     * 
     * @var array
     */
    protected array $permissionCheckCache = [];

    /**
     * Configuração de namespaces para busca de policies
     * 
     * @var array
     */
    protected array $policyNamespaces = [
        'App\\Policies',
        'Callcocam\\ReactPapaLeguas\\Policies',
    ];

    /**
     * Verifica se o usuário atual tem permissão para realizar uma ação
     * 
     * @param string $ability Nome da habilidade/ação (ex: 'view', 'create', 'update', 'delete')
     * @param mixed $resource Recurso/modelo a ser verificado (pode ser string de classe ou instância)
     * @param array $context Contexto adicional para a verificação
     * @return bool
     */
    public function checkPermission(string $ability, $resource = null, array $context = []): bool
    {
        try {
            // Criar chave de cache para esta verificação
            $cacheKey = $this->buildPermissionCacheKey($ability, $resource, $context);
            
            // Verificar cache em memória primeiro
            if (isset($this->permissionCheckCache[$cacheKey])) {
                return $this->permissionCheckCache[$cacheKey];
            }

            $user = auth()->user();
            
            // Se não há usuário autenticado, negar acesso
            if (!$user) {
                return $this->cachePermissionResult($cacheKey, false);
            }

            // 1. Verificar se é super-admin ou tem role especial (bypass automático)
            if ($this->hasSuperAdminBypass($user)) {
                $this->logPermissionCheck($ability, $resource, true, 'super_admin_bypass');
                return $this->cachePermissionResult($cacheKey, true);
            }

            // 2. Tentar resolver e usar Policy específica
            $policyResult = $this->checkPolicyPermission($user, $ability, $resource, $context);
            if ($policyResult !== null) {
                $this->logPermissionCheck($ability, $resource, $policyResult, 'policy');
                return $this->cachePermissionResult($cacheKey, $policyResult);
            }

            // 3. Verificar permissões diretas via Shinobi
            $shinobiResult = $this->checkShinobiPermission($user, $ability, $resource, $context);
            if ($shinobiResult !== null) {
                $this->logPermissionCheck($ability, $resource, $shinobiResult, 'shinobi');
                return $this->cachePermissionResult($cacheKey, $shinobiResult);
            }

            // 4. Aplicar regras de fallback hardcoded
            $fallbackResult = $this->checkFallbackRules($user, $ability, $resource, $context);
            $this->logPermissionCheck($ability, $resource, $fallbackResult, 'fallback');
            
            return $this->cachePermissionResult($cacheKey, $fallbackResult);

        } catch (\Exception $e) {
            Log::error('Erro na verificação de permissão', [
                'ability' => $ability,
                'resource' => $this->getResourceIdentifier($resource),
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Em caso de erro, negar acesso por segurança
            return false;
        }
    }

    /**
     * Verifica múltiplas permissões de uma vez (otimizado)
     * 
     * @param array $checks Array de verificações: ['ability' => 'resource', ...]
     * @param array $context Contexto adicional
     * @return array
     */
    public function checkMultiplePermissions(array $checks, array $context = []): array
    {
        $results = [];
        
        foreach ($checks as $ability => $resource) {
            $results[$ability] = $this->checkPermission($ability, $resource, $context);
        }
        
        return $results;
    }

    /**
     * Wrapper para authorize do Laravel que usa nosso sistema
     * 
     * @param string $ability
     * @param mixed $resource
     * @param array $context
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function authorizePermission(string $ability, $resource = null, array $context = []): void
    {
        if (!$this->checkPermission($ability, $resource, $context)) {
            abort(403, "Acesso negado para a ação '{$ability}'");
        }
    }

    /**
     * Verifica se o usuário tem bypass de super-admin
     * 
     * @param mixed $user
     * @return bool
     */
    protected function hasSuperAdminBypass($user): bool
    {
        if (!$user) {
            return false;
        }

        // Verificar se tem método hasRole do Shinobi
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('super-admin') || $user->hasRole('super_admin');
        }

        // Verificar role especial do Shinobi
        if (method_exists($user, 'roles')) {
            $specialRoles = $user->roles()->where('special', true)->get();
            foreach ($specialRoles as $role) {
                if ($role->special === true || $role->special === 'all-access') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Verifica permissão usando Policy específica
     * 
     * @param mixed $user
     * @param string $ability
     * @param mixed $resource
     * @param array $context
     * @return bool|null
     */
    protected function checkPolicyPermission($user, string $ability, $resource, array $context): ?bool
    {
        $policy = $this->resolvePolicy($resource);
        
        if (!$policy) {
            return null;
        }

        try {
            // Verificar se o método existe na policy
            if (!method_exists($policy, $ability)) {
                return null;
            }

            // Executar verificação da policy
            if ($resource instanceof Model) {
                return $policy->{$ability}($user, $resource, ...$context);
            } else {
                return $policy->{$ability}($user, ...$context);
            }
            
        } catch (\Exception $e) {
            Log::warning('Erro ao executar policy', [
                'policy_class' => get_class($policy),
                'ability' => $ability,
                'resource' => $this->getResourceIdentifier($resource),
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Verifica permissão usando sistema Shinobi
     * 
     * @param mixed $user
     * @param string $ability
     * @param mixed $resource
     * @param array $context
     * @return bool|null
     */
    protected function checkShinobiPermission($user, string $ability, $resource, array $context): ?bool
    {
        if (!method_exists($user, 'hasPermissionTo')) {
            return null;
        }

        try {
            // Gerar slug da permissão baseado no recurso e habilidade
            $permissionSlug = $this->buildPermissionSlug($ability, $resource);
            
            return $user->hasPermissionTo($permissionSlug);
            
        } catch (\Exception $e) {
            Log::warning('Erro ao verificar permissão Shinobi', [
                'ability' => $ability,
                'resource' => $this->getResourceIdentifier($resource),
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Aplica regras de fallback hardcoded
     * 
     * @param mixed $user
     * @param string $ability
     * @param mixed $resource
     * @param array $context
     * @return bool
     */
    protected function checkFallbackRules($user, string $ability, $resource, array $context): bool
    {
        $config = config('react-papa-leguas.permissions.fallback_rules', []);
        
        // Se fallback está desabilitado, negar por padrão
        if (empty($config)) {
            return false;
        }

        // Verificar se landlord tem acesso total
        if ($config['landlord_full_access'] ?? false) {
            if ($this->isLandlordUser($user)) {
                return true;
            }
        }

        // Verificar escopo de tenant
        if ($config['tenant_scoped_access'] ?? false) {
            if ($this->hasTenantScopedAccess($user, $resource)) {
                return in_array($ability, ['view', 'viewAny', 'create', 'update']);
            }
        }

        // Por padrão, negar acesso
        return false;
    }

    /**
     * Resolve a Policy para um recurso
     * 
     * @param mixed $resource
     * @return object|null
     */
    protected function resolvePolicy($resource): ?object
    {
        $modelClass = $this->getModelClassFromResource($resource);
        
        if (!$modelClass) {
            return null;
        }

        $modelName = class_basename($modelClass);
        $cacheKey = "policy_resolution_{$modelName}";

        // Verificar cache em memória
        if (isset(self::$resolvedPoliciesCache[$cacheKey])) {
            return self::$resolvedPoliciesCache[$cacheKey];
        }

        // Tentar cada namespace configurado
        foreach ($this->policyNamespaces as $namespace) {
            $policyClass = "{$namespace}\\{$modelName}Policy";
            
            if (class_exists($policyClass)) {
                $policy = app($policyClass);
                self::$resolvedPoliciesCache[$cacheKey] = $policy;
                return $policy;
            }
        }

        // Tentar DefaultPolicy como fallback
        $defaultPolicyClass = 'Callcocam\\ReactPapaLeguas\\Policies\\DefaultPolicy';
        if (class_exists($defaultPolicyClass)) {
            $policy = app($defaultPolicyClass);
            self::$resolvedPoliciesCache[$cacheKey] = $policy;
            return $policy;
        }

        // Cache negativo para evitar múltiplas tentativas
        self::$resolvedPoliciesCache[$cacheKey] = null;
        return null;
    }

    /**
     * Obtém a classe do modelo a partir do recurso
     * 
     * @param mixed $resource
     * @return string|null
     */
    protected function getModelClassFromResource($resource): ?string
    {
        if (is_string($resource) && class_exists($resource)) {
            return $resource;
        }
        
        if (is_object($resource)) {
            return get_class($resource);
        }
        
        // Se o trait está sendo usado em um controller, tentar getModelClass()
        if (method_exists($this, 'getModelClass')) {
            return $this->getModelClass();
        }
        
        return null;
    }

    /**
     * Constrói slug de permissão baseado na habilidade e recurso
     * 
     * @param string $ability
     * @param mixed $resource
     * @return string
     */
    protected function buildPermissionSlug(string $ability, $resource): string
    {
        $modelClass = $this->getModelClassFromResource($resource);
        
        if ($modelClass) {
            $modelName = Str::plural(Str::snake(class_basename($modelClass)));
            return "{$modelName}.{$ability}";
        }
        
        return $ability;
    }

    /**
     * Verifica se é usuário landlord
     * 
     * @param mixed $user
     * @return bool
     */
    protected function isLandlordUser($user): bool
    {
        if (!$user) {
            return false;
        }

        // Verificar se é modelo Admin/Landlord
        $adminModel = config('react-papa-leguas.landlord.model');
        if ($adminModel && $user instanceof $adminModel) {
            return true;
        }

        // Verificar por role
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole('landlord') || $user->hasRole('admin');
        }

        return false;
    }

    /**
     * Verifica se tem acesso scoped por tenant
     * 
     * @param mixed $user
     * @param mixed $resource
     * @return bool
     */
    protected function hasTenantScopedAccess($user, $resource): bool
    {
        if (!$user || !$resource) {
            return false;
        }

        // Se o recurso é uma instância de modelo, verificar tenant_id
        if ($resource instanceof Model && isset($resource->tenant_id) && isset($user->tenant_id)) {
            return $resource->tenant_id === $user->tenant_id;
        }

        return true; // Para recursos sem tenant, permitir acesso básico
    }

    /**
     * Constrói chave de cache para verificação de permissão
     * 
     * @param string $ability
     * @param mixed $resource
     * @param array $context
     * @return string
     */
    protected function buildPermissionCacheKey(string $ability, $resource, array $context): string
    {
        $resourceId = $this->getResourceIdentifier($resource);
        $contextHash = md5(json_encode($context));
        $userId = auth()->id() ?? 'guest';
        
        return "perm_{$userId}_{$ability}_{$resourceId}_{$contextHash}";
    }

    /**
     * Obtém identificador único do recurso
     * 
     * @param mixed $resource
     * @return string
     */
    protected function getResourceIdentifier($resource): string
    {
        if (is_string($resource)) {
            return $resource;
        }
        
        if ($resource instanceof Model) {
            return get_class($resource) . '_' . ($resource->getKey() ?? 'new');
        }
        
        if (is_object($resource)) {
            return get_class($resource);
        }
        
        return 'unknown';
    }

    /**
     * Armazena resultado no cache em memória
     * 
     * @param string $cacheKey
     * @param bool $result
     * @return bool
     */
    protected function cachePermissionResult(string $cacheKey, bool $result): bool
    {
        $this->permissionCheckCache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Loga verificação de permissão (se habilitado)
     * 
     * @param string $ability
     * @param mixed $resource
     * @param bool $result
     * @param string $method
     */
    protected function logPermissionCheck(string $ability, $resource, bool $result, string $method): void
    {
        $auditConfig = config('react-papa-leguas.permissions.audit', []);
        
        if (!($auditConfig['enabled'] ?? false)) {
            return;
        }

        $shouldLog = $result 
            ? ($auditConfig['log_successful_attempts'] ?? false)
            : ($auditConfig['log_failed_attempts'] ?? true);

        if (!$shouldLog) {
            return;
        }

        $logData = [
            'user_id' => auth()->id(),
            'ability' => $ability,
            'resource' => $this->getResourceIdentifier($resource),
            'result' => $result ? 'GRANTED' : 'DENIED',
            'method' => $method,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ];

        $logChannel = $auditConfig['log_channel'] ?? 'permissions';
        Log::channel($logChannel)->info('Permission Check', $logData);
    }

    /**
     * Limpa caches em memória (útil para testes)
     */
    public function clearPermissionCaches(): void
    {
        $this->permissionCheckCache = [];
        self::$resolvedPoliciesCache = [];
    }
} 
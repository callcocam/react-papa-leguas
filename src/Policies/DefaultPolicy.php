<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Policies;

use Illuminate\Database\Eloquent\Model;

/**
 * Policy padrão do sistema Papa Leguas
 * 
 * Esta policy serve como fallback quando não há uma policy específica
 * para um modelo. Implementa as regras básicas de permissão baseadas
 * no sistema Shinobi e nas configurações de tenant/landlord.
 * 
 * @package Callcocam\ReactPapaLeguas\Policies
 */
class DefaultPolicy
{
    /**
     * Determina se o usuário pode visualizar qualquer recurso
     * 
     * @param mixed $user
     * @param string|null $modelClass
     * @return bool
     */
    public function viewAny($user, ?string $modelClass = null): bool
    {
        if (!$user) {
            return false;
        }

        // Super admin tem acesso total
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Verificar permissão específica via Shinobi
        if ($modelClass && method_exists($user, 'hasPermissionTo')) {
            $permissionSlug = $this->buildPermissionSlug('view', $modelClass);
            if ($user->hasPermissionTo($permissionSlug)) {
                return true;
            }
        }

        // Landlords têm acesso de visualização por padrão
        if ($this->isLandlordUser($user)) {
            return true;
        }

        // Usuários de tenant têm acesso básico de visualização
        return $this->isTenantUser($user);
    }

    /**
     * Determina se o usuário pode visualizar o recurso específico
     * 
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    public function view($user, Model $model): bool
    {
        if (!$user) {
            return false;
        }

        // Super admin tem acesso total
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Verificar permissão específica via Shinobi
        if (method_exists($user, 'hasPermissionTo')) {
            $permissionSlug = $this->buildPermissionSlug('view', get_class($model));
            if ($user->hasPermissionTo($permissionSlug)) {
                return $this->checkTenantScope($user, $model);
            }
        }

        // Landlords têm acesso se não há tenant ou se têm acesso ao tenant
        if ($this->isLandlordUser($user)) {
            return $this->checkLandlordTenantAccess($user, $model);
        }

        // Verificar escopo de tenant
        return $this->checkTenantScope($user, $model);
    }

    /**
     * Determina se o usuário pode criar recursos
     * 
     * @param mixed $user
     * @param string|null $modelClass
     * @return bool
     */
    public function create($user, ?string $modelClass = null): bool
    {
        if (!$user) {
            return false;
        }

        // Super admin tem acesso total
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Verificar permissão específica via Shinobi
        if ($modelClass && method_exists($user, 'hasPermissionTo')) {
            $permissionSlug = $this->buildPermissionSlug('create', $modelClass);
            if ($user->hasPermissionTo($permissionSlug)) {
                return true;
            }
        }

        // Landlords podem criar por padrão
        if ($this->isLandlordUser($user)) {
            return true;
        }

        // Usuários de tenant têm criação limitada
        return $this->isTenantUser($user);
    }

    /**
     * Determina se o usuário pode atualizar o recurso
     * 
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    public function update($user, Model $model): bool
    {
        if (!$user) {
            return false;
        }

        // Super admin tem acesso total
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Verificar permissão específica via Shinobi
        if (method_exists($user, 'hasPermissionTo')) {
            $permissionSlug = $this->buildPermissionSlug('update', get_class($model));
            if ($user->hasPermissionTo($permissionSlug)) {
                return $this->checkTenantScope($user, $model);
            }
        }

        // Landlords têm acesso se têm acesso ao tenant
        if ($this->isLandlordUser($user)) {
            return $this->checkLandlordTenantAccess($user, $model);
        }

        // Verificar escopo de tenant e propriedade
        return $this->checkTenantScope($user, $model) && $this->checkOwnership($user, $model);
    }

    /**
     * Determina se o usuário pode deletar o recurso
     * 
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    public function delete($user, Model $model): bool
    {
        if (!$user) {
            return false;
        }

        // Super admin tem acesso total
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Verificar permissão específica via Shinobi
        if (method_exists($user, 'hasPermissionTo')) {
            $permissionSlug = $this->buildPermissionSlug('delete', get_class($model));
            if ($user->hasPermissionTo($permissionSlug)) {
                return $this->checkTenantScope($user, $model);
            }
        }

        // Landlords têm acesso se têm acesso ao tenant
        if ($this->isLandlordUser($user)) {
            return $this->checkLandlordTenantAccess($user, $model);
        }

        // Verificar escopo de tenant e propriedade (mais restritivo para deletar)
        return $this->checkTenantScope($user, $model) && $this->checkOwnership($user, $model);
    }

    /**
     * Determina se o usuário pode restaurar o recurso (soft deletes)
     * 
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    public function restore($user, Model $model): bool
    {
        // Usar mesma lógica do update para restore
        return $this->update($user, $model);
    }

    /**
     * Determina se o usuário pode forçar a exclusão do recurso
     * 
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    public function forceDelete($user, Model $model): bool
    {
        if (!$user) {
            return false;
        }

        // Apenas super admin pode forçar exclusão por padrão
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Landlords com permissão específica
        if ($this->isLandlordUser($user) && method_exists($user, 'hasPermissionTo')) {
            $permissionSlug = $this->buildPermissionSlug('force_delete', get_class($model));
            return $user->hasPermissionTo($permissionSlug);
        }

        return false;
    }

    /**
     * Verifica se o usuário é super admin
     * 
     * @param mixed $user
     * @return bool
     */
    protected function isSuperAdmin($user): bool
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
     * Verifica se é usuário de tenant
     * 
     * @param mixed $user
     * @return bool
     */
    protected function isTenantUser($user): bool
    {
        if (!$user) {
            return false;
        }

        // Se tem tenant_id, é usuário de tenant
        return isset($user->tenant_id) && !empty($user->tenant_id);
    }

    /**
     * Verifica escopo de tenant
     * 
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    protected function checkTenantScope($user, Model $model): bool
    {
        // Se o modelo não tem tenant_id, permitir acesso
        if (!isset($model->tenant_id)) {
            return true;
        }

        // Se o usuário não tem tenant_id, negar acesso a recursos com tenant
        if (!isset($user->tenant_id)) {
            return false;
        }

        // Verificar se pertencem ao mesmo tenant
        return $model->tenant_id === $user->tenant_id;
    }

    /**
     * Verifica se landlord tem acesso ao tenant do modelo
     * 
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    protected function checkLandlordTenantAccess($user, Model $model): bool
    {
        // Se o modelo não tem tenant_id, landlord tem acesso
        if (!isset($model->tenant_id)) {
            return true;
        }

        // Verificar se o landlord tem acesso a este tenant específico
        // Implementação pode variar baseada no modelo de relacionamento
        if (method_exists($user, 'tenants')) {
            return $user->tenants()->where('id', $model->tenant_id)->exists();
        }

        // Por padrão, landlord tem acesso a todos os tenants
        return true;
    }

    /**
     * Verifica propriedade do recurso
     * 
     * @param mixed $user
     * @param Model $model
     * @return bool
     */
    protected function checkOwnership($user, Model $model): bool
    {
        // Se o modelo tem user_id, verificar propriedade
        if (isset($model->user_id)) {
            return $model->user_id === $user->id;
        }

        // Se não há campo de propriedade, assumir que pode editar
        return true;
    }

    /**
     * Constrói slug de permissão baseado na ação e modelo
     * 
     * @param string $action
     * @param string $modelClass
     * @return string
     */
    protected function buildPermissionSlug(string $action, string $modelClass): string
    {
        $modelName = \Illuminate\Support\Str::plural(\Illuminate\Support\Str::snake(class_basename($modelClass)));
        return "{$modelName}.{$action}";
    }
} 
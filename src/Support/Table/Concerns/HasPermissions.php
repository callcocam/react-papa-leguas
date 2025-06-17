<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Closure;

/**
 * Trait para sistema de permissões avançado da tabela
 */
trait HasPermissions
{
    /**
     * Configurações de permissões
     */
    protected array $permissionConfig = [
        'enabled' => true,
        'guard' => 'web',
        'model_policy' => null,
        'table_permissions' => [],
        'column_permissions' => [],
        'action_permissions' => [],
        'filter_permissions' => [],
        'row_level_permissions' => null,
    ];

    /**
     * Permissões do usuário atual (cache)
     */
    protected ?array $userPermissions = null;

    /**
     * Habilitar sistema de permissões
     */
    public function permissions(bool $enabled = true): static
    {
        $this->permissionConfig['enabled'] = $enabled;
        
        return $this;
    }

    /**
     * Definir guard de autenticação
     */
    public function permissionGuard(string $guard): static
    {
        $this->permissionConfig['guard'] = $guard;
        
        return $this;
    }

    /**
     * Definir policy do modelo
     */
    public function modelPolicy(string $policy): static
    {
        $this->permissionConfig['model_policy'] = $policy;
        
        return $this;
    }

    /**
     * Definir permissões da tabela
     */
    public function tablePermissions(array $permissions): static
    {
        $this->permissionConfig['table_permissions'] = $permissions;
        
        return $this;
    }

    /**
     * Definir permissões de colunas
     */
    public function columnPermissions(array $permissions): static
    {
        $this->permissionConfig['column_permissions'] = $permissions;
        
        return $this;
    }

    /**
     * Definir permissões de ações
     */
    public function actionPermissions(array $permissions): static
    {
        $this->permissionConfig['action_permissions'] = $permissions;
        
        return $this;
    }

    /**
     * Definir permissões de filtros
     */
    public function filterPermissions(array $permissions): static
    {
        $this->permissionConfig['filter_permissions'] = $permissions;
        
        return $this;
    }

    /**
     * Definir permissões em nível de linha
     */
    public function rowLevelPermissions(Closure $callback): static
    {
        $this->permissionConfig['row_level_permissions'] = $callback;
        
        return $this;
    }

    /**
     * Verificar se usuário tem permissão
     */
    public function hasPermission(string $permission): bool
    {
        if (!$this->permissionConfig['enabled']) {
            return true;
        }

        $user = auth()->guard($this->permissionConfig['guard'])->user();
        
        if (!$user) {
            return false;
        }

        // Verificar se é super admin
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        // Cache das permissões do usuário
        if ($this->userPermissions === null) {
            $this->userPermissions = $this->getUserPermissions();
        }

        // Verificar permissão específica
        if (in_array($permission, $this->userPermissions)) {
            return true;
        }

        // Verificar permissão usando policy
        if ($this->permissionConfig['model_policy'] && $this->getModel()) {
            return $this->checkModelPolicy($permission, $user);
        }

        return false;
    }

    /**
     * Verificar múltiplas permissões (AND)
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        
        return true;
    }

    /**
     * Verificar múltiplas permissões (OR)
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Aplicar permissões à query
     */
    protected function applyPermissions(Builder $query, Request $request): Builder
    {
        if (!$this->permissionConfig['enabled']) {
            return $query;
        }

        // Aplicar filtros de permissão em nível de linha
        if ($this->permissionConfig['row_level_permissions']) {
            $callback = $this->permissionConfig['row_level_permissions'];
            $query = $callback($query, auth()->user(), $request);
        }

        // Aplicar scope de tenant se disponível
        if (method_exists($query->getModel(), 'scopeForUser')) {
            $query->forUser(auth()->user());
        }

        return $query;
    }

    /**
     * Obter colunas visíveis baseadas em permissões
     */
    public function getPermissionFilteredColumns(): array
    {
        $columns = $this->getColumns();
        
        if (!$this->permissionConfig['enabled']) {
            return $columns;
        }

        $filteredColumns = [];
        
        foreach ($columns as $key => $column) {
            if ($this->canViewColumn($key)) {
                $filteredColumns[$key] = $column;
            }
        }
        
        return $filteredColumns;
    }

    /**
     * Verificar se pode visualizar coluna
     */
    public function canViewColumn(string $columnKey): bool
    {
        if (!$this->permissionConfig['enabled']) {
            return true;
        }

        // Verificar permissões específicas da coluna
        if (isset($this->permissionConfig['column_permissions'][$columnKey])) {
            $permissions = $this->permissionConfig['column_permissions'][$columnKey];
            
            if (isset($permissions['view'])) {
                return $this->hasAnyPermission((array) $permissions['view']);
            }
        }

        // Verificar permissão geral de visualização
        return $this->hasPermission('view_' . $this->getId() . '_columns') ||
               $this->hasPermission('view_table_columns');
    }

    /**
     * Verificar se pode editar coluna
     */
    public function canEditColumn(string $columnKey): bool
    {
        if (!$this->permissionConfig['enabled']) {
            return true;
        }

        if (isset($this->permissionConfig['column_permissions'][$columnKey])) {
            $permissions = $this->permissionConfig['column_permissions'][$columnKey];
            
            if (isset($permissions['edit'])) {
                return $this->hasAnyPermission((array) $permissions['edit']);
            }
        }

        return $this->hasPermission('edit_' . $this->getId() . '_columns') ||
               $this->hasPermission('edit_table_columns');
    }

    /**
     * Obter ações visíveis baseadas em permissões
     */
    public function getPermissionFilteredActions(): array
    {
        $actions = $this->getHeaderActions();
        
        if (!$this->permissionConfig['enabled']) {
            return $actions;
        }

        return array_filter($actions, function ($action) {
            return $this->canPerformAction($action->getId());
        });
    }

    /**
     * Verificar se pode executar ação
     */
    public function canPerformAction(string $actionId): bool
    {
        if (!$this->permissionConfig['enabled']) {
            return true;
        }

        if (isset($this->permissionConfig['action_permissions'][$actionId])) {
            $permissions = $this->permissionConfig['action_permissions'][$actionId];
            return $this->hasAnyPermission((array) $permissions);
        }

        return $this->hasPermission($actionId) ||
               $this->hasPermission('perform_table_actions');
    }

    /**
     * Obter filtros visíveis baseados em permissões
     */
    public function getPermissionFilteredFilters(): array
    {
        $filters = $this->getFilters();
        
        if (!$this->permissionConfig['enabled']) {
            return $filters;
        }

        return array_filter($filters, function ($filter) {
            return $this->canUseFilter($filter->getId());
        });
    }

    /**
     * Verificar se pode usar filtro
     */
    public function canUseFilter(string $filterId): bool
    {
        if (!$this->permissionConfig['enabled']) {
            return true;
        }

        if (isset($this->permissionConfig['filter_permissions'][$filterId])) {
            $permissions = $this->permissionConfig['filter_permissions'][$filterId];
            return $this->hasAnyPermission((array) $permissions);
        }

        return $this->hasPermission('use_' . $filterId . '_filter') ||
               $this->hasPermission('use_table_filters');
    }

    /**
     * Obter permissões para o frontend
     */
    protected function getPermissionsForFrontend(): array
    {
        if (!$this->permissionConfig['enabled']) {
            return ['enabled' => false];
        }

        return [
            'enabled' => true,
            'user_permissions' => $this->getUserPermissions(),
            'table_permissions' => [
                'view' => $this->hasPermission('view_' . $this->getId()),
                'create' => $this->hasPermission('create_' . $this->getId()),
                'edit' => $this->hasPermission('edit_' . $this->getId()),
                'delete' => $this->hasPermission('delete_' . $this->getId()),
                'export' => $this->hasPermission('export_' . $this->getId()),
            ],
            'column_permissions' => $this->getColumnPermissionsForFrontend(),
            'action_permissions' => $this->getActionPermissionsForFrontend(),
            'filter_permissions' => $this->getFilterPermissionsForFrontend(),
        ];
    }

    /**
     * Obter permissões de colunas para frontend
     */
    protected function getColumnPermissionsForFrontend(): array
    {
        $permissions = [];
        
        foreach (array_keys($this->getColumns()) as $columnKey) {
            $permissions[$columnKey] = [
                'view' => $this->canViewColumn($columnKey),
                'edit' => $this->canEditColumn($columnKey),
            ];
        }
        
        return $permissions;
    }

    /**
     * Obter permissões de ações para frontend
     */
    protected function getActionPermissionsForFrontend(): array
    {
        $permissions = [];
        
        foreach ($this->getHeaderActions() as $action) {
            $permissions[$action->getId()] = $this->canPerformAction($action->getId());
        }
        
        foreach ($this->getRowActions() as $action) {
            $permissions[$action->getId()] = $this->canPerformAction($action->getId());
        }
        
        return $permissions;
    }

    /**
     * Obter permissões de filtros para frontend
     */
    protected function getFilterPermissionsForFrontend(): array
    {
        $permissions = [];
        
        foreach ($this->getFilters() as $filter) {
            $permissions[$filter->getId()] = $this->canUseFilter($filter->getId());
        }
        
        return $permissions;
    }

    /**
     * Verificar se usuário é super admin
     */
    protected function isSuperAdmin($user): bool
    {
        // Verificar role de super admin
        if (method_exists($user, 'hasRole')) {
            return $user->hasRole(['super-admin', 'administrator', 'admin']);
        }

        // Verificar permissão de super admin
        if (method_exists($user, 'can')) {
            return $user->can('*') || $user->can('super-admin');
        }

        // Verificar propriedade is_admin
        if (isset($user->is_admin)) {
            return $user->is_admin;
        }

        return false;
    }

    /**
     * Verificar permissão usando policy do modelo
     */
    protected function checkModelPolicy(string $permission, $user): bool
    {
        $policy = $this->permissionConfig['model_policy'];
        $model = $this->getModel();
        
        if (!$policy || !$model) {
            return false;
        }

        try {
            $policyInstance = app($policy);
            
            if (method_exists($policyInstance, $permission)) {
                return $policyInstance->{$permission}($user, new $model);
            }
        } catch (\Exception $e) {
            // Log error and return false
            return false;
        }
        
        return false;
    }

    /**
     * Configurações rápidas de permissões
     */
    public function adminOnly(): static
    {
        return $this->tablePermissions([
            'view' => ['admin', 'super-admin'],
            'create' => ['admin', 'super-admin'],
            'edit' => ['admin', 'super-admin'],
            'delete' => ['admin', 'super-admin'],
        ]);
    }

    public function readOnly(): static
    {
        return $this->tablePermissions([
            'view' => ['*'],
            'create' => [],
            'edit' => [],
            'delete' => [],
        ]);
    }

    public function ownerOnly(): static
    {
        return $this->rowLevelPermissions(function ($query, $user) {
            return $query->where('user_id', $user->id);
        });
    }

    public function tenantScoped(): static
    {
        return $this->rowLevelPermissions(function ($query, $user) {
            if (isset($user->tenant_id)) {
                return $query->where('tenant_id', $user->tenant_id);
            }
            return $query;
        });
    }
} 
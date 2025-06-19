<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Callcocam\ReactPapaLeguas\ReactPapaLeguas;

/**
 * Trait para gerenciar nomes de rotas de forma padronizada
 * Foca apenas em nomes (users.index), não URLs (Laravel gerencia paths)
 */
trait BelongsToRoutes
{
    protected $customRoutePrefix = null;

    /**
     * Define prefixo personalizado das rotas
     */
    public function setRoutePrefix(string $prefix): self
    {
        $this->customRoutePrefix = $prefix;
        return $this;
    }

    /**
     * Obter o prefixo das rotas (automático ou personalizado)
     */
    protected function getRoutePrefix(): string
    {
        return ReactPapaLeguas::generateRoutePrefix(
            $this->getRouteSource(),
            $this->customRoutePrefix
        );
    }

    /**
     * Obter fonte para geração automática de rotas
     */
    protected function getRouteSource()
    {
        // Prioridade: model → classe atual
        if (isset($this->model) && $this->model) {
            return $this->model;
        }

        return static::class;
    }

    /**
     * Gerar nome de rota específico
     */
    protected function getRouteName(string $action): string
    {
        return ReactPapaLeguas::generateRouteName(
            $action,
            $this->getRouteSource(),
            $this->customRoutePrefix
        );
    }

    /**
     * Métodos específicos para cada ação
     */
    
    public function getRouteNameIndex(): string
    {
        return $this->getRouteName('index');
    }

    public function getRouteNameCreate(): string
    {
        return $this->getRouteName('create');
    }

    public function getRouteNameStore(): string
    {
        return $this->getRouteName('store');
    }

    public function getRouteNameShow(): string
    {
        return $this->getRouteName('show');
    }

    public function getRouteNameEdit(): string
    {
        return $this->getRouteName('edit');
    }

    public function getRouteNameUpdate(): string
    {
        return $this->getRouteName('update');
    }

    public function getRouteNameDestroy(): string
    {
        return $this->getRouteName('destroy');
    }

    public function getRouteNameExport(): string
    {
        return $this->getRouteName('export');
    }

    public function getRouteNameBulkDestroy(): string
    {
        return $this->getRouteName('bulk_destroy');
    }

    /**
     * Obter todos os nomes de rotas como array
     */
    public function getRouteNames(): array
    {
        return ReactPapaLeguas::generateAllRouteNames(
            $this->getRouteSource(),
            $this->customRoutePrefix
        );
    }

    /**
     * Verificar se uma ação é válida
     */
    public function hasRoute(string $action): bool
    {
        return ReactPapaLeguas::isValidAction($action);
    }

    /**
     * Gerar nome de rota customizada
     */
    public function getCustomRouteName(string $action): string
    {
        return $this->getRouteName($action);
    }

    /**
     * Exemplos de uso:
     * 
     * // Auto-detecção (UserTable → users.index)
     * $this->getRouteNameIndex() → 'users.index'
     * 
     * // Prefixo personalizado
     * $this->setRoutePrefix('admin.users');
     * $this->getRouteNameCreate() → 'admin.users.create'
     * 
     * // Array completo
     * $this->getRouteNames() → ['index' => 'users.index', 'create' => 'users.create', ...]
     */
}

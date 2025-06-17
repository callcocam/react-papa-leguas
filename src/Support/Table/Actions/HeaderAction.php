<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Actions;

/**
 * Ação do cabeçalho da tabela
 */
class HeaderAction extends Action
{
    /**
     * Posição da ação no cabeçalho
     */
    protected string $position = 'right';

    /**
     * Grupo da ação
     */
    protected ?string $group = null;

    /**
     * Se a ação é um dropdown
     */
    protected bool $isDropdown = false;

    /**
     * Ações filhas (para dropdown)
     */
    protected array $children = [];

    /**
     * Definir posição
     */
    public function position(string $position): static
    {
        $this->position = $position;
        return $this;
    }

    /**
     * Posicionar à esquerda
     */
    public function left(): static
    {
        return $this->position('left');
    }

    /**
     * Posicionar à direita
     */
    public function right(): static
    {
        return $this->position('right');
    }

    /**
     * Definir grupo
     */
    public function group(string $group): static
    {
        $this->group = $group;
        return $this;
    }

    /**
     * Tornar um dropdown
     */
    public function dropdown(array $children = []): static
    {
        $this->isDropdown = true;
        $this->children = $children;
        return $this;
    }

    /**
     * Adicionar filhos ao dropdown
     */
    public function children(array $children): static
    {
        $this->children = $children;
        return $this;
    }

    /**
     * Ação de criar
     */
    public static function create(string $route = null): static
    {
        return static::make('create')
            ->label('Criar')
            ->icon('Plus')
            ->color('primary')
            ->route($route ?? 'create');
    }

    /**
     * Ação de exportar
     */
    public static function export(string $route = null): static
    {
        return static::make('export')
            ->label('Exportar')
            ->icon('Download')
            ->color('secondary')
            ->route($route ?? 'export');
    }

    /**
     * Ação de importar
     */
    public static function import(string $route = null): static
    {
        return static::make('import')
            ->label('Importar')
            ->icon('Upload')
            ->color('secondary')
            ->route($route ?? 'import');
    }

    /**
     * Ação de atualizar
     */
    public static function refresh(): static
    {
        return static::make('refresh')
            ->label('Atualizar')
            ->icon('RefreshCw')
            ->color('secondary')
            ->variant('outline');
    }

    /**
     * Ação de filtros
     */
    public static function filters(): static
    {
        return static::make('filters')
            ->label('Filtros')
            ->icon('Filter')
            ->color('secondary')
            ->variant('outline');
    }

    /**
     * Ação de configurações
     */
    public static function settings(): static
    {
        return static::make('settings')
            ->label('Configurações')
            ->icon('Settings')
            ->color('secondary')
            ->variant('ghost');
    }

    /**
     * Converter para array
     */
    public function toArray($record = null): array
    {
        $data = parent::toArray($record);
        
        return array_merge($data, [
            'type' => 'header',
            'position' => $this->position,
            'group' => $this->group,
            'isDropdown' => $this->isDropdown,
            'children' => array_map(fn($child) => $child->toArray(), $this->children),
        ]);
    }
} 
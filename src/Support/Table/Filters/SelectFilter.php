<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Filtro de seleção para React Frontend
 */
class SelectFilter extends Filter
{
    /**
     * Tipo do filtro
     */
    protected string $type = 'select';

    /**
     * Opções do filtro
     */
    protected array $options = [];

    /**
     * Se permite múltipla seleção
     */
    protected bool $multiple = false;

    /**
     * Configurar opções do filtro
     */
    public function options(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /**
     * Configurar múltipla seleção
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        $this->reactConfig['multiple'] = $multiple;
        return $this;
    }

    /**
     * Filtro de status com opções padrão
     */
    public static function status(array $customOptions = []): static
    {
        $defaultOptions = [
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'pending' => 'Pendente',
            'suspended' => 'Suspenso',
        ];

        $options = empty($customOptions) ? $defaultOptions : $customOptions;

        return static::make('status')
            ->label('Status')
            ->options($options)
            ->placeholder('Selecione um status...')
            ->icon('Filter')
            ->reactConfig([
                'component' => 'StatusFilter',
                'colors' => [
                    'active' => 'success',
                    'inactive' => 'secondary',
                    'pending' => 'warning',
                    'suspended' => 'danger',
                ],
            ]);
    }

    /**
     * Filtro de categoria
     */
    public static function category(string $model, string $labelField = 'name'): static
    {
        $options = [];
        if (class_exists($model)) {
            $options = $model::query()
                ->orderBy($labelField)
                ->pluck($labelField, 'id')
                ->toArray();
        }

        return static::make('category_id')
            ->label('Categoria')
            ->options($options)
            ->placeholder('Selecione uma categoria...')
            ->icon('Tag')
            ->searchable()
            ->reactConfig([
                'component' => 'CategoryFilter',
                'remote' => true,
                'searchEndpoint' => '/api/categories/search',
            ]);
    }

    /**
     * Filtro de relacionamento
     */
    public static function relationship(string $relation, string $model, string $labelField = 'name'): static
    {
        $options = [];
        if (class_exists($model)) {
            $options = $model::query()
                ->orderBy($labelField)
                ->pluck($labelField, 'id')
                ->toArray();
        }

        return static::make("{$relation}_id")
            ->label(ucfirst($relation))
            ->options($options)
            ->placeholder("Selecione um {$relation}...")
            ->icon('Link')
            ->searchable()
            ->reactConfig([
                'component' => 'RelationshipFilter',
                'relation' => $relation,
                'model' => $model,
                'labelField' => $labelField,
                'remote' => true,
                'searchEndpoint' => "/api/{$relation}/search",
            ]);
    }

    /**
     * Filtro de tipo com ícones
     */
    public static function type(array $types): static
    {
        return static::make('type')
            ->label('Tipo')
            ->options($types)
            ->placeholder('Selecione um tipo...')
            ->icon('Type')
            ->reactConfig([
                'component' => 'TypeFilter',
                'showIcons' => true,
                'icons' => [
                    'post' => 'FileText',
                    'page' => 'File',
                    'product' => 'Package',
                    'service' => 'Settings',
                ],
            ]);
    }

    /**
     * Filtro de prioridade
     */
    public static function priority(): static
    {
        return static::make('priority')
            ->label('Prioridade')
            ->options([
                'low' => 'Baixa',
                'medium' => 'Média',
                'high' => 'Alta',
                'urgent' => 'Urgente',
            ])
            ->placeholder('Selecione a prioridade...')
            ->icon('AlertTriangle')
            ->reactConfig([
                'component' => 'PriorityFilter',
                'colors' => [
                    'low' => 'secondary',
                    'medium' => 'primary',
                    'high' => 'warning',
                    'urgent' => 'danger',
                ],
                'showBadges' => true,
            ]);
    }

    /**
     * Filtro de tags (múltipla seleção)
     */
    public static function tags(array $tags = []): static
    {
        return static::make('tags')
            ->label('Tags')
            ->options($tags)
            ->multiple()
            ->placeholder('Selecione as tags...')
            ->icon('Tag')
            ->searchable()
            ->reactConfig([
                'component' => 'TagsFilter',
                'creatable' => true,
                'colorful' => true,
                'maxSelected' => 10,
            ]);
    }

    /**
     * Filtro de usuário
     */
    public static function user(string $model = 'App\\Models\\User'): static
    {
        $options = [];
        if (class_exists($model)) {
            $options = $model::query()
                ->orderBy('name')
                ->pluck('name', 'id')
                ->toArray();
        }

        return static::make('user_id')
            ->label('Usuário')
            ->options($options)
            ->placeholder('Selecione um usuário...')
            ->icon('User')
            ->searchable()
            ->reactConfig([
                'component' => 'UserFilter',
                'showAvatars' => true,
                'remote' => true,
                'searchEndpoint' => '/api/users/search',
            ]);
    }

    /**
     * Filtro personalizado com grupos
     */
    public function grouped(array $groups): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'component' => 'GroupedSelectFilter',
            'grouped' => true,
            'groups' => $groups,
        ]);
        return $this;
    }

    /**
     * Filtro com busca remota
     */
    public function remote(string $endpoint, array $params = []): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'remote' => true,
            'searchEndpoint' => $endpoint,
            'searchParams' => $params,
            'debounce' => 300,
            'minLength' => 2,
        ]);
        return $this;
    }

    /**
     * Filtro com criação de novos itens
     */
    public function creatable(bool $creatable = true): static
    {
        $this->reactConfig['creatable'] = $creatable;
        return $this;
    }

    /**
     * Aplicar filtro à query
     */
    protected function applyFilter(Builder $query, mixed $value): Builder
    {
        if (!$this->field) {
            return $query;
        }

        if ($this->multiple) {
            $values = is_array($value) ? $value : [$value];
            return $query->whereIn($this->field, $values);
        }

        return $query->where($this->field, $value);
    }

    /**
     * Converter para array (com configurações React)
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        return array_merge($data, [
            'options' => $this->options,
            'multiple' => $this->multiple,
            'frontend' => [
                'component' => 'SelectFilter',
                'type' => $this->type,
                'config' => array_merge($this->reactConfig, [
                    'options' => $this->options,
                    'multiple' => $this->multiple,
                ]),
            ],
        ]);
    }
} 
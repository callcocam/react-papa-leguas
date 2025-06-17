<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

/**
 * Coluna específica para relacionamentos (React Frontend)
 */
class RelationColumn extends Column
{
    /**
     * Tipo da coluna de relacionamento
     */
    protected string $relationType = 'relation';

    /**
     * Nome do relacionamento
     */
    protected ?string $relationshipName = null;

    /**
     * Campo do modelo relacionado
     */
    protected ?string $relationField = null;

    /**
     * Se é um campo pivot (BelongsToMany)
     */
    protected bool $isPivotField = false;

    /**
     * Configurações específicas para React
     */
    protected array $reactConfig = [
        'showRelationBadge' => false,
        'relationBadgeColor' => 'secondary',
        'showTimestamps' => false,
        'showPivotData' => false,
        'linkToRelated' => false,
        'relationModal' => [
            'enabled' => false,
            'size' => 'lg',
            'title' => '',
        ],
    ];

    /**
     * Definir tipo da relação
     */
    public function relationType(string $type): static
    {
        $this->relationType = $type;
        return $this;
    }

    /**
     * Definir relacionamento
     */
    public function relationship(string $name, string $field = 'name'): static
    {
        $this->relationshipName = $name;
        $this->relationField = $field;
        return $this;
    }

    /**
     * Marcar como campo pivot
     */
    public function pivot(bool $isPivot = true): static
    {
        $this->isPivotField = $isPivot;
        return $this;
    }

    /**
     * Configurar interface React
     */
    public function reactConfig(array $config): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, $config);
        return $this;
    }

    /**
     * Mostrar badge de relacionamento React
     */
    public function showRelationBadge(bool $show = true, string $color = 'secondary'): static
    {
        $this->reactConfig['showRelationBadge'] = $show;
        $this->reactConfig['relationBadgeColor'] = $color;
        return $this;
    }

    /**
     * Mostrar timestamps do relacionamento React
     */
    public function showTimestamps(bool $show = true): static
    {
        $this->reactConfig['showTimestamps'] = $show;
        return $this;
    }

    /**
     * Mostrar dados do pivot React
     */
    public function showPivotData(bool $show = true): static
    {
        $this->reactConfig['showPivotData'] = $show;
        return $this;
    }

    /**
     * Link para registro relacionado React
     */
    public function linkToRelated(bool $link = true): static
    {
        $this->reactConfig['linkToRelated'] = $link;
        return $this;
    }

    /**
     * Modal de relacionamento React
     */
    public function relationModal(bool $enabled = true, string $size = 'lg', string $title = ''): static
    {
        $this->reactConfig['relationModal'] = [
            'enabled' => $enabled,
            'size' => $size,
            'title' => $title,
        ];
        return $this;
    }

    /**
     * Coluna para mostrar dados do relacionamento
     */
    public static function relationData(string $relationship, string $field = 'name'): static
    {
        return static::make("{$relationship}.{$field}")
            ->relationship($relationship, $field)
            ->relationType('data')
            ->label(ucfirst($field))
            ->reactConfig([
                'showRelationBadge' => true,
                'linkToRelated' => true,
            ]);
    }

    /**
     * Coluna para contar relacionamentos
     */
    public static function relationCount(string $relationship): static
    {
        return static::make("{$relationship}_count")
            ->relationship($relationship)
            ->relationType('count')
            ->label("Total de " . ucfirst($relationship))
            ->formatUsing(fn($value) => number_format($value, 0, ',', '.'))
            ->reactConfig([
                'showRelationBadge' => true,
                'relationBadgeColor' => 'primary',
            ]);
    }

    /**
     * Coluna para timestamp do relacionamento
     */
    public static function relationTimestamp(string $relationship, string $field = 'created_at'): static
    {
        return static::make("pivot.{$field}")
            ->relationship($relationship, $field)
            ->relationType('timestamp')
            ->pivot()
            ->label(ucfirst(str_replace('_', ' ', $field)))
            ->formatUsing(function ($value) {
                if (!$value) return '-';
                return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
            })
            ->reactConfig([
                'showTimestamps' => true,
                'showRelationBadge' => true,
                'relationBadgeColor' => 'info',
            ]);
    }

    /**
     * Coluna para campo pivot personalizado
     */
    public static function pivotField(string $field, string $relationship = null): static
    {
        return static::make("pivot.{$field}")
            ->relationship($relationship ?? 'pivot', $field)
            ->relationType('pivot')
            ->pivot()
            ->label(ucfirst(str_replace('_', ' ', $field)))
            ->reactConfig([
                'showPivotData' => true,
                'showRelationBadge' => true,
                'relationBadgeColor' => 'warning',
            ]);
    }

    /**
     * Coluna para status do relacionamento
     */
    public static function relationStatus(string $relationship, string $field = 'status'): static
    {
        return static::make("pivot.{$field}")
            ->relationship($relationship, $field)
            ->relationType('status')
            ->pivot()
            ->label('Status')
            ->formatUsing(function ($value) {
                return match($value) {
                    'active' => 'Ativo',
                    'inactive' => 'Inativo',
                    'pending' => 'Pendente',
                    'approved' => 'Aprovado',
                    'rejected' => 'Rejeitado',
                    default => ucfirst($value)
                };
            })
            ->reactConfig([
                'showPivotData' => true,
                'showRelationBadge' => true,
                'relationBadgeColor' => 'success',
            ]);
    }

    /**
     * Coluna para ações de relacionamento inline
     */
    public static function relationActions(string $relationship): static
    {
        return static::make('relation_actions')
            ->relationship($relationship)
            ->relationType('actions')
            ->label('Ações')
            ->reactConfig([
                'component' => 'RelationInlineActions',
                'showRelationBadge' => false,
                'actions' => [
                    'view' => ['icon' => 'Eye', 'color' => 'secondary'],
                    'edit' => ['icon' => 'Edit', 'color' => 'primary'],
                    'detach' => ['icon' => 'Unlink', 'color' => 'warning'],
                ],
            ]);
    }

    /**
     * Coluna para link direto ao relacionamento
     */
    public static function relationLink(string $relationship, string $field = 'name', string $route = null): static
    {
        return static::make("{$relationship}.{$field}")
            ->relationship($relationship, $field)
            ->relationType('link')
            ->label(ucfirst($field))
            ->reactConfig([
                'component' => 'RelationLink',
                'linkToRelated' => true,
                'route' => $route,
                'showRelationBadge' => true,
                'relationBadgeColor' => 'info',
            ]);
    }

    /**
     * Coluna para imagem do relacionamento
     */
    public static function relationImage(string $relationship, string $field = 'avatar'): static
    {
        return static::make("{$relationship}.{$field}")
            ->relationship($relationship, $field)
            ->relationType('image')
            ->label('Imagem')
            ->reactConfig([
                'component' => 'RelationImage',
                'showRelationBadge' => false,
                'linkToRelated' => true,
                'imageSize' => 'sm',
                'fallbackIcon' => 'User',
            ]);
    }

    /**
     * Coluna para badge do relacionamento
     */
    public static function relationBadge(string $relationship, string $field = 'name', array $colors = []): static
    {
        return static::make("{$relationship}.{$field}")
            ->relationship($relationship, $field)
            ->relationType('badge')
            ->label(ucfirst($field))
            ->reactConfig([
                'component' => 'RelationBadge',
                'showRelationBadge' => true,
                'colors' => $colors,
                'linkToRelated' => true,
            ]);
    }

    /**
     * Converter para array (com configurações React)
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        return array_merge($data, [
            'relationType' => $this->relationType,
            'relationshipName' => $this->relationshipName,
            'relationField' => $this->relationField,
            'isPivotField' => $this->isPivotField,
            'reactConfig' => $this->reactConfig,
            'frontend' => [
                'component' => 'RelationColumn',
                'relationType' => $this->relationType,
                'config' => $this->reactConfig,
            ],
        ]);
    }
} 
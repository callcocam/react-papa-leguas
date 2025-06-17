<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * Filtro booleano para React Frontend
 */
class BooleanFilter extends Filter
{
    /**
     * Tipo do filtro
     */
    protected string $type = 'boolean';

    /**
     * Labels para true/false
     */
    protected array $labels = [
        'true' => 'Sim',
        'false' => 'Não',
    ];

    /**
     * Se deve incluir opção "Todos"
     */
    protected bool $includeAll = true;

    /**
     * Configurar labels
     */
    public function labels(string $trueLabel, string $falseLabel): static
    {
        $this->labels = [
            'true' => $trueLabel,
            'false' => $falseLabel,
        ];
        return $this;
    }

    /**
     * Incluir opção "Todos"
     */
    public function includeAll(bool $include = true): static
    {
        $this->includeAll = $include;
        return $this;
    }

    /**
     * Filtro de status ativo/inativo
     */
    public static function active(): static
    {
        return static::make('active')
            ->label('Status')
            ->labels('Ativo', 'Inativo')
            ->icon('ToggleLeft')
            ->reactConfig([
                'component' => 'ActiveFilter',
                'colors' => [
                    'true' => 'success',
                    'false' => 'secondary',
                ],
                'icons' => [
                    'true' => 'CheckCircle',
                    'false' => 'XCircle',
                ],
            ]);
    }

    /**
     * Filtro de publicado/não publicado
     */
    public static function published(): static
    {
        return static::make('published')
            ->label('Publicação')
            ->labels('Publicado', 'Rascunho')
            ->icon('Eye')
            ->reactConfig([
                'component' => 'PublishedFilter',
                'colors' => [
                    'true' => 'success',
                    'false' => 'warning',
                ],
                'icons' => [
                    'true' => 'Eye',
                    'false' => 'EyeOff',
                ],
            ]);
    }

    /**
     * Filtro de verificado/não verificado
     */
    public static function verified(): static
    {
        return static::make('verified')
            ->label('Verificação')
            ->labels('Verificado', 'Não Verificado')
            ->icon('Shield')
            ->reactConfig([
                'component' => 'VerifiedFilter',
                'colors' => [
                    'true' => 'success',
                    'false' => 'danger',
                ],
                'icons' => [
                    'true' => 'ShieldCheck',
                    'false' => 'Shield',
                ],
            ]);
    }

    /**
     * Filtro de favorito/não favorito
     */
    public static function favorite(): static
    {
        return static::make('is_favorite')
            ->label('Favoritos')
            ->labels('Favorito', 'Normal')
            ->icon('Heart')
            ->reactConfig([
                'component' => 'FavoriteFilter',
                'colors' => [
                    'true' => 'danger',
                    'false' => 'secondary',
                ],
                'icons' => [
                    'true' => 'Heart',
                    'false' => 'HeartOff',
                ],
            ]);
    }

    /**
     * Filtro de destaque/normal
     */
    public static function featured(): static
    {
        return static::make('featured')
            ->label('Destaque')
            ->labels('Em Destaque', 'Normal')
            ->icon('Star')
            ->reactConfig([
                'component' => 'FeaturedFilter',
                'colors' => [
                    'true' => 'warning',
                    'false' => 'secondary',
                ],
                'icons' => [
                    'true' => 'Star',
                    'false' => 'StarOff',
                ],
            ]);
    }

    /**
     * Filtro de arquivado/ativo
     */
    public static function archived(): static
    {
        return static::make('archived')
            ->label('Arquivo')
            ->labels('Arquivado', 'Ativo')
            ->icon('Archive')
            ->reactConfig([
                'component' => 'ArchivedFilter',
                'colors' => [
                    'true' => 'secondary',
                    'false' => 'primary',
                ],
                'icons' => [
                    'true' => 'Archive',
                    'false' => 'File',
                ],
            ]);
    }

    /**
     * Filtro personalizado com switch
     */
    public function asSwitch(): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'component' => 'SwitchFilter',
            'variant' => 'switch',
            'showLabels' => true,
        ]);
        return $this;
    }

    /**
     * Filtro personalizado com checkbox
     */
    public function asCheckbox(): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'component' => 'CheckboxFilter',
            'variant' => 'checkbox',
            'showLabel' => true,
        ]);
        return $this;
    }

    /**
     * Filtro personalizado com botões
     */
    public function asButtons(): static
    {
        $this->reactConfig = array_merge_recursive($this->reactConfig, [
            'component' => 'ButtonGroupFilter',
            'variant' => 'buttons',
            'showIcons' => true,
        ]);
        return $this;
    }

    /**
     * Aplicar filtro à query
     */
    protected function applyFilter(Builder $query, mixed $value): Builder
    {
        if (!$this->field || $value === null || $value === '') {
            return $query;
        }

        // Converter string para boolean
        if (is_string($value)) {
            $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $query->where($this->field, (bool) $value);
    }

    /**
     * Obter opções para o filtro
     */
    public function getOptions(): array
    {
        $options = [];

        if ($this->includeAll) {
            $options[''] = 'Todos';
        }

        $options['1'] = $this->labels['true'];
        $options['0'] = $this->labels['false'];

        return $options;
    }

    /**
     * Converter para array (com configurações React)
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        
        return array_merge($data, [
            'labels' => $this->labels,
            'includeAll' => $this->includeAll,
            'options' => $this->getOptions(),
            'frontend' => [
                'component' => 'BooleanFilter',
                'type' => $this->type,
                'config' => array_merge($this->reactConfig, [
                    'labels' => $this->labels,
                    'includeAll' => $this->includeAll,
                    'options' => $this->getOptions(),
                ]),
            ],
        ]);
    }
} 
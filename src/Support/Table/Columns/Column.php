<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

use Closure;

/**
 * Classe base para colunas da tabela com formatação avançada
 */
class Column
{
    /**
     * Chave da coluna
     */
    protected string $key;

    /**
     * Label da coluna
     */
    protected ?string $label = null;

    /**
     * Tipo da coluna
     */
    protected string $type = 'text';

    /**
     * Componente React para renderização
     */
    protected ?string $component = null;

    /**
     * Se a coluna é pesquisável
     */
    protected bool $searchable = false;

    /**
     * Se a coluna é ordenável
     */
    protected bool $sortable = false;

    /**
     * Se a coluna é visível
     */
    protected bool $visible = true;

    /**
     * Largura da coluna
     */
    protected ?string $width = null;

    /**
     * Alinhamento da coluna
     */
    protected string $align = 'left';

    /**
     * Função de formatação customizada
     */
    protected ?Closure $formatCallback = null;

    /**
     * Configurações de formatação
     */
    protected array $formatConfig = [];

    /**
     * Regras de validação
     */
    protected array $validationRules = [];

    /**
     * Permissões da coluna
     */
    protected array $permissions = [];

    /**
     * Metadados da coluna
     */
    protected array $meta = [];

    /**
     * Campos para busca (para relacionamentos)
     */
    protected array $searchFields = [];

    /**
     * Campo para ordenação (para relacionamentos)
     */
    protected ?string $sortField = null;

    /**
     * Relacionamentos para eager loading
     */
    protected array $relationships = [];

    public function __construct(string $key, ?string $label = null)
    {
        $this->key = $key;
        $this->label = $label ?? $this->generateLabel($key);
    }

    /**
     * Criar nova instância da coluna
     */
    public static function make(string $key, ?string $label = null): static
    {
        return new static($key, $label);
    }

    /**
     * Gerar label a partir da chave
     */
    protected function generateLabel(string $key): string
    {
        return ucfirst(str_replace(['_', '.'], ' ', $key));
    }

    /**
     * Definir label
     */
    public function label(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Definir tipo
     */
    public function type(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Definir componente React
     */
    public function component(string $component): static
    {
        $this->component = $component;
        return $this;
    }

    /**
     * Tornar coluna pesquisável
     */
    public function searchable(bool $searchable = true, array $fields = []): static
    {
        $this->searchable = $searchable;
        if (!empty($fields)) {
            $this->searchFields = $fields;
        }
        return $this;
    }

    /**
     * Tornar coluna ordenável
     */
    public function sortable(bool $sortable = true, string $field = null): static
    {
        $this->sortable = $sortable;
        if ($field) {
            $this->sortField = $field;
        }
        return $this;
    }

    /**
     * Definir visibilidade
     */
    public function visible(bool $visible = true): static
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * Ocultar coluna
     */
    public function hidden(): static
    {
        return $this->visible(false);
    }

    /**
     * Definir largura
     */
    public function width(string $width): static
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Definir alinhamento
     */
    public function align(string $align): static
    {
        $this->align = $align;
        return $this;
    }

    /**
     * Alinhar à esquerda
     */
    public function alignLeft(): static
    {
        return $this->align('left');
    }

    /**
     * Alinhar ao centro
     */
    public function alignCenter(): static
    {
        return $this->align('center');
    }

    /**
     * Alinhar à direita
     */
    public function alignRight(): static
    {
        return $this->align('right');
    }

    /**
     * Definir ícone da coluna
     */
    public function icon(string $icon, string $position = 'before'): static
    {
        $this->formatConfig['icon'] = [
            'name' => $icon,
            'position' => $position,
        ];
        return $this;
    }

    /**
     * Definir cor da coluna
     */
    public function color(string $color): static
    {
        $this->formatConfig['color'] = $color;
        return $this;
    }

    /**
     * Definir tooltip
     */
    public function tooltip(string $tooltip): static
    {
        $this->formatConfig['tooltip'] = $tooltip;
        return $this;
    }

    /**
     * Renderizar como imagem
     */
    public function renderAsImage(bool $renderAsImage = true): static
    {
        $this->formatConfig['renderAsImage'] = $renderAsImage;
        return $this;
    }

    /**
     * Limitar texto
     */
    public function limit(int $limit): static
    {
        $this->formatConfig['limit'] = $limit;
        return $this;
    }

    /**
     * Tornar texto copiável
     */
    public function copyable(bool $copyable = true): static
    {
        $this->formatConfig['copyable'] = $copyable;
        return $this;
    }

    /**
     * Usar fonte monoespaçada
     */
    public function fontMono(bool $fontMono = true): static
    {
        $this->formatConfig['fontMono'] = $fontMono;
        return $this;
    }

    /**
     * Formatar como moeda
     */
    public function currency(string $currency = 'BRL'): static
    {
        $this->formatConfig['currency'] = $currency;
        return $this;
    }

    /**
     * Definir placeholder
     */
    public function placeholder(string $placeholder): static
    {
        $this->formatConfig['placeholder'] = $placeholder;
        return $this;
    }

    /**
     * Definir formatação customizada
     */
    public function format(Closure $callback): static
    {
        $this->formatCallback = $callback;
        return $this;
    }

    /**
     * Definir configurações de formatação
     */
    public function formatConfig(array $config): static
    {
        $this->formatConfig = array_merge($this->formatConfig, $config);
        return $this;
    }

    /**
     * Definir regras de validação
     */
    public function validation(array $rules): static
    {
        $this->validationRules = $rules;
        return $this;
    }

    /**
     * Definir permissões
     */
    public function permissions(array $permissions): static
    {
        $this->permissions = $permissions;
        return $this;
    }

    /**
     * Definir metadados
     */
    public function meta(array $meta): static
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Definir relacionamentos para eager loading
     */
    public function with(array $relationships): static
    {
        $this->relationships = $relationships;
        return $this;
    }

    /**
     * Configurar coluna com array de opções
     */
    public function configure(array $options): static
    {
        foreach ($options as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }
        return $this;
    }

    /**
     * Formatar valor da coluna
     */
    public function formatValue($value, $record): mixed
    {
        // Aplicar formatação customizada se definida
        if ($this->formatCallback) {
            return call_user_func($this->formatCallback, $value, $record, $this);
        }

        // Aplicar formatação padrão do tipo
        return $this->applyDefaultFormatting($value, $record);
    }

    /**
     * Aplicar formatação padrão baseada no tipo
     */
    protected function applyDefaultFormatting($value, $record): mixed
    {
        return $value;
    }

    // Getters
    public function getKey(): string
    {
        return $this->key;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getComponent(): ?string
    {
        return $this->component;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isVisible(): bool
    {
        return $this->visible;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function getAlign(): string
    {
        return $this->align;
    }

    public function getFormatConfig(): array
    {
        return $this->formatConfig;
    }

    public function getValidationRules(): array
    {
        return $this->validationRules;
    }

    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function getMeta(): array
    {
        return $this->meta;
    }

    public function getSearchFields(): array
    {
        return $this->searchFields ?: [$this->key];
    }

    public function getSortField(): string
    {
        return $this->sortField ?: $this->key;
    }

    public function getRelationships(): array
    {
        return $this->relationships;
    }
} 
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Classe base para filtros da tabela (React Frontend)
 */
abstract class Filter
{
    /**
     * ID único do filtro
     */
    protected string $id;

    /**
     * Label do filtro
     */
    protected string $label;

    /**
     * Tipo do filtro
     */
    protected string $type = 'text';

    /**
     * Campo da query
     */
    protected ?string $field = null;

    /**
     * Valor padrão do filtro
     */
    protected mixed $defaultValue = null;

    /**
     * Valor atual do filtro
     */
    protected mixed $value = null;

    /**
     * Se o filtro é obrigatório
     */
    protected bool $required = false;

    /**
     * Se o filtro está visível
     */
    protected bool $visible = true;

    /**
     * Closure para aplicar o filtro
     */
    protected ?Closure $applyUsing = null;

    /**
     * Configurações específicas para React
     */
    protected array $reactConfig = [
        'placeholder' => '',
        'helpText' => '',
        'clearable' => true,
        'searchable' => false,
        'multiple' => false,
        'size' => 'default',
        'variant' => 'default',
        'icon' => null,
        'loading' => false,
        'disabled' => false,
        'validation' => [
            'required' => false,
            'min' => null,
            'max' => null,
            'pattern' => null,
            'message' => '',
        ],
        'ui' => [
            'showLabel' => true,
            'showClear' => true,
            'showSearch' => false,
            'showCount' => false,
            'compact' => false,
        ],
    ];

    /**
     * Metadados do filtro
     */
    protected array $metadata = [];

    /**
     * Construtor
     */
    public function __construct(string $id)
    {
        $this->id = $id;
        $this->label = ucfirst(str_replace(['_', '-'], ' ', $id));
        $this->field = $id;
    }

    /**
     * Criar nova instância
     */
    public static function make(string $id): static
    {
        return new static($id);
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
     * Definir campo da query
     */
    public function field(string $field): static
    {
        $this->field = $field;
        return $this;
    }

    /**
     * Definir valor padrão
     */
    public function default(mixed $value): static
    {
        $this->defaultValue = $value;
        return $this;
    }

    /**
     * Definir valor atual
     */
    public function value(mixed $value): static
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Marcar como obrigatório
     */
    public function required(bool $required = true): static
    {
        $this->required = $required;
        $this->reactConfig['validation']['required'] = $required;
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
     * Definir visibilidade condicional
     */
    public function visibleWhen(Closure $callback): static
    {
        $this->visible = $callback();
        return $this;
    }

    /**
     * Definir como aplicar o filtro
     */
    public function applyUsing(Closure $callback): static
    {
        $this->applyUsing = $callback;
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
     * Configurar placeholder React
     */
    public function placeholder(string $placeholder): static
    {
        $this->reactConfig['placeholder'] = $placeholder;
        return $this;
    }

    /**
     * Configurar texto de ajuda React
     */
    public function helpText(string $helpText): static
    {
        $this->reactConfig['helpText'] = $helpText;
        return $this;
    }

    /**
     * Permitir limpeza React
     */
    public function clearable(bool $clearable = true): static
    {
        $this->reactConfig['clearable'] = $clearable;
        return $this;
    }

    /**
     * Permitir busca React
     */
    public function searchable(bool $searchable = true): static
    {
        $this->reactConfig['searchable'] = $searchable;
        return $this;
    }

    /**
     * Permitir múltipla seleção React
     */
    public function multiple(bool $multiple = true): static
    {
        $this->reactConfig['multiple'] = $multiple;
        return $this;
    }

    /**
     * Definir tamanho React
     */
    public function size(string $size): static
    {
        $this->reactConfig['size'] = $size;
        return $this;
    }

    /**
     * Definir variante React
     */
    public function variant(string $variant): static
    {
        $this->reactConfig['variant'] = $variant;
        return $this;
    }

    /**
     * Definir ícone React
     */
    public function icon(string $icon): static
    {
        $this->reactConfig['icon'] = $icon;
        return $this;
    }

    /**
     * Definir estado de loading React
     */
    public function loading(bool $loading = true): static
    {
        $this->reactConfig['loading'] = $loading;
        return $this;
    }

    /**
     * Definir estado desabilitado React
     */
    public function disabled(bool $disabled = true): static
    {
        $this->reactConfig['disabled'] = $disabled;
        return $this;
    }

    /**
     * Configurar validação React
     */
    public function validation(array $rules): static
    {
        $this->reactConfig['validation'] = array_merge($this->reactConfig['validation'], $rules);
        return $this;
    }

    /**
     * Configurar interface de usuário React
     */
    public function ui(array $ui): static
    {
        $this->reactConfig['ui'] = array_merge($this->reactConfig['ui'], $ui);
        return $this;
    }

    /**
     * Adicionar metadados
     */
    public function metadata(array $metadata): static
    {
        $this->metadata = array_merge($this->metadata, $metadata);
        return $this;
    }

    /**
     * Obter ID do filtro
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * Obter label do filtro
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Obter tipo do filtro
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Obter campo da query
     */
    public function getField(): ?string
    {
        return $this->field;
    }

    /**
     * Obter valor atual
     */
    public function getValue(): mixed
    {
        return $this->value ?? $this->defaultValue;
    }

    /**
     * Verificar se tem valor
     */
    public function hasValue(): bool
    {
        $value = $this->getValue();
        return $value !== null && $value !== '' && $value !== [];
    }

    /**
     * Verificar se é obrigatório
     */
    public function isRequired(): bool
    {
        return $this->required;
    }

    /**
     * Verificar se está visível
     */
    public function isVisible(): bool
    {
        return $this->visible;
    }

    /**
     * Aplicar filtro à query
     */
    public function apply(Builder $query): Builder
    {
        if (!$this->hasValue()) {
            return $query;
        }

        if ($this->applyUsing) {
            return ($this->applyUsing)($query, $this->getValue(), $this->field);
        }

        return $this->applyFilter($query, $this->getValue());
    }

    /**
     * Aplicar filtro específico (implementado pelas subclasses)
     */
    abstract protected function applyFilter(Builder $query, mixed $value): Builder;

    /**
     * Converter para array (com configurações React)
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'type' => $this->type,
            'field' => $this->field,
            'value' => $this->getValue(),
            'defaultValue' => $this->defaultValue,
            'required' => $this->required,
            'visible' => $this->visible,
            'hasValue' => $this->hasValue(),
            'reactConfig' => $this->reactConfig,
            'metadata' => $this->metadata,
            'frontend' => [
                'component' => 'Filter',
                'type' => $this->type,
                'config' => $this->reactConfig,
            ],
        ];
    }
} 
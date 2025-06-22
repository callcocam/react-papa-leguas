<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Filters;

use Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToLabel;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToHidden;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToAttributes;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToPlaceholder;
use Closure;

/**
 * Classe base abstrata para filtros de tabela
 */
abstract class Filter
{
    use EvaluatesClosures,
        BelongsToLabel,
        BelongsToHidden,
        BelongsToAttributes,
        BelongsToPlaceholder;

    protected string $key;
    protected mixed $value = null;
    protected mixed $defaultValue = null;
    protected array $config = [];
    protected bool $multiple = false;
    protected ?Closure $queryCallback = null;
    protected ?Closure $valueCallback = null;

    /**
     * Constructor
     */
    public function __construct(string $key, ?string $label = null)
    {
        $this->key = $key;
        $this->label = $label ?? $this->generateLabel($key);
    }

    /**
     * Criar instância do filtro
     */
    public static function make(string $key, ?string $label = null): static
    {
        return new static($key, $label);
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
     * Permitir múltiplos valores
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * Definir callback personalizado para query
     */
    public function queryUsing(Closure $callback): static
    {
        $this->queryCallback = $callback;
        return $this;
    }

    /**
     * Definir callback para processar valor
     */
    public function valueUsing(Closure $callback): static
    {
        $this->valueCallback = $callback;
        return $this;
    }

    /**
     * Definir configuração específica
     */
    public function config(string $key, mixed $value): static
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Obter configuração específica
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Definir valor do filtro
     */
    public function setValue(mixed $value): static
    {
        // Processar valor com callback se definido
        if ($this->valueCallback) {
            $value = $this->evaluate($this->valueCallback, [
                'value' => $value,
                'filter' => $this,
            ]);
        }

        $this->value = $value;
        return $this;
    }

    /**
     * Obter valor do filtro
     */
    public function getValue(): mixed
    {
        return $this->value ?? $this->defaultValue;
    }

    /**
     * Verificar se filtro tem valor
     */
    public function hasValue(): bool
    {
        $value = $this->getValue();
        
        if (is_null($value)) {
            return false;
        }
        
        if (is_string($value) && trim($value) === '') {
            return false;
        }
        
        if (is_array($value) && empty($value)) {
            return false;
        }
        
        return true;
    }

    /**
     * Aplicar filtro à query
     */
    public function applyToQuery($query, mixed $value = null): void
    {
        $filterValue = $value ?? $this->getValue();
        
        if (!$this->hasValue() && is_null($value)) {
            return;
        }

        // Usar callback personalizado se definido
        if ($this->queryCallback) {
            $this->evaluate($this->queryCallback, [
                'query' => $query,
                'value' => $filterValue,
                'filter' => $this,
            ]);
            return;
        }

        // Aplicar filtro específico da classe filha
        $this->apply($query, $filterValue);
    }

    /**
     * Método abstrato para aplicar filtro específico
     * Deve ser implementado pelas classes filhas
     */
    abstract protected function apply($query, mixed $value): void;

    /**
     * Obter tipo do filtro
     * Deve ser implementado pelas classes filhas
     */
    abstract public function getType(): string;

    /**
     * Converter filtro para array para serialização
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->getLabel() ?? $this->generateLabel($this->key),
            'type' => $this->getType(),
            'value' => $this->getValue(),
            'default_value' => $this->defaultValue,
            'multiple' => $this->multiple,
            'placeholder' => $this->getPlaceholder(),
            'hidden' => $this->isHidden(),
            'attributes' => $this->getAttributes(),
            'config' => $this->config,
        ];
    }

    /**
     * Gerar label automaticamente baseado na key
     */
    protected function generateLabel(string $key): string
    {
        return ucfirst(str_replace(['_', '-'], ' ', $key));
    }

    /**
     * Getters
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Obtém o label do filtro (usa trait BelongsToLabel com fallback)
     */
    public function getLabel(): ?string
    {
        // Usa trait se label está definido, senão gera label automático
        if ($this->label !== null) {
            $result = $this->evaluate($this->label, $this->context ?? []);
            return is_string($result) ? $result : $this->generateLabel($this->key);
        }
        
        return $this->generateLabel($this->key);
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }
} 
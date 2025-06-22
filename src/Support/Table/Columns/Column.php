<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

use Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;
use Callcocam\ReactPapaLeguas\Support\Concerns\FactoryPattern;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToLabel;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToHidden;
use Callcocam\ReactPapaLeguas\Support\Concerns\BelongsToAttributes;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\Contracts\CastInterface;
use Closure;

abstract class Column
{
    use EvaluatesClosures, 
        FactoryPattern,
        BelongsToLabel,
        BelongsToHidden,
        BelongsToAttributes;

    protected string $key;
    protected bool $sortable = true;
    protected bool $searchable = true;
    protected ?string $width = null;
    protected ?string $alignment = null;
    protected ?Closure $formatCallback = null;
    protected ?Closure $valueCallback = null;
    
    /**
     * Casts específicos da coluna
     */
    protected array $casts = [];
    protected bool $disableAutoCasts = false;

    public function __construct(string $key, ?string $label = null)
    {
        $this->key = $key;
        $this->label = $label ?? $this->generateLabel($key);
        $this->evaluationIdentifier = 'column';
    }

    /**
     * Definir se a coluna é ordenável
     */
    public function sortable(bool $sortable = true): static
    {
        $this->sortable = $sortable;
        return $this;
    }

    /**
     * Definir se a coluna é pesquisável
     */
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        return $this;
    }

    /**
     * Definir largura da coluna
     */
    public function width(string $width): static
    {
        $this->width = $width;
        return $this;
    }

    /**
     * Definir alinhamento da coluna
     */
    public function alignment(string $alignment): static
    {
        $this->alignment = $alignment;
        return $this;
    }

    /**
     * Definir callback de formatação personalizada
     */
    public function formatUsing(Closure $callback): static
    {
        $this->formatCallback = $callback;
        return $this;
    }

    /**
     * Definir callback para obter o valor
     */
    public function getValueUsing(Closure $callback): static
    {
        $this->valueCallback = $callback;
        return $this;
    }

    public function rendererOptions(array $options): static
    {
        return $this->attributes(['rendererOptions' => $options]);
    }

    public function renderAs(string $type): static
    {
        return $this->attributes(['renderAs' => $type]);
    }

    /**
     * Adicionar cast específico para esta coluna
     */
    public function cast(CastInterface $cast): static
    {
        $this->casts[] = $cast;
        return $this;
    }

    /**
     * Adicionar múltiplos casts para esta coluna
     */
    public function casts(array $casts): static
    {
        foreach ($casts as $cast) {
            if ($cast instanceof CastInterface) {
                $this->cast($cast);
            }
        }
        return $this;
    }

    /**
     * Desabilitar casts automáticos para esta coluna
     */
    public function disableAutoCasts(bool $disable = true): static
    {
        $this->disableAutoCasts = $disable;
        return $this;
    }

    /**
     * Habilitar casts automáticos para esta coluna
     */
    public function enableAutoCasts(): static
    {
        $this->disableAutoCasts = false;
        return $this;
    }

    /**
     * Obter o valor da coluna para uma linha específica
     */
    public function getValue($row): mixed
    {
        if ($this->valueCallback) {
            return $this->evaluate($this->valueCallback, ['row' => $row, 'column' => $this]);
        }

        return data_get($row, $this->key);
    }

    /**
     * Formatar o valor da coluna
     */
    public function formatValue($row, mixed $castedValue = null): mixed
    {
        // Usar valor com cast aplicado se fornecido, senão obter valor normal
        $value = $castedValue !== null ? $castedValue : $this->getValue($row);

        // Aplicar callback de formatação personalizada se definido
        if ($this->formatCallback) {
            return $this->evaluate($this->formatCallback, [
                'value' => $value,
                'row' => $row,
                'column' => $this
            ]);
        }

        // Aplicar formatação específica do tipo de coluna
        return $this->format($value, $row);
    }

    /**
     * Método abstrato para formatação específica do tipo de coluna
     * Deve ser implementado pelas classes filhas
     */
    abstract protected function format(mixed $value, $row): mixed;

    /**
     * Obter o tipo da coluna
     * Deve ser implementado pelas classes filhas
     */
    abstract public function getType(): string;

    /**
     * Converter a coluna para array para serialização
     */
    public function toArray(): array
    {
        $attributes = $this->getAttributes();
        
        return [
            'key' => $this->key,
            'label' => $this->getLabel() ?? $this->generateLabel($this->key),
            'type' => $this->getType(),
            'renderAs' => $attributes['renderAs'] ?? null,
            'rendererOptions' => $attributes['rendererOptions'] ?? [],
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'hidden' => $this->isHidden(),
            'width' => $this->width,
            'alignment' => $this->alignment,
            'attributes' => $attributes,
            'casts_count' => count($this->casts),
            'auto_casts_disabled' => $this->disableAutoCasts,
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
     * Obtém o label da coluna (usa trait BelongsToLabel com fallback)
     */
    public function getLabel(): string
    {
        // Usa trait se label está definido, senão gera label automático
        if ($this->label !== null) {
            return $this->evaluate($this->label, $this->context ?? []);
        }
        
        return $this->generateLabel($this->key);
    }

    /**
     * Get all fields required by this column for rendering.
     * By default, it's just the column's key.
     * Can be overridden by complex columns to declare dependencies.
     */
    public function getRequiredFields(): array
    {
        return [$this->key];
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function getAlignment(): ?string
    {
        return $this->alignment;
    }

    /**
     * Obter casts específicos da coluna
     */
    public function getCasts(): array
    {
        return $this->casts;
    }

    /**
     * Verificar se casts automáticos estão desabilitados
     */
    public function isAutoCastsDisabled(): bool
    {
        return $this->disableAutoCasts;
    }

    /**
     * Verificar se a coluna tem casts específicos
     */
    public function hasCasts(): bool
    {
        return !empty($this->casts);
    }

    /**
     * Aplicar casts específicos da coluna a um valor
     */
    public function applyCasts(mixed $value, array $context = []): mixed
    {
        if (empty($this->casts)) {
            return $value;
        }

        $result = $value;
        
        // Aplicar cada cast específico da coluna
        foreach ($this->casts as $cast) {
            if ($cast->canCast($result)) {
                $result = $cast->cast($result, array_merge($context, [
                    'column' => $this,
                    'column_key' => $this->key,
                ]));
            }
        }

        return $result;
    }

    /**
     * Obter configuração de casts para serialização
     */
    public function getCastsConfig(): array
    {
        return [
            'has_casts' => $this->hasCasts(),
            'casts_count' => count($this->casts),
            'auto_casts_disabled' => $this->disableAutoCasts,
            'cast_types' => array_map(fn($cast) => $cast->getType(), $this->casts),
        ];
    }
} 
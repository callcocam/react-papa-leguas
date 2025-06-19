<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

use Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;
use Closure;

abstract class Column
{
    use EvaluatesClosures;

    protected string $key;
    protected string $label;
    protected bool $sortable = true;
    protected bool $searchable = true;
    protected bool $hidden = false;
    protected ?string $width = null;
    protected ?string $alignment = null;
    protected ?Closure $formatCallback = null;
    protected ?Closure $valueCallback = null;
    protected array $attributes = [];

    public function __construct(string $key, ?string $label = null)
    {
        $this->key = $key;
        $this->label = $label ?? $this->generateLabel($key);
        $this->evaluationIdentifier = 'column';
    }

    /**
     * Método estático para criar uma nova instância
     */
    public static function make(string $key, ?string $label = null): static
    {
        return new static($key, $label);
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
     * Definir se a coluna está oculta
     */
    public function hidden(bool $hidden = true): static
    {
        $this->hidden = $hidden;
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

    /**
     * Adicionar atributos personalizados
     */
    public function attributes(array $attributes): static
    {
        $this->attributes = array_merge($this->attributes, $attributes);
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
    public function formatValue($row): mixed
    {
        $value = $this->getValue($row);

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
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->getType(),
            'sortable' => $this->sortable,
            'searchable' => $this->searchable,
            'hidden' => $this->hidden,
            'width' => $this->width,
            'alignment' => $this->alignment,
            'attributes' => $this->attributes,
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

    public function getLabel(): string
    {
        return $this->label;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function getWidth(): ?string
    {
        return $this->width;
    }

    public function getAlignment(): ?string
    {
        return $this->alignment;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }
} 
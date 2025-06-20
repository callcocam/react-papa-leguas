<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

use Callcocam\ReactPapaLeguas\Enums\BaseStatus;

class TextColumn extends Column
{
    protected ?int $limit = null;
    protected string $limitSuffix = '...';
    protected bool $wrap = false;
    protected bool $copyable = false;
    protected ?string $placeholder = null;

    /**
     * Limitar número de caracteres
     */
    public function limit(int $limit, string $suffix = '...'): static
    {
        $this->limit = $limit;
        $this->limitSuffix = $suffix;
        return $this;
    }

    /**
     * Permitir quebra de linha
     */
    public function wrap(bool $wrap = true): static
    {
        $this->wrap = $wrap;
        return $this;
    }

    /**
     * Permitir copiar texto
     */
    public function copyable(bool $copyable = true): static
    {
        $this->copyable = $copyable;
        return $this;
    }

    /**
     * Definir placeholder para valores vazios
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        return $this;
    }

    /**
     * Formatar valor como texto
     */
    protected function format(mixed $value, $row): mixed
    {
        $baseResult = is_array($value) ? $value : ['value' => $value];
        $textForFormatting = $baseResult['formatted'] ?? ($baseResult['value'] ?? '');

        if ($textForFormatting instanceof BaseStatus) {
            $baseResult['value'] = $textForFormatting->value;
            $textForFormatting = $textForFormatting->label();
        } elseif (is_object($textForFormatting) && method_exists($textForFormatting, 'name')) {
            $textForFormatting = $textForFormatting->value ?? $textForFormatting->name;
        } elseif (is_array($textForFormatting)) {
            $textForFormatting = $textForFormatting['label'] ?? $textForFormatting['name'] ?? '';
        }

        $text = (string) $textForFormatting;
        
        if (is_null($value) || $text === '') {
            return [
                'value' => null,
                'type' => 'text',
                'formatted' => $this->placeholder ?? '',
                'wrap' => $this->wrap,
                'copyable' => $this->copyable,
                'placeholder' => $this->placeholder
            ];
        }

        if ($this->limit && mb_strlen($text) > $this->limit) {
            $text = mb_substr($text, 0, $this->limit) . $this->limitSuffix;
        }

        return array_merge($baseResult, [
            'type' => 'text',
            'formatted' => $text,
            'wrap' => $this->wrap,
            'copyable' => $this->copyable,
            'placeholder' => $this->placeholder
        ]);
    }

    /**
     * Obter tipo da coluna
     */
    public function getType(): string
    {
        return 'text';
    }

    /**
     * Converter para array incluindo configurações específicas
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'limit' => $this->limit,
            'limitSuffix' => $this->limitSuffix,
            'wrap' => $this->wrap,
            'copyable' => $this->copyable,
            'placeholder' => $this->placeholder,
        ]);
    }
} 
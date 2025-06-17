<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

/**
 * Coluna de imagem
 */
class ImageColumn extends Column
{
    protected string $type = 'image';

    /**
     * Tamanho da imagem
     */
    public function size(int $width, int $height = null): static
    {
        $this->formatConfig['size'] = [
            'width' => $width,
            'height' => $height ?? $width,
        ];
        return $this;
    }

    /**
     * Imagem redonda
     */
    public function rounded(bool $rounded = true): static
    {
        $this->formatConfig['rounded'] = $rounded;
        return $this;
    }

    /**
     * Imagem como avatar
     */
    public function avatar(): static
    {
        return $this->size(40)->rounded();
    }

    /**
     * URL padrão se não houver imagem
     */
    public function defaultUrl(string $url): static
    {
        $this->formatConfig['defaultUrl'] = $url;
        return $this;
    }

    /**
     * Alt text
     */
    public function alt(string $alt): static
    {
        $this->formatConfig['alt'] = $alt;
        return $this;
    }

    /**
     * Aplicar formatação padrão
     */
    protected function applyDefaultFormatting($value, $record): mixed
    {
        return [
            'url' => $value ?: ($this->formatConfig['defaultUrl'] ?? null),
            'alt' => $this->formatConfig['alt'] ?? 'Imagem',
            'size' => $this->formatConfig['size'] ?? ['width' => 50, 'height' => 50],
            'rounded' => $this->formatConfig['rounded'] ?? false,
        ];
    }
} 
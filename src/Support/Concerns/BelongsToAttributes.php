<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;

trait BelongsToAttributes
{
    protected array $attributes = [];
    protected ?Closure $attributesCallback = null;

    /**
     * Define atributos HTML personalizados
     */
    public function attributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Define atributos usando callback
     */
    public function attributesUsing(Closure $callback): self
    {
        $this->attributesCallback = $callback;
        return $this;
    }

    /**
     * Adiciona um atributo específico
     */
    public function attribute(string $key, mixed $value): self
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /**
     * Remove um atributo específico
     */
    public function removeAttribute(string $key): self
    {
        unset($this->attributes[$key]);
        return $this;
    }

    /**
     * Define classes CSS
     */
    public function class(string $classes): self
    {
        return $this->attribute('class', $classes);
    }

    /**
     * Adiciona classes CSS às existentes
     */
    public function addClass(string $classes): self
    {
        $existingClasses = $this->attributes['class'] ?? '';
        $this->attributes['class'] = trim($existingClasses . ' ' . $classes);
        return $this;
    }

    /**
     * Define atributo data-*
     */
    public function data(string $key, mixed $value): self
    {
        return $this->attribute("data-{$key}", $value);
    }

    /**
     * Obtém os atributos avaliados
     */
    public function getAttributes(): array
    {
        $staticAttributes = $this->attributes;

        if ($this->attributesCallback) {
            $dynamicAttributes = $this->evaluate($this->attributesCallback, $this->context ?? []);
            
            if (is_array($dynamicAttributes)) {
                $staticAttributes = array_merge($staticAttributes, $dynamicAttributes);
            }
        }

        return $staticAttributes;
    }

    /**
     * Obtém um atributo específico
     */
    public function getAttribute(string $key, mixed $default = null): mixed
    {
        return $this->getAttributes()[$key] ?? $default;
    }

    /**
     * Verifica se tem atributos definidos
     */
    public function hasAttributes(): bool
    {
        return !empty($this->getAttributes());
    }

    /**
     * Verifica se tem um atributo específico
     */
    public function hasAttribute(string $key): bool
    {
        return array_key_exists($key, $this->getAttributes());
    }
} 
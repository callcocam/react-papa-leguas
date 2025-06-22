<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

use Closure;
use Illuminate\Support\Str;

trait BelongsToKey
{
    protected Closure|string|null $key = null;

    /**
     * Define a chave
     */
    public function key(Closure|string $key): self
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Define a chave usando callback
     */
    public function keyUsing(Closure $callback): self
    {
        $this->key = $callback;
        return $this;
    }

    /**
     * Gera chave automaticamente baseada no nome/label
     */
    public function autoKey(): self
    {
        $this->key = function () {
            $source = null;
            
            // Tenta usar name primeiro, depois label
            if (method_exists($this, 'getName') && $this->hasName()) {
                $source = $this->getName();
            } elseif (method_exists($this, 'getLabel') && $this->hasLabel()) {
                $source = $this->getLabel();
            }
            
            if ($source) {
                return Str::slug($source, '_');
            }
            
            return 'key_' . uniqid();
        };
        
        return $this;
    }

    /**
     * Define chave como slug baseado em texto
     */
    public function slugKey(string $text): self
    {
        return $this->key(Str::slug($text, '_'));
    }

    /**
     * Gera chave única com prefixo
     */
    public function uniqueKey(string $prefix = 'item'): self
    {
        return $this->key($prefix . '_' . uniqid());
    }

    /**
     * Obtém a chave avaliada
     */
    public function getKey(): ?string
    {
        if ($this->key === null) {
            return null;
        }

        $key = $this->evaluate($this->key, $this->context ?? []);
        
        // Sanitiza a chave para garantir formato válido
        return $this->sanitizeKey($key);
    }

    /**
     * Verifica se tem chave definida
     */
    public function hasKey(): bool
    {
        return $this->key !== null;
    }

    /**
     * Verifica se tem chave específica
     */
    public function hasKeyValue(string $key): bool
    {
        return $this->getKey() === $key;
    }

    /**
     * Obtém chave ou gera uma padrão
     */
    public function getKeyOrDefault(string $default = null): string
    {
        return $this->getKey() ?? $default ?? 'default_' . uniqid();
    }

    /**
     * Sanitiza a chave para formato válido
     */
    protected function sanitizeKey(?string $key): ?string
    {
        if ($key === null) {
            return null;
        }

        // Remove caracteres especiais e converte para snake_case
        $sanitized = Str::slug($key, '_');
        
        // Garante que não comece com número
        if (is_numeric(substr($sanitized, 0, 1))) {
            $sanitized = 'key_' . $sanitized;
        }
        
        return $sanitized ?: null;
    }
} 
<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

trait HasDefaultConfig
{
    /**
     * Configurações da instância
     */
    protected array $config = [];

    /**
     * Obter configuração específica
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
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
     * Definir múltiplas configurações
     */
    public function setConfig(array $config): static
    {
        $this->config = array_merge($this->config, $config);
        return $this;
    }

    /**
     * Obter todas as configurações
     */
    public function getAllConfig(): array
    {
        return $this->config;
    }

    /**
     * Inicializar configurações com valores padrão
     */
    protected function initializeConfig(array $config = []): void
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }

    /**
     * Obter configuração padrão (deve ser implementado pelas classes)
     */
    abstract protected function getDefaultConfig(): array;
} 
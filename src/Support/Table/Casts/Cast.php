<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Casts;

use Callcocam\ReactPapaLeguas\Support\Concerns\EvaluatesClosures;
use Callcocam\ReactPapaLeguas\Support\Concerns\FactoryPattern;
use Callcocam\ReactPapaLeguas\Support\Table\Casts\Contracts\CastInterface;
use Closure;
use Illuminate\Support\Facades\Log;

abstract class Cast implements CastInterface
{
    use EvaluatesClosures, FactoryPattern;

    /**
     * Configurações do cast
     */
    protected array $config = [];

    /**
     * Prioridade padrão do cast
     */
    protected int $priority = 50;

    /**
     * Se o cast deve ser aplicado automaticamente
     */
    protected bool $automatic = true;

    /**
     * Cache de resultados
     */
    protected array $cache = [];

    /**
     * Callbacks personalizados
     */
    protected ?Closure $castUsing = null;
    protected ?Closure $canCastUsing = null;

    public function __construct(array $config = [])
    {
        $this->config = array_merge($this->getDefaultConfig(), $config);
    }



    /**
     * Define callback personalizado para casting
     */
    public function castUsing(Closure $callback): static
    {
        $this->castUsing = $callback;
        return $this;
    }

    /**
     * Define callback personalizado para verificação de compatibilidade
     */
    public function canCastUsing(Closure $callback): static
    {
        $this->canCastUsing = $callback;
        return $this;
    }

    /**
     * Define se o cast é automático
     */
    public function automatic(bool $automatic = true): static
    {
        $this->automatic = $automatic;
        return $this;
    }

    /**
     * Define a prioridade do cast
     */
    public function priority(int $priority): static
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * Define configuração específica
     */
    public function config(string $key, mixed $value): static
    {
        $this->config[$key] = $value;
        return $this;
    }

    /**
     * Obtém configuração específica
     */
    public function getConfig(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function cast(mixed $value, array $context = []): mixed
    {
        // Usar callback personalizado se definido
        if ($this->castUsing) {
            return $this->evaluate($this->castUsing, [
                'value' => $value,
                'context' => $context,
                'cast' => $this,
            ]);
        }

        try {
            // Verificar cache se habilitado
            if ($this->getConfig('cache', false)) {
                $cacheKey = $this->getCacheKey($value, $context);
                if (isset($this->cache[$cacheKey])) {
                    return $this->cache[$cacheKey];
                }
            }

            // Aplicar cast específico
            $result = $this->applyCast($value, $context);

            // Armazenar no cache se habilitado
            if ($this->getConfig('cache', false)) {
                $this->cache[$cacheKey] = $result;
            }

            return $result;

        } catch (\Exception $e) {
            // Log do erro
            Log::warning("Erro ao aplicar cast {$this->getType()}: {$e->getMessage()}", [
                'value' => $value,
                'context' => $context,
                'cast' => static::class,
            ]);

            // Retornar valor original em caso de erro
            return $value;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function canCast(mixed $value, ?string $type = null): bool
    {
        // Usar callback personalizado se definido
        if ($this->canCastUsing) {
            $result = $this->evaluate($this->canCastUsing, [
                'value' => $value,
                'type' => $type,
                'cast' => $this,
            ]);
            
            return (bool) $result;
        }

        return $this->checkCanCast($value, $type);
    }

    /**
     * {@inheritdoc}
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * {@inheritdoc}
     */
    public function isAutomatic(): bool
    {
        return $this->automatic;
    }

    /**
     * Gera chave de cache para o valor
     */
    protected function getCacheKey(mixed $value, array $context): string
    {
        return md5(serialize([$value, $context, $this->config]));
    }

    /**
     * Configurações padrão do cast
     */
    protected function getDefaultConfig(): array
    {
        return [
            'cache' => false,
            'strict' => false,
        ];
    }

    /**
     * Aplica o cast específico (deve ser implementado pelas classes filhas)
     */
    abstract protected function applyCast(mixed $value, array $context): mixed;

    /**
     * Verifica se pode aplicar o cast (deve ser implementado pelas classes filhas)
     */
    abstract protected function checkCanCast(mixed $value, ?string $type): bool;
} 
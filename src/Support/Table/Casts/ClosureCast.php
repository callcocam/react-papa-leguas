<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Casts;

use Closure;

class ClosureCast extends Cast
{
    /**
     * Prioridade baixa para permitir outros casts primeiro
     */
    protected int $priority = 30;

    /**
     * Closure principal para transformação
     */
    protected ?Closure $transformClosure = null;

    /**
     * Closure para verificação de compatibilidade
     */
    protected ?Closure $canCastClosure = null;

    /**
     * {@inheritdoc}
     */
    public function getType(): string
    {
        return 'closure';
    }

    /**
     * Configurações padrão do cast
     */
    protected function getDefaultConfig(): array
    {
        return array_merge(parent::getDefaultConfig(), [
            'pass_context' => true,
            'pass_cast' => false,
            'pass_column' => true,
            'pass_row' => true,
            'allow_null' => true,
            'fallback_value' => null,
            'strict_mode' => false,
        ]);
    }

    /**
     * Cria cast com closure de transformação
     */
    public static function transform(Closure $closure, array $config = []): static
    {
        $cast = static::make($config);
        $cast->transformClosure = $closure;
        return $cast;
    }



    /**
     * Define closure de transformação
     */
    public function setTransform(Closure $closure): static
    {
        $this->transformClosure = $closure;
        return $this;
    }

    /**
     * Define closure de verificação de compatibilidade
     */
    public function setCanCast(Closure $closure): static
    {
        $this->canCastClosure = $closure;
        return $this;
    }

    /**
     * Define se deve passar o contexto completo
     */
    public function passContext(bool $pass = true): static
    {
        return $this->config('pass_context', $pass);
    }

    /**
     * Define se deve passar a instância do cast
     */
    public function passCast(bool $pass = true): static
    {
        return $this->config('pass_cast', $pass);
    }

    /**
     * Define se deve passar o nome da coluna
     */
    public function passColumn(bool $pass = true): static
    {
        return $this->config('pass_column', $pass);
    }

    /**
     * Define se deve passar os dados da linha
     */
    public function passRow(bool $pass = true): static
    {
        return $this->config('pass_row', $pass);
    }

    /**
     * Define valor de fallback em caso de erro
     */
    public function fallback(mixed $value): static
    {
        return $this->config('fallback_value', $value);
    }

    /**
     * Define modo estrito (lança exceções em vez de usar fallback)
     */
    public function strict(bool $strict = true): static
    {
        return $this->config('strict_mode', $strict);
    }

    /**
     * {@inheritdoc}
     */
    protected function applyCast(mixed $value, array $context): mixed
    {
        if (!$this->transformClosure) {
            return $value;
        }

        try {
            // Preparar argumentos baseado nas configurações
            $arguments = $this->prepareArguments($value, $context);
            
            // Executar closure usando EvaluatesClosures
            return $this->evaluate($this->transformClosure, $arguments);
            
        } catch (\Exception $e) {
            // Modo estrito: relançar exceção
            if ($this->getConfig('strict_mode')) {
                throw $e;
            }
            
            // Modo normal: retornar fallback
            $fallback = $this->getConfig('fallback_value');
            return $fallback !== null ? $fallback : $value;
        }
    }

    /**
     * Prepara argumentos para o closure baseado nas configurações
     */
    protected function prepareArguments(mixed $value, array $context): array
    {
        $arguments = ['value' => $value];
        
        // Adicionar contexto se configurado
        if ($this->getConfig('pass_context')) {
            $arguments['context'] = $context;
        }
        
        // Adicionar cast se configurado
        if ($this->getConfig('pass_cast')) {
            $arguments['cast'] = $this;
        }
        
        // Adicionar coluna se disponível e configurado
        if ($this->getConfig('pass_column') && isset($context['column'])) {
            $arguments['column'] = $context['column'];
        }
        
        // Adicionar dados da linha se disponível e configurado
        if ($this->getConfig('pass_row') && isset($context['row'])) {
            $arguments['row'] = $context['row'];
        }
        
        // Adicionar índice da linha se disponível
        if (isset($context['index'])) {
            $arguments['index'] = $context['index'];
        }
        
        // Adicionar dados da tabela se disponível
        if (isset($context['table'])) {
            $arguments['table'] = $context['table'];
        }
        
        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkCanCast(mixed $value, ?string $type = null): bool
    {
        // Verificar tipo explícito
        if ($type && $type !== 'closure') {
            return false;
        }

        // Se há closure de verificação customizada, usar ela
        if ($this->canCastClosure) {
            try {
                $result = $this->evaluate($this->canCastClosure, [
                    'value' => $value,
                    'type' => $type,
                    'cast' => $this,
                ]);
                
                return (bool) $result;
            } catch (\Exception $e) {
                return false;
            }
        }

        // Se não há closure de transformação, não pode processar
        if (!$this->transformClosure) {
            return false;
        }

        // Se permite null e valor é null, pode processar
        if ($value === null && $this->getConfig('allow_null')) {
            return true;
        }

        // Por padrão, closure cast pode processar qualquer valor
        return true;
    }

    /**
     * Cria cast condicional baseado em condição
     */
    public static function when(Closure $condition, Closure $transform, mixed $fallback = null): static
    {
        return static::make()
            ->setCanCast($condition)
            ->setTransform($transform)
            ->fallback($fallback);
    }

    /**
     * Cria cast para formatação simples
     */
    public static function format(Closure $formatter): static
    {
        return static::transform($formatter)
            ->passContext(false)
            ->passCast(false);
    }

    /**
     * Cria cast para transformação com contexto completo
     */
    public static function contextual(Closure $transformer): static
    {
        return static::transform($transformer)
            ->passContext(true)
            ->passRow(true)
            ->passColumn(true);
    }

    /**
     * Cria cast para pipeline de transformações
     */
    public static function pipeline(array $transformers): static
    {
        return static::transform(function ($value, $context) use ($transformers) {
            $result = $value;
            
            foreach ($transformers as $transformer) {
                if ($transformer instanceof Closure) {
                    $result = $transformer($result, $context);
                } elseif (is_callable($transformer)) {
                    $result = call_user_func($transformer, $result, $context);
                }
            }
            
            return $result;
        });
    }

    /**
     * Cria cast para mapeamento de valores
     */
    public static function map(array $mapping, mixed $default = null): static
    {
        return static::transform(function ($value) use ($mapping, $default) {
            return $mapping[$value] ?? $default ?? $value;
        });
    }

    /**
     * Cria cast para filtro de valores
     */
    public static function filter(Closure $predicate, mixed $replacement = null): static
    {
        return static::transform(function ($value) use ($predicate, $replacement) {
            return $predicate($value) ? $value : $replacement;
        });
    }

    /**
     * Cria cast para aplicar múltiplas transformações condicionalmente
     */
    public static function switch(array $cases, mixed $default = null): static
    {
        return static::transform(function ($value, $context) use ($cases, $default) {
            foreach ($cases as $condition => $transformer) {
                $matches = false;
                
                if ($condition instanceof Closure) {
                    $matches = $condition($value, $context);
                } elseif (is_callable($condition)) {
                    $matches = call_user_func($condition, $value, $context);
                } else {
                    $matches = $value === $condition;
                }
                
                if ($matches) {
                    if ($transformer instanceof Closure) {
                        return $transformer($value, $context);
                    } elseif (is_callable($transformer)) {
                        return call_user_func($transformer, $value, $context);
                    } else {
                        return $transformer;
                    }
                }
            }
            
            return $default ?? $value;
        });
    }
} 
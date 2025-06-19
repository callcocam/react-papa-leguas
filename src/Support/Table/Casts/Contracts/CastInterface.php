<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Casts\Contracts;

interface CastInterface
{
    /**
     * Transforma o valor usando o cast
     *
     * @param mixed $value Valor original
     * @param array $context Contexto da linha/tabela
     * @return mixed Valor transformado
     */
    public function cast(mixed $value, array $context = []): mixed;

    /**
     * Verifica se o cast pode processar o valor
     *
     * @param mixed $value Valor a ser verificado
     * @param string|null $type Tipo esperado (opcional)
     * @return bool
     */
    public function canCast(mixed $value, ?string $type = null): bool;

    /**
     * Retorna o tipo de cast
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Retorna a prioridade do cast (maior = mais prioritário)
     *
     * @return int
     */
    public function getPriority(): int;

    /**
     * Verifica se o cast deve ser aplicado automaticamente
     *
     * @return bool
     */
    public function isAutomatic(): bool;
} 
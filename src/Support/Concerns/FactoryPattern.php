<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

/**
 * Trait para implementar o padrão Factory
 */
trait FactoryPattern
{
    /**
     * Cria uma nova instância da classe
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
    }
} 
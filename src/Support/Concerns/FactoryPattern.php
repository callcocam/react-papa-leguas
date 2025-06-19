<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Concerns;

trait FactoryPattern
{
    /**
     * Criar uma nova instância da classe
     */
    public static function make(...$args): static
    {
        return new static(...$args);
    }
} 
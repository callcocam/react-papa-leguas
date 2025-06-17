<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Columns;

class NumberColumn extends Column
{
    protected int $decimalPlaces = 0;
    protected string $decimalSeparator = ',';
    protected string $thousandsSeparator = '.';
    protected ?string $prefix = null;
    protected ?string $suffix = null;
    protected bool $signed = false;

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'number';
        $this->align = 'right';
    }

    public function decimalPlaces(int $places): static
    {
        $this->decimalPlaces = $places;
        return $this;
    }

    public function decimalSeparator(string $separator): static
    {
        $this->decimalSeparator = $separator;
        return $this;
    }

    public function thousandsSeparator(string $separator): static
    {
        $this->thousandsSeparator = $separator;
        return $this;
    }

    public function prefix(string $prefix): static
    {
        $this->prefix = $prefix;
        return $this;
    }

    public function suffix(string $suffix): static
    {
        $this->suffix = $suffix;
        return $this;
    }

    public function signed(bool $signed = true): static
    {
        $this->signed = $signed;
        return $this;
    }

    // Shortcuts for common formats
    public function currency(string $symbol = 'R$'): static
    {
        return $this->prefix($symbol . ' ')
            ->decimalPlaces(2);
    }

    public function percentage(): static
    {
        return $this->suffix('%')
            ->decimalPlaces(2);
    }

    public function getDecimalPlaces(): int
    {
        return $this->decimalPlaces;
    }

    public function getDecimalSeparator(): string
    {
        return $this->decimalSeparator;
    }

    public function getThousandsSeparator(): string
    {
        return $this->thousandsSeparator;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function isSigned(): bool
    {
        return $this->signed;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'decimalPlaces' => $this->getDecimalPlaces(),
            'decimalSeparator' => $this->getDecimalSeparator(),
            'thousandsSeparator' => $this->getThousandsSeparator(),
            'prefix' => $this->getPrefix(),
            'suffix' => $this->getSuffix(),
            'signed' => $this->isSigned(),
        ]);
    }
}

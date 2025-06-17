<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Filters;

use Closure;

class SelectFilter extends Filter
{
    protected bool $clearable = true;
    protected ?Closure $optionsCallback = null;

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'select';
    }

    public function clearable(bool $clearable = true): static
    {
        $this->clearable = $clearable;
        return $this;
    }

    public function optionsFrom(Closure $callback): static
    {
        $this->optionsCallback = $callback;
        return $this;
    }

    public function isClearable(): bool
    {
        return $this->clearable;
    }

    public function getOptions(): array
    {
        if ($this->optionsCallback) {
            $result = $this->evaluate($this->optionsCallback);
            return is_array($result) ? $result : [];
        }

        return parent::getOptions();
    }

    // Common option builders
    public function fromEnum(string $enumClass): static
    {
        if (enum_exists($enumClass)) {
            $options = [];
            foreach ($enumClass::cases() as $case) {
                $options[] = [
                    'value' => $case->value,
                    'label' => $case->name,
                ];
            }
            $this->options($options);
        }

        return $this;
    }

    public function fromModel(string $modelClass, string $valueField = 'id', string $labelField = 'name'): static
    {
        return $this->optionsFrom(function () use ($modelClass, $valueField, $labelField) {
            return $modelClass::all()->map(function ($item) use ($valueField, $labelField) {
                return [
                    'value' => $item->{$valueField},
                    'label' => $item->{$labelField},
                ];
            })->toArray();
        });
    }

    public function booleanOptions(string $trueLabel = 'Sim', string $falseLabel = 'Não'): static
    {
        return $this->options([
            ['value' => true, 'label' => $trueLabel],
            ['value' => false, 'label' => $falseLabel],
        ]);
    }

    public function statusOptions(): static
    {
        return $this->options([
            ['value' => 'active', 'label' => 'Ativo'],
            ['value' => 'inactive', 'label' => 'Inativo'],
            ['value' => 'pending', 'label' => 'Pendente'],
            ['value' => 'suspended', 'label' => 'Suspenso'],
        ]);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'clearable' => $this->isClearable(),
        ]);
    }
}

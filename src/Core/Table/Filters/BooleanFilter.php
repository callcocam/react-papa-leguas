<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Filters;

class BooleanFilter extends Filter
{
    protected string $trueLabel = 'Sim';
    protected string $falseLabel = 'Não';
    protected string $display = 'select'; // select, radio, switch

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'boolean';
        $this->options([
            ['value' => true, 'label' => $this->trueLabel],
            ['value' => false, 'label' => $this->falseLabel],
        ]);
    }

    public function trueLabel(string $label): static
    {
        $this->trueLabel = $label;
        $this->updateOptions();
        return $this;
    }

    public function falseLabel(string $label): static
    {
        $this->falseLabel = $label;
        $this->updateOptions();
        return $this;
    }

    public function display(string $display): static
    {
        $this->display = $display;
        return $this;
    }

    // Display shortcuts
    public function asSelect(): static
    {
        return $this->display('select');
    }

    public function asRadio(): static
    {
        return $this->display('radio');
    }

    public function asSwitch(): static
    {
        return $this->display('switch');
    }

    // Common presets
    public function yesNo(): static
    {
        return $this->trueLabel('Sim')->falseLabel('Não');
    }

    public function activeInactive(): static
    {
        return $this->trueLabel('Ativo')->falseLabel('Inativo');
    }

    public function enabledDisabled(): static
    {
        return $this->trueLabel('Habilitado')->falseLabel('Desabilitado');
    }

    public function onOff(): static
    {
        return $this->trueLabel('Ligado')->falseLabel('Desligado');
    }

    private function updateOptions(): void
    {
        $this->options([
            ['value' => true, 'label' => $this->trueLabel],
            ['value' => false, 'label' => $this->falseLabel],
        ]);
    }

    public function getTrueLabel(): string
    {
        return $this->trueLabel;
    }

    public function getFalseLabel(): string
    {
        return $this->falseLabel;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    protected function defaultApply($query, $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        // Convert string values to boolean
        if (is_string($value)) {
            $value = in_array(strtolower($value), ['true', '1', 'yes', 'sim', 'on']);
        }

        $query->where($this->getKey(), (bool) $value);
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'trueLabel' => $this->getTrueLabel(),
            'falseLabel' => $this->getFalseLabel(),
            'display' => $this->getDisplay(),
        ]);
    }
}

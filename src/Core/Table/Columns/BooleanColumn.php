<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Columns;

use Callcocam\ReactPapaLeguas\Core;

class BooleanColumn extends Column
{
    use Core\Concerns\BelongsToIcon;
    use Core\Concerns\BelongsToColor;

    protected string $trueLabel = 'Sim';
    protected string $falseLabel = 'Não';
    protected ?string $trueIcon = null;
    protected ?string $falseIcon = null;
    protected ?string $trueColor = null;
    protected ?string $falseColor = null;
    protected string $display = 'badge'; // badge, icon, text, switch

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'boolean';
        $this->align = 'center';
    }

    public function trueLabel(string $label): static
    {
        $this->trueLabel = $label;
        return $this;
    }

    public function falseLabel(string $label): static
    {
        $this->falseLabel = $label;
        return $this;
    }

    public function trueIcon(string $icon): static
    {
        $this->trueIcon = $icon;
        return $this;
    }

    public function falseIcon(string $icon): static
    {
        $this->falseIcon = $icon;
        return $this;
    }

    public function trueColor(string $color): static
    {
        $this->trueColor = $color;
        return $this;
    }

    public function falseColor(string $color): static
    {
        $this->falseColor = $color;
        return $this;
    }

    public function display(string $display): static
    {
        $this->display = $display;
        return $this;
    }

    // Display shortcuts
    public function asBadge(): static
    {
        return $this->display('badge');
    }

    public function asIcon(): static
    {
        return $this->display('icon');
    }

    public function asText(): static
    {
        return $this->display('text');
    }

    public function asSwitch(): static
    {
        return $this->display('switch');
    }

    // Common presets
    public function yesNo(): static
    {
        return $this->trueLabel('Sim')
            ->falseLabel('Não')
            ->trueColor('success')
            ->falseColor('secondary');
    }

    public function activeInactive(): static
    {
        return $this->trueLabel('Ativo')
            ->falseLabel('Inativo')
            ->trueColor('success')
            ->falseColor('secondary')
            ->trueIcon('check-circle')
            ->falseIcon('x-circle');
    }

    public function enabledDisabled(): static
    {
        return $this->trueLabel('Habilitado')
            ->falseLabel('Desabilitado')
            ->trueColor('success')
            ->falseColor('secondary');
    }

    public function getTrueLabel(): string
    {
        return $this->trueLabel;
    }

    public function getFalseLabel(): string
    {
        return $this->falseLabel;
    }

    public function getTrueIcon(): ?string
    {
        return $this->trueIcon;
    }

    public function getFalseIcon(): ?string
    {
        return $this->falseIcon;
    }

    public function getTrueColor(): ?string
    {
        return $this->trueColor;
    }

    public function getFalseColor(): ?string
    {
        return $this->falseColor;
    }

    public function getDisplay(): string
    {
        return $this->display;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'trueLabel' => $this->getTrueLabel(),
            'falseLabel' => $this->getFalseLabel(),
            'trueIcon' => $this->getTrueIcon(),
            'falseIcon' => $this->getFalseIcon(),
            'trueColor' => $this->getTrueColor(),
            'falseColor' => $this->getFalseColor(),
            'display' => $this->getDisplay(),
        ]);
    }
}

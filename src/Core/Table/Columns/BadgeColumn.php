<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Core\Table\Columns;

use Callcocam\ReactPapaLeguas\Core;

class BadgeColumn extends Column
{
    use Core\Concerns\BelongsToColor;
    use Core\Concerns\BelongsToIcon;

    protected array $colorMap = [];
    protected array $iconMap = [];
    protected array $labelMap = [];
    protected string $variant = 'default';
    protected string $size = 'sm';

    public function __construct(string $key, ?string $label = null)
    {
        parent::__construct($key, $label);
        $this->type = 'badge';
    }

    public function colorMap(array $colorMap): static
    {
        $this->colorMap = $colorMap;
        return $this;
    }

    public function iconMap(array $iconMap): static
    {
        $this->iconMap = $iconMap;
        return $this;
    }

    public function labelMap(array $labelMap): static
    {
        $this->labelMap = $labelMap;
        return $this;
    }

    public function variant(string $variant): static
    {
        $this->variant = $variant;
        return $this;
    }

    public function size(string $size): static
    {
        $this->size = $size;
        return $this;
    }

    // Shortcuts for common status mappings
    public function statusColors(): static
    {
        return $this->colorMap([
            'active' => 'success',
            'inactive' => 'secondary',
            'pending' => 'warning',
            'suspended' => 'destructive',
            'draft' => 'outline',
            'published' => 'success',
            'archived' => 'secondary',
        ]);
    }

    public function statusIcons(): static
    {
        return $this->iconMap([
            'active' => 'check-circle',
            'inactive' => 'x-circle',
            'pending' => 'clock',
            'suspended' => 'pause-circle',
            'draft' => 'edit',
            'published' => 'eye',
            'archived' => 'archive',
        ]);
    }

    public function statusLabels(): static
    {
        return $this->labelMap([
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'pending' => 'Pendente',
            'suspended' => 'Suspenso',
            'draft' => 'Rascunho',
            'published' => 'Publicado',
            'archived' => 'Arquivado',
        ]);
    }

    public function getColorMap(): array
    {
        return $this->colorMap;
    }

    public function getIconMap(): array
    {
        return $this->iconMap;
    }

    public function getLabelMap(): array
    {
        return $this->labelMap;
    }

    public function getVariant(): string
    {
        return $this->variant;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function getColorForValue($value): string
    {
        return $this->colorMap[$value] ?? $this->getColor() ?? 'default';
    }

    public function getIconForValue($value): ?string
    {
        return $this->iconMap[$value] ?? $this->getIcon();
    }

    public function getLabelForValue($value): string
    {
        return $this->labelMap[$value] ?? $value;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'colorMap' => $this->getColorMap(),
            'iconMap' => $this->getIconMap(),
            'labelMap' => $this->getLabelMap(),
            'variant' => $this->getVariant(),
            'size' => $this->getSize(),
        ]);
    }
}

<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

/**
 * Coluna de badge com cores e ícones dinâmicos
 */
class BadgeColumn extends Column
{
    protected string $type = 'badge';

    /**
     * Definir cores para diferentes valores
     */
    public function colors(array $colors): static
    {
        $this->formatConfig['colors'] = $colors;
        return $this;
    }

    /**
     * Definir ícones para diferentes valores
     */
    public function icons(array $icons): static
    {
        $this->formatConfig['icons'] = $icons;
        return $this;
    }

    /**
     * Definir labels para diferentes valores
     */
    public function labels(array $labels): static
    {
        $this->formatConfig['labels'] = $labels;
        return $this;
    }

    /**
     * Usar cores de status padrão
     */
    public function statusColors(): static
    {
        return $this->colors([
            'active' => 'green',
            'inactive' => 'red',
            'pending' => 'yellow',
            'draft' => 'gray',
            'published' => 'blue',
            'archived' => 'orange',
        ]);
    }

    /**
     * Usar ícones de status padrão
     */
    public function statusIcons(): static
    {
        return $this->icons([
            'active' => 'CheckCircle',
            'inactive' => 'XCircle',
            'pending' => 'Clock',
            'draft' => 'Edit',
            'published' => 'Eye',
            'archived' => 'Archive',
        ]);
    }

    /**
     * Usar labels de status padrão
     */
    public function statusLabels(): static
    {
        return $this->labels([
            'active' => 'Ativo',
            'inactive' => 'Inativo',
            'pending' => 'Pendente',
            'draft' => 'Rascunho',
            'published' => 'Publicado',
            'archived' => 'Arquivado',
        ]);
    }

    /**
     * Badge com animação de pulso
     */
    public function pulse(bool $pulse = true): static
    {
        $this->formatConfig['pulse'] = $pulse;
        return $this;
    }

    /**
     * Badge com borda
     */
    public function outlined(bool $outlined = true): static
    {
        $this->formatConfig['outlined'] = $outlined;
        return $this;
    }

    /**
     * Aplicar formatação padrão
     */
    protected function applyDefaultFormatting($value, $record): mixed
    {
        if (is_null($value)) {
            return null;
        }

        $stringValue = (string) $value;
        
        return [
            'value' => $stringValue,
            'text' => $this->formatConfig['labels'][$stringValue] ?? $stringValue,
            'color' => $this->formatConfig['colors'][$stringValue] ?? 'gray',
            'icon' => $this->formatConfig['icons'][$stringValue] ?? null,
            'pulse' => $this->formatConfig['pulse'] ?? false,
            'outlined' => $this->formatConfig['outlined'] ?? false,
        ];
    }
} 
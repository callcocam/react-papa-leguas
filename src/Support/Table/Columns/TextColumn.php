<?php

/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Support\Table\Columns;

/**
 * Coluna de texto com formatação avançada
 */
class TextColumn extends Column
{
    protected string $type = 'text';

    /**
     * Truncar texto
     */
    public function truncate(int $length = 50, string $suffix = '...'): static
    {
        $this->formatConfig['truncate'] = [
            'length' => $length,
            'suffix' => $suffix,
        ];
        return $this;
    }

    /**
     * Tornar texto copiável
     */
    public function copyable(bool $copyable = true): static
    {
        $this->formatConfig['copyable'] = $copyable;
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

        $formatted = (string) $value;

        // Aplicar truncate se configurado
        if (isset($this->formatConfig['truncate'])) {
            $config = $this->formatConfig['truncate'];
            if (strlen($formatted) > $config['length']) {
                $formatted = substr($formatted, 0, $config['length']) . $config['suffix'];
            }
        }

        return [
            'value' => $formatted,
            'original' => $value,
            'copyable' => $this->formatConfig['copyable'] ?? false,
        ];
    }
} 
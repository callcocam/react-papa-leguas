<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Enums;

enum BaseStatus: string
{
    case Draft = 'draft';
    case Published = 'published';

    /**
     * Get all status values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get status labels.
     */
    public function label(): string
    {
        return match($this) {
            self::Draft => 'Rascunho',
            self::Published => 'Publicado',
        };
    }

    /**
     * Get status color for UI.
     */
    public function color(): string
    {
        return match($this) {
            self::Draft => 'gray',
            self::Published => 'green',
        };
    }

    /**
     * Get badge class for Tailwind CSS.
     */
    public function badgeClass(): string
    {
        return match($this) {
            self::Draft => 'bg-gray-100 text-gray-800 border-gray-200',
            self::Published => 'bg-green-100 text-green-800 border-green-200',
        };
    }

    /**
     * Check if status is published.
     */
    public function isPublished(): bool
    {
        return $this === self::Published;
    }

    /**
     * Check if status is draft.
     */
    public function isDraft(): bool
    {
        return $this === self::Draft;
    }
}

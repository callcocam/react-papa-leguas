<?php

namespace Callcocam\ReactPapaLeguas\Enums;

enum PermissionStatus: string
{
    case Published = 'published';
    case Draft = 'draft';
    case Inactive = 'inactive';
    case Archived = 'archived';

    /**
     * Get all possible values.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Get the label for the status.
     */
    public function label(): string
    {
        return match($this) {
            self::Published => 'Published',
            self::Draft => 'Draft',
            self::Inactive => 'Inactive',
            self::Archived => 'Archived',
        };
    }

    /**
     * Check if the status is published/active.
     */
    public function isActive(): bool
    {
        return $this === self::Published;
    }
}

<?php

namespace Callcocam\ReactPapaLeguas\Enums;

enum UserStatus: string
{
    case Published = 'published';
    case Draft = 'draft';
    case Inactive = 'inactive';
    case Pending = 'pending';
    case Suspended = 'suspended';
    case Blocked = 'blocked';

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
            self::Pending => 'Pending',
            self::Suspended => 'Suspended',
            self::Blocked => 'Blocked',
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

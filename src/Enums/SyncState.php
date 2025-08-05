<?php

namespace Turahe\MailClient\Enums;

enum SyncState: string
{
    case Disabled = 'DISABLED';
    case Stopped = 'STOPPED';
    case Enabled = 'ENABLED';

    /**
     * Get the display name for the sync state.
     */
    public function getDisplayName(): string
    {
        return match($this) {
            self::Disabled => 'Disabled',
            self::Stopped => 'Stopped',
            self::Enabled => 'Enabled',
        };
    }

    /**
     * Check if sync is active (enabled).
     */
    public function isActive(): bool
    {
        return $this === self::Enabled;
    }

    /**
     * Check if sync is inactive (disabled or stopped).
     */
    public function isInactive(): bool
    {
        return match($this) {
            self::Disabled, self::Stopped => true,
            self::Enabled => false,
        };
    }

    /**
     * Get the color class for UI display.
     */
    public function getColorClass(): string
    {
        return match($this) {
            self::Disabled => 'text-red-500',
            self::Stopped => 'text-yellow-500',
            self::Enabled => 'text-green-500',
        };
    }

    /**
     * Get all sync states as an array.
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}

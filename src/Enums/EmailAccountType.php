<?php

namespace Turahe\MailClient\Enums;

enum EmailAccountType: string
{
    case Personal = 'PERSONAL';
    case Shared = 'SHARED';

    /**
     * Get the display name for the account type.
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::Personal => 'Personal',
            self::Shared => 'Shared',
        };
    }

    /**
     * Check if this is a personal account.
     */
    public function isPersonal(): bool
    {
        return $this === self::Personal;
    }

    /**
     * Check if this is a shared account.
     */
    public function isShared(): bool
    {
        return $this === self::Shared;
    }

    /**
     * Get the icon class for UI display.
     */
    public function getIconClass(): string
    {
        return match ($this) {
            self::Personal => 'fas fa-user',
            self::Shared => 'fas fa-users',
        };
    }

    /**
     * Get all account types as an array.
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}

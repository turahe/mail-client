<?php

namespace Turahe\MailClient\Enums;

enum ConnectionType: string
{
    case Gmail = 'GMAIL';
    case Outlook = 'OUTLOOK';
    case Imap = 'IMAP';

    /**
     * Get the display name for the connection type.
     */
    public function getDisplayName(): string
    {
        return match ($this) {
            self::Gmail => 'Gmail',
            self::Outlook => 'Outlook',
            self::Imap => 'IMAP',
        };
    }

    /**
     * Check if this connection type supports OAuth.
     */
    public function supportsOAuth(): bool
    {
        return match ($this) {
            self::Gmail, self::Outlook => true,
            self::Imap => false,
        };
    }

    /**
     * Get the default ports for this connection type.
     */
    public function getDefaultPorts(): array
    {
        return match ($this) {
            self::Gmail => ['imap' => 993, 'smtp' => 587],
            self::Outlook => ['imap' => 993, 'smtp' => 587],
            self::Imap => ['imap' => 993, 'smtp' => 587],
        };
    }

    /**
     * Get all connection types as an array.
     */
    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }
}

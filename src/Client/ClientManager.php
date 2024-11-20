<?php

namespace Turahe\MailClient\Client;

use Turahe\MailClient\Client\Contracts\Connectable;
use Turahe\MailClient\Client\Exceptions\ConnectionErrorException;
use Turahe\MailClient\Client\Gmail\ImapClient as GmailImapClient;
use Turahe\MailClient\Client\Gmail\SmtpClient as GmailSmtpClient;
use Turahe\MailClient\Client\Imap\Config;
use Turahe\MailClient\Client\Imap\ImapClient;
use Turahe\MailClient\Client\Imap\SmtpClient;
use Turahe\MailClient\Client\Imap\SmtpConfig;
use Turahe\MailClient\Client\Outlook\ImapClient as OutlookImapClient;
use Turahe\MailClient\Client\Outlook\SmtpClient as OutlookSmtpClient;
use Turahe\MailClient\Enums\ConnectionType;

class ClientManager
{
    /**
     * Available encryption types
     */
    const ENCRYPTION_TYPES = [
        'ssl', 'tls', 'starttls',
    ];

    /**
     * Create mail client instance
     */
    public static function createClient(
        ConnectionType $connectionType,
        AccessTokenProvider|Config $imapConfig,
        AccessTokenProvider|SmtpConfig|null $smtpConfig = null,
    ): Client {
        $part = $connectionType === ConnectionType::Imap ? '' : $connectionType->value;

        return new Client(
            self::{'create'.$part.'ImapClient'}($imapConfig),
            // ?? $imapConfig is if is AccessTokenProvider
            self::{'create'.$part.'SmtpClient'}($smtpConfig ?? $imapConfig)
        );
    }

    /**
     * Create IMAP client instance
     */
    public static function createImapClient(Config $config): ImapClient
    {
        return new ImapClient($config);
    }

    /**
     * Create SMTP client instance
     */
    public static function createSmtpClient(SmtpConfig $config): SmtpClient
    {
        return new SmtpClient($config);
    }

    /**
     * Create Outlook IMAP client instance
     */
    public static function createOutlookImapClient(AccessTokenProvider $token): OutlookImapClient
    {
        return new OutlookImapClient($token);
    }

    /**
     * Create Outlook SMTP client instance
     */
    public static function createOutlookSmtpClient(AccessTokenProvider $token): OutlookSmtpClient
    {
        return new OutlookSmtpClient($token);
    }

    /**
     * Create Gmail IMAP client instance
     */
    public static function createGmailImapClient(AccessTokenProvider $token): GmailImapClient
    {
        return new GmailImapClient($token);
    }

    /**
     * Create Gmail SMTP client instance
     */
    public static function createGmailSmtpClient(AccessTokenProvider $token): GmailSmtpClient
    {
        return new GmailSmtpClient($token);
    }

    /**
     * Test server connection
     */
    public static function testConnection(Connectable $client): void
    {
        try {
            $client->testConnection();
        } catch (\Exception $e) {
            throw new ConnectionErrorException($e->getMessage(), $e->getCode(), $e);
        }
    }
}

<?php

namespace Turahe\MailClient\DTOs;

use Turahe\MailClient\Enums\ConnectionType;
use Turahe\MailClient\Enums\EmailAccountType;

readonly class EmailAccountConfig
{
    public function __construct(
        public string $email,
        public string $password,
        public ConnectionType $connectionType,
        public ?string $aliasEmail = null,
        public ?string $username = null,
        public ?string $imapServer = null,
        public int $imapPort = 993,
        public string $imapEncryption = 'ssl',
        public ?string $smtpServer = null,
        public int $smtpPort = 587,
        public string $smtpEncryption = 'tls',
        public bool $validateCert = true,
        public bool $requiresAuth = true,
        public bool $createContact = true,
        public ?EmailAccountType $accountType = null,
        public ?string $modelId = null,
        public ?string $modelType = null,
    ) {
        $this->validate();
    }

    /**
     * Validate the configuration.
     */
    private function validate(): void
    {
        // Validate email
        if (! filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }

        // Validate ports
        if ($this->imapPort < 1 || $this->imapPort > 65535) {
            throw new \InvalidArgumentException('Invalid IMAP port number');
        }

        if ($this->smtpPort < 1 || $this->smtpPort > 65535) {
            throw new \InvalidArgumentException('Invalid SMTP port number');
        }
    }

    /**
     * Get default IMAP server based on connection type.
     */
    public function getDefaultImapServer(): string
    {
        return match ($this->connectionType) {
            ConnectionType::Gmail => 'imap.gmail.com',
            ConnectionType::Outlook => 'outlook.office365.com',
            ConnectionType::Imap => 'localhost',
        };
    }

    /**
     * Get default SMTP server based on connection type.
     */
    public function getDefaultSmtpServer(): string
    {
        return match ($this->connectionType) {
            ConnectionType::Gmail => 'smtp.gmail.com',
            ConnectionType::Outlook => 'smtp.office365.com',
            ConnectionType::Imap => 'localhost',
        };
    }

    /**
     * Get the effective IMAP server (default if not provided).
     */
    public function getEffectiveImapServer(): string
    {
        return $this->imapServer ?? $this->getDefaultImapServer();
    }

    /**
     * Get the effective SMTP server (default if not provided).
     */
    public function getEffectiveSmtpServer(): string
    {
        return $this->smtpServer ?? $this->getDefaultSmtpServer();
    }

    /**
     * Get the effective username (email if not provided).
     */
    public function getEffectiveUsername(): string
    {
        return $this->username ?? $this->email;
    }

    /**
     * Convert to array for database insertion.
     */
    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'alias_email' => $this->aliasEmail,
            'password' => $this->password,
            'connection_type' => $this->connectionType,
            'username' => $this->getEffectiveUsername(),
            'imap_server' => $this->getEffectiveImapServer(),
            'imap_port' => $this->imapPort,
            'imap_encryption' => $this->imapEncryption,
            'smtp_server' => $this->getEffectiveSmtpServer(),
            'smtp_port' => $this->smtpPort,
            'smtp_encryption' => $this->smtpEncryption,
            'validate_cert' => $this->validateCert,
            'requires_auth' => $this->requiresAuth,
            'create_contact' => $this->createContact,
            'model_id' => $this->modelId,
            'model_type' => $this->modelType,
        ];
    }

    /**
     * Create from array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            connectionType: ConnectionType::from($data['connection_type']),
            aliasEmail: $data['alias_email'] ?? null,
            username: $data['username'] ?? null,
            imapServer: $data['imap_server'] ?? null,
            imapPort: $data['imap_port'] ?? 993,
            imapEncryption: $data['imap_encryption'] ?? 'ssl',
            smtpServer: $data['smtp_server'] ?? null,
            smtpPort: $data['smtp_port'] ?? 587,
            smtpEncryption: $data['smtp_encryption'] ?? 'tls',
            validateCert: $data['validate_cert'] ?? true,
            requiresAuth: $data['requires_auth'] ?? true,
            createContact: $data['create_contact'] ?? true,
            accountType: isset($data['account_type']) ? EmailAccountType::from($data['account_type']) : null,
            modelId: $data['model_id'] ?? null,
            modelType: $data['model_type'] ?? null,
        );
    }

    /**
     * Get configuration summary.
     */
    public function getSummary(): array
    {
        return [
            'email' => $this->email,
            'connection_type' => $this->connectionType->getDisplayName(),
            'imap_server' => $this->getEffectiveImapServer(),
            'imap_port' => $this->imapPort,
            'smtp_server' => $this->getEffectiveSmtpServer(),
            'smtp_port' => $this->smtpPort,
            'supports_oauth' => $this->connectionType->supportsOAuth(),
        ];
    }

    /**
     * Create a new instance with updated values.
     */
    public function with(array $changes): self
    {
        return new self(
            email: $changes['email'] ?? $this->email,
            password: $changes['password'] ?? $this->password,
            connectionType: $changes['connection_type'] ?? $this->connectionType,
            aliasEmail: $changes['alias_email'] ?? $this->aliasEmail,
            username: $changes['username'] ?? $this->username,
            imapServer: $changes['imap_server'] ?? $this->imapServer,
            imapPort: $changes['imap_port'] ?? $this->imapPort,
            imapEncryption: $changes['imap_encryption'] ?? $this->imapEncryption,
            smtpServer: $changes['smtp_server'] ?? $this->smtpServer,
            smtpPort: $changes['smtp_port'] ?? $this->smtpPort,
            smtpEncryption: $changes['smtp_encryption'] ?? $this->smtpEncryption,
            validateCert: $changes['validate_cert'] ?? $this->validateCert,
            requiresAuth: $changes['requires_auth'] ?? $this->requiresAuth,
            createContact: $changes['create_contact'] ?? $this->createContact,
            accountType: $changes['account_type'] ?? $this->accountType,
            modelId: $changes['model_id'] ?? $this->modelId,
            modelType: $changes['model_type'] ?? $this->modelType,
        );
    }
}

<?php

namespace Turahe\MailClient\Concerns;

use Turahe\MailClient\Enums\ConnectionType;
use Turahe\MailClient\Enums\SyncState;

trait HasEmailAccountValidation
{
    /**
     * Validate email account configuration.
     */
    public function validateEmailAccountConfig(array $config): array
    {
        $errors = [];

        // Required fields validation
        $requiredFields = ['email', 'password', 'connection_type'];
        foreach ($requiredFields as $field) {
            if (empty($config[$field])) {
                $errors[] = "The {$field} field is required.";
            }
        }

        // Email validation
        if (!empty($config['email'])) {
            if (!filter_var($config['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'The email field must be a valid email address.';
            }
        }

        // Connection type validation
        if (!empty($config['connection_type'])) {
            $connectionType = ConnectionType::tryFrom($config['connection_type']);
            if (!$connectionType) {
                $errors[] = 'The connection type must be a valid value.';
            }
        }

        // Port validation
        if (!empty($config['imap_port'])) {
            if (!is_numeric($config['imap_port']) || $config['imap_port'] < 1 || $config['imap_port'] > 65535) {
                $errors[] = 'The IMAP port must be a valid port number (1-65535).';
            }
        }

        if (!empty($config['smtp_port'])) {
            if (!is_numeric($config['smtp_port']) || $config['smtp_port'] < 1 || $config['smtp_port'] > 65535) {
                $errors[] = 'The SMTP port must be a valid port number (1-65535).';
            }
        }

        // Encryption validation
        $validEncryptions = ['ssl', 'tls', 'notls'];
        if (!empty($config['imap_encryption']) && !in_array($config['imap_encryption'], $validEncryptions)) {
            $errors[] = 'The IMAP encryption must be one of: ' . implode(', ', $validEncryptions);
        }

        if (!empty($config['smtp_encryption']) && !in_array($config['smtp_encryption'], $validEncryptions)) {
            $errors[] = 'The SMTP encryption must be one of: ' . implode(', ', $validEncryptions);
        }

        return $errors;
    }

    /**
     * Validate sync state transition.
     */
    public function validateSyncStateTransition(SyncState $currentState, SyncState $newState): array
    {
        $errors = [];

        // Define valid transitions
        $validTransitions = [
            SyncState::Disabled => [SyncState::Enabled],
            SyncState::Stopped => [SyncState::Enabled, SyncState::Disabled],
            SyncState::Enabled => [SyncState::Disabled, SyncState::Stopped],
        ];

        if (!isset($validTransitions[$currentState]) || !in_array($newState, $validTransitions[$currentState])) {
            $errors[] = "Invalid transition from {$currentState->getDisplayName()} to {$newState->getDisplayName()}.";
        }

        return $errors;
    }

    /**
     * Validate connection type configuration.
     */
    public function validateConnectionTypeConfig(ConnectionType $connectionType, array $config): array
    {
        $errors = [];

        // Connection-specific validation
        match($connectionType) {
            ConnectionType::Gmail => $errors = $this->validateGmailConfig($config),
            ConnectionType::Outlook => $errors = $this->validateOutlookConfig($config),
            ConnectionType::Imap => $errors = $this->validateImapConfig($config),
        };

        return $errors;
    }

    /**
     * Validate Gmail-specific configuration.
     */
    private function validateGmailConfig(array $config): array
    {
        $errors = [];

        // Gmail requires OAuth or app password
        if (empty($config['password']) && empty($config['access_token_id'])) {
            $errors[] = 'Gmail requires either a password or OAuth access token.';
        }

        // Gmail uses specific servers
        if (!empty($config['imap_server']) && $config['imap_server'] !== 'imap.gmail.com') {
            $errors[] = 'Gmail IMAP server should be imap.gmail.com.';
        }

        if (!empty($config['smtp_server']) && $config['smtp_server'] !== 'smtp.gmail.com') {
            $errors[] = 'Gmail SMTP server should be smtp.gmail.com.';
        }

        return $errors;
    }

    /**
     * Validate Outlook-specific configuration.
     */
    private function validateOutlookConfig(array $config): array
    {
        $errors = [];

        // Outlook requires OAuth or password
        if (empty($config['password']) && empty($config['access_token_id'])) {
            $errors[] = 'Outlook requires either a password or OAuth access token.';
        }

        // Outlook uses specific servers
        if (!empty($config['imap_server']) && $config['imap_server'] !== 'outlook.office365.com') {
            $errors[] = 'Outlook IMAP server should be outlook.office365.com.';
        }

        if (!empty($config['smtp_server']) && $config['smtp_server'] !== 'smtp.office365.com') {
            $errors[] = 'Outlook SMTP server should be smtp.office365.com.';
        }

        return $errors;
    }

    /**
     * Validate IMAP-specific configuration.
     */
    private function validateImapConfig(array $config): array
    {
        $errors = [];

        // IMAP requires server configuration
        if (empty($config['imap_server'])) {
            $errors[] = 'IMAP server is required for IMAP connection type.';
        }

        if (empty($config['smtp_server'])) {
            $errors[] = 'SMTP server is required for IMAP connection type.';
        }

        return $errors;
    }

    /**
     * Get validation rules for email account creation.
     */
    public function getEmailAccountValidationRules(): array
    {
        return [
            'email' => 'required|email|unique:email_accounts,email',
            'password' => 'required|string|min:1',
            'connection_type' => 'required|string|in:' . implode(',', ConnectionType::toArray()),
            'alias_email' => 'nullable|email',
            'username' => 'nullable|string',
            'imap_server' => 'nullable|string',
            'imap_port' => 'nullable|integer|min:1|max:65535',
            'imap_encryption' => 'nullable|string|in:ssl,tls,notls',
            'smtp_server' => 'nullable|string',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_encryption' => 'nullable|string|in:ssl,tls,notls',
            'validate_cert' => 'nullable|boolean',
            'requires_auth' => 'nullable|boolean',
            'create_contact' => 'nullable|boolean',
            'model_id' => 'nullable|string',
            'model_type' => 'nullable|string',
        ];
    }

    /**
     * Get validation messages for email account creation.
     */
    public function getEmailAccountValidationMessages(): array
    {
        return [
            'email.required' => 'Email address is required.',
            'email.email' => 'Please provide a valid email address.',
            'email.unique' => 'This email address is already registered.',
            'password.required' => 'Password is required.',
            'connection_type.required' => 'Connection type is required.',
            'connection_type.in' => 'Please select a valid connection type.',
            'imap_port.integer' => 'IMAP port must be a number.',
            'imap_port.min' => 'IMAP port must be at least 1.',
            'imap_port.max' => 'IMAP port cannot exceed 65535.',
            'smtp_port.integer' => 'SMTP port must be a number.',
            'smtp_port.min' => 'SMTP port must be at least 1.',
            'smtp_port.max' => 'SMTP port cannot exceed 65535.',
        ];
    }
} 
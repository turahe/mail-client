<?php

namespace Turahe\MailClient\Services;

use Illuminate\Support\Collection;
use Turahe\MailClient\Enums\ConnectionType;
use Turahe\MailClient\Enums\EmailAccountType;
use Turahe\MailClient\Enums\SyncState;
use Turahe\MailClient\Models\EmailAccount;
use Turahe\MailClient\Models\EmailAccountMessage;

class EmailAccountManager
{
    /**
     * Create a new email account with validation.
     */
    public function createAccount(
        string $email,
        string $password,
        ConnectionType $connectionType,
        array $config = [],
        ?EmailAccountType $type = null
    ): EmailAccount {
        $account = new EmailAccount([
            'email' => $email,
            'password' => $password,
            'connection_type' => $connectionType,
            'sync_state' => SyncState::Enabled,
            ...$config,
        ]);

        // Set default ports if not provided
        if (!isset($config['imap_port'])) {
            $account->imap_port = $connectionType->getDefaultPorts()['imap'];
        }
        if (!isset($config['smtp_port'])) {
            $account->smtp_port = $connectionType->getDefaultPorts()['smtp'];
        }

        $account->save();

        return $account;
    }

    /**
     * Get accounts by type with improved filtering.
     */
    public function getAccountsByType(EmailAccountType $type, ?int $userId = null): Collection
    {
        $query = EmailAccount::query();

        return match($type) {
            EmailAccountType::Personal => $query->where('model_id', $userId)->get(),
            EmailAccountType::Shared => $query->whereNull('model_id')->get(),
        };
    }

    /**
     * Get accounts by connection type.
     */
    public function getAccountsByConnectionType(ConnectionType $connectionType): Collection
    {
        return EmailAccount::where('connection_type', $connectionType)->get();
    }

    /**
     * Get accounts by sync state.
     */
    public function getAccountsBySyncState(SyncState $syncState): Collection
    {
        return EmailAccount::where('sync_state', $syncState)->get();
    }

    /**
     * Get active accounts (enabled sync).
     */
    public function getActiveAccounts(): Collection
    {
        return EmailAccount::where('sync_state', SyncState::Enabled)->get();
    }

    /**
     * Get inactive accounts (disabled or stopped sync).
     */
    public function getInactiveAccounts(): Collection
    {
        return EmailAccount::whereIn('sync_state', [
            SyncState::Disabled,
            SyncState::Stopped
        ])->get();
    }

    /**
     * Bulk update sync state for multiple accounts.
     */
    public function bulkUpdateSyncState(array $accountIds, SyncState $syncState, ?string $comment = null): int
    {
        return EmailAccount::whereIn('id', $accountIds)
            ->update([
                'sync_state' => $syncState,
                'sync_state_comment' => $comment,
            ]);
    }

    /**
     * Get account statistics.
     */
    public function getAccountStatistics(): array
    {
        $total = EmailAccount::count();
        $active = EmailAccount::where('sync_state', SyncState::Enabled)->count();
        $inactive = $total - $active;

        $byConnectionType = EmailAccount::selectRaw('connection_type, COUNT(*) as count')
            ->groupBy('connection_type')
            ->pluck('count', 'connection_type')
            ->toArray();

        $byAccountType = EmailAccount::selectRaw('
            CASE 
                WHEN model_id IS NULL THEN "SHARED" 
                ELSE "PERSONAL" 
            END as account_type, 
            COUNT(*) as count
        ')
            ->groupBy('account_type')
            ->pluck('count', 'account_type')
            ->toArray();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'by_connection_type' => $byConnectionType,
            'by_account_type' => $byAccountType,
        ];
    }

    /**
     * Get accounts with message counts.
     */
    public function getAccountsWithMessageCounts(): Collection
    {
        return EmailAccount::withCount([
            'messages',
            'messages as unread_count' => fn($query) => $query->where('is_read', false),
            'messages as read_count' => fn($query) => $query->where('is_read', true),
        ])->get();
    }

    /**
     * Get accounts with recent activity.
     */
    public function getAccountsWithRecentActivity(int $days = 7): Collection
    {
        $date = now()->subDays($days);

        return EmailAccount::whereHas('messages', function($query) use ($date) {
            $query->where('created_at', '>=', $date);
        })->withCount([
            'messages' => fn($query) => $query->where('created_at', '>=', $date)
        ])->get();
    }

    /**
     * Validate account configuration.
     */
    public function validateAccountConfig(array $config): array
    {
        $errors = [];

        // Required fields
        $required = ['email', 'password', 'connection_type'];
        foreach ($required as $field) {
            if (empty($config[$field])) {
                $errors[] = "The {$field} field is required.";
            }
        }

        // Email validation
        if (!empty($config['email']) && !filter_var($config['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'The email field must be a valid email address.';
        }

        // Connection type validation
        if (!empty($config['connection_type'])) {
            $connectionType = ConnectionType::tryFrom($config['connection_type']);
            if (!$connectionType) {
                $errors[] = 'The connection type must be a valid value.';
            }
        }

        // Port validation
        if (!empty($config['imap_port']) && (!is_numeric($config['imap_port']) || $config['imap_port'] < 1 || $config['imap_port'] > 65535)) {
            $errors[] = 'The IMAP port must be a valid port number (1-65535).';
        }

        if (!empty($config['smtp_port']) && (!is_numeric($config['smtp_port']) || $config['smtp_port'] < 1 || $config['smtp_port'] > 65535)) {
            $errors[] = 'The SMTP port must be a valid port number (1-65535).';
        }

        return $errors;
    }

    /**
     * Get accounts that need attention (errors, warnings, etc.).
     */
    public function getAccountsNeedingAttention(): Collection
    {
        return EmailAccount::where(function($query) {
            $query->where('sync_state', SyncState::Stopped)
                  ->orWhere('sync_state', SyncState::Disabled)
                  ->orWhereNull('last_sync_at')
                  ->orWhere('last_sync_at', '<', now()->subDays(7));
        })->get();
    }
} 
<?php

namespace Turahe\MailClient\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Application;
use Illuminate\Support\Carbon;
use Turahe\Core\Concerns\HasConfigurablePrimaryKey;
use Turahe\MailClient\Concerns\SendsScheduledEmail;
use Turahe\Media\HasMedia;

class ScheduledEmail extends Model
{
    use HasConfigurablePrimaryKey;
    use HasFactory,
        HasMedia,
        SendsScheduledEmail;

    protected $table = 'scheduled_emails';

    /**
     * Create a new factory instance for the model.
     */
    protected static function newFactory()
    {
        return \Turahe\MailClient\Tests\Factories\ScheduledEmailFactory::new();
    }

    /**
     * The number of max retries to retry failed emails to sent.
     */
    public static int $maxRetries = 3;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<string>|bool
     */
    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
        'to' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'retries' => 'int',
        'retry_after' => 'datetime',
        'email_account_id' => 'string',
        'related_message_id' => 'string',
        'associations' => 'array',
    ];

    /**
     * Get the account belonging to the scheduled message.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(EmailAccount::class, 'email_account_id');
    }

    /**
     * Get the user that scheduled message.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'email_account_id');
    }

    /**
     * Mark the scheduled message as "sending".
     */
    public function markAsSending(): static
    {
        $this->fill(['status' => 'sending'])->save();

        return $this;
    }

    /**
     * Mark the scheduled message as "failed".
     */
    public function markAsFailed(?string $reason, array $attributes = []): static
    {
        $this->fill(array_merge($attributes, [
            'status' => 'failed',
            'fail_reason' => $reason,
        ]))->save();

        return $this;
    }

    /**
     * Mark the scheduled message as "sent".
     */
    public function markAsSent(): static
    {
        $this->fill([
            'status' => 'sent',
            'fail_reason' => null,
            'retry_after' => null,
            'failed_at' => null,
            'sent_at' => now(),
        ])->save();

        return $this;
    }

    /**
     * Determine if the email status is "sent".
     */
    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    /**
     * Determine if the email status is "sending".
     */
    public function isSending(): bool
    {
        return $this->status === 'sending';
    }

    /**
     * Determine if the email status is "pending".
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Determine if the email status is "failed".
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Scope a query to include only scheduled emails with status "pending".
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', 'pending');
    }

    /**
     * Scope a query to include only scheduled emails with status "failed".
     */
    public function scopeFailed(Builder $query): void
    {
        $query->where('status', 'failed');
    }

    /**
     * Scope a query to include emails that due for sending.
     */
    public function scopeDueForSend(Builder $query): void
    {
        $query->pending()->where('scheduled_at', '<=', Carbon::now());
    }

    /**
     * Scope a query to include failed scheduled emails that can be retried for sending.
     */
    public function scopeRetryable(Builder $query, $after = null): void
    {
        $query->where('status', 'failed')
            ->whereNotNull('retry_after')
            ->when(! is_null($after), function (Builder $query) use ($after) {
                $query->where('retry_after', '<=', $after);
            })
            ->where('retries', '<', static::$maxRetries);
    }

    /**
     * Scope a query to include scheduled emails of the given resource.
     */
    public function scopeOfResource(Builder $query, string $resourceName, int $resourceId): void
    {
        $resource = Application::resourceByName($resourceName);

        $query->whereRaw(
            "id IN (SELECT scheduled_email_id FROM {$query->getConnection()->getTablePrefix()}model_has_scheduled_emails WHERE model_type = ? AND model_id = ?)",
            [
                $resource::$model,
                $resourceId,
            ]
        );
    }
}

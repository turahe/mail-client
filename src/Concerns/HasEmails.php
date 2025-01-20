<?php

namespace Turahe\MailClient\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Turahe\MailClient\Criteria\EmailAccountsForUserCriteria;
use Turahe\MailClient\Models\EmailAccount;

trait HasEmails
{
    /**
     * Boot the HasEmails trait.
     */
    protected static function bootHasEmails(): void
    {
        static::deleted(function (Model $model) {
            $model->scheduledEmails()->delete();
            $model->emails()->delete();
        });
    }

    public function setEmail(string $email): EmailAccount
    {
        return $this->emails()->create([
            'email' => $email,
        ]);
    }

    public function emails(): MorphMany
    {
        return $this->morphMany(EmailAccount::class, 'model');

    }

    /**
     * Scope a query to include records by phone.
     */
    public function scopeByEmail(Builder $query, string $email): void
    {
        $query->whereHas('emails', function ($query) use ($email) {
            return $query->where('email', $email);
        });
    }

    /**
     * Get all of the scheduled emails for the model.
     */
    public function scheduledEmails(): MorphToMany
    {
        return $this->morphToMany(
            \Turahe\MailClient\Models\ScheduledEmail::class,
            'model',
            'model_has_scheduled_emails',
            null,
            'scheduled_email_id'
        );
    }

    /**
     * Get all of the emails for the model.
     */
    public function messages(): MorphToMany
    {
        return $this->morphToMany(
            \Turahe\MailClient\Models\EmailAccountMessage::class,
            'messageable',
            'email_account_messageables',
            null,
            'message_id'
        );
    }

    /**
     * A model has unread emails
     */
    public function unreadEmails(): MorphToMany
    {
        return $this->emails()->unread()->whereHas('folders', function ($folderQuery) {
            return $folderQuery->where('syncable', true);
        });
    }

    /**
     * Get the unread emails that the user can see
     */
    public function unreadEmailsForUser(): MorphToMany
    {
        return $this->unreadEmails()->where(function ($query) {
            $query->whereHas('account', function ($accountQuery) {
                $accountQuery->criteria(EmailAccountsForUserCriteria::class);
            })->whereHas('folders.account', function ($query) {
                return $query->whereColumn('folder_id', '!=', 'trash_folder_id');
            });
        });
    }
}

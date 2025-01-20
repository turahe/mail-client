<?php

namespace Turahe\MailClient\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Turahe\MailClient\Models\EmailAccount;

trait HasEmailAccounts
{
    /**
     * Boot the HasEmails trait.
     */
    protected static function bootHasEmailAccounts(): void
    {
        static::deleted(function (Model $model) {
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
}

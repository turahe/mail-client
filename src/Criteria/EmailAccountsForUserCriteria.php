<?php

namespace Turahe\MailClient\Criteria;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;

class EmailAccountsForUserCriteria
{
    /**
     * Initialize new EmailAccountsForUserCriteria instance.
     */
    public function __construct(protected ?User $user = null) {}

    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $query): void
    {
        $query->where(function ($query) {
            $user = $this->user ?: Auth::user();

            $query->whereHas('user', function ($subQuery) use ($user) {
                $subQuery->where('user_id', $user->id);
            });

            if ($user->can('access shared inbox')) {
                $query->orDoesntHave('user');
            }
        });
    }
}

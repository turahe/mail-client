<?php

namespace Turahe\MailClient\Criteria;

use Illuminate\Database\Eloquent\Builder;

class EmailAccountMessagesForUserCriteria
{
    /**
     * Apply the criteria for the given query.
     */
    public function apply(Builder $model)
    {
        return $model->whereHas('account', function ($query) {
            $query->criteria(EmailAccountsForUserCriteria::class);
        });
    }
}

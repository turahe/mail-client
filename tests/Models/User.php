<?php

namespace Turahe\MailClient\Tests\Models;

use Turahe\MailClient\Concerns\HasEmailAccounts;

class User extends \Illuminate\Foundation\Auth\User
{
    use HasEmailAccounts;
}

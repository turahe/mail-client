<?php

namespace Turahe\MailClient\Tests\Models;

use Turahe\MailClient\Concerns\HasEmails;

class User extends \Illuminate\Foundation\Auth\User {
    use HasEmails;
}

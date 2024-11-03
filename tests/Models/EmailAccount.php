<?php

namespace Turahe\MailClient\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountFactory;

class EmailAccount extends \Turahe\MailClient\Models\EmailAccount
{
    use HasFactory;

    protected static function newFactory()
    {
        return EmailAccountFactory::new();
    }
}

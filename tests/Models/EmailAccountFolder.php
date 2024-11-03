<?php

namespace Turahe\MailClient\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Turahe\MailClient\Tests\Factories\EmailAccountFolderFactory;

class EmailAccountFolder extends \Turahe\MailClient\Models\EmailAccountFolder
{
    use HasFactory;

    protected static function newFactory()
    {
        return EmailAccountFolderFactory::new();
    }
}

<?php

namespace Turahe\MailClient\Client\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Turahe\MailClient\Client\Client;

class MessageSent
{
    use Dispatchable;

    /**
     * Create new MessageSent instance.
     */
    public function __construct(public Client $client) {}
}

<?php

namespace Turahe\MailClient\Client\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Turahe\MailClient\Client\Client;

class MessageSending
{
    use Dispatchable;

    /**
     * Create new MessageSending instance.
     */
    public function __construct(public Client $client) {}
}
